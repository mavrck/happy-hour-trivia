<?php

namespace App\Http\Controllers;

use App\Events\GameUpdated;
use App\Models\Answer;
use App\Models\Game;
use App\Models\Player;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GameController extends Controller
{
    public function join(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:24'],
            'pin' => ['nullable', 'string', 'max:4'],
        ]);
        $name = trim($data['name']);

        $existing = Player::whereRaw('lower(name) = ?', [Str::lower($name)])->first();

        // Same name + matching code means the same person on a new device.
        if ($existing) {
            if (($data['pin'] ?? '') !== '' && hash_equals($existing->pin, $data['pin'])) {
                return response()->json(['token' => $existing->token]);
            }

            return response()->json([
                'message' => 'That name is taken. Pick another, or enter your 4-digit rejoin code if that\'s you.',
                'needsPin' => true,
            ], 422);
        }

        $game = Game::current();

        if ($game->state !== Game::LOBBY && ! $game->allow_late_join) {
            return response()->json([
                'message' => 'The game has already started and new players can\'t join. If you were already playing, enter your name and rejoin code.',
                'needsPin' => true,
            ], 422);
        }

        $player = Player::create([
            'name' => $name,
            'token' => Str::random(40),
            'pin' => str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
        ]);

        GameUpdated::send();

        return response()->json(['token' => $player->token]);
    }

    public function state(Request $request): JsonResponse
    {
        return response()->json(self::payload($request->query('token'), forHost: false));
    }

    public function answer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'choice' => ['required', 'integer', 'min:0', 'max:3'],
        ]);

        $player = Player::where('token', $data['token'])->first();
        $game = Game::current()->load('question');

        if (! $player || $game->state !== Game::QUESTION || $game->remainingSeconds() <= 0) {
            return response()->json(['message' => 'Too late.'], 422);
        }

        $question = $game->question;
        // What this answer is worth if correct. Stored separately so players can see it without revealing correctness.
        $potential = (int) round(500 + 500 * ($game->remainingSeconds() / $game->question_seconds));
        $points = $data['choice'] === $question->correct ? $potential : 0;

        $existing = Answer::where(['player_id' => $player->id, 'question_id' => $question->id])->first();

        if (! $existing) {
            Answer::create([
                'player_id' => $player->id,
                'question_id' => $question->id,
                'choice' => $data['choice'],
                'points' => $points,
                'potential' => $potential,
            ]);
        } elseif ($game->allow_change && $existing->choice !== $data['choice']) {
            // Points are re-scored at the time of the change, so a later answer is worth less.
            $existing->update(['choice' => $data['choice'], 'points' => $points, 'potential' => $potential]);
        }

        // If that was the last answer needed, reveal now instead of waiting for the next poll.
        $game->tick();

        return response()->json(['ok' => true]);
    }

    /** Every question with the right answer and what this player picked. Only sent once the game is over. */
    private static function review(Player $player): array
    {
        $answers = Answer::where('player_id', $player->id)->get()->keyBy('question_id');

        $game = Game::current();
        $ids = $game->question_ids ?? [];
        $questions = Question::whereIn('id', $ids)->get()->keyBy('id');

        return collect($ids)->map(fn ($id, $i) => [$i + 1, $questions->get($id)])->filter(fn ($pair) => $pair[1])->map(function ($pair) use ($answers) {
            [$number, $question] = $pair;
            $answer = $answers->get($question->id);

            return [
                'number' => $number,
                'body' => $question->body,
                'image' => $question->image,
                'correct' => $question->options[$question->correct],
                'given' => $answer ? $question->options[$answer->choice] : null,
                'wasCorrect' => $answer?->choice === $question->correct,
                'points' => $answer?->points ?? 0,
            ];
        })->values()->all();
    }

    public static function payload(?string $token, bool $forHost): array
    {
        $game = Game::current();
        $game->tick();
        $game->load('question');

        $question = $game->question;
        $showQuestion = in_array($game->state, [Game::QUESTION, Game::REVEAL]) && $question;
        $revealed = $game->state === Game::REVEAL;

        $payload = [
            'state' => $game->state,
            'playerCount' => Player::count(),
            'players' => $game->state === Game::LOBBY ? Player::orderBy('id')->pluck('name') : [],
            'leaderboard' => Player::orderByDesc('score')->orderBy('name')->limit(10)->get(['name', 'score']),
            'total' => $game->question_ids ? count($game->question_ids) : min($game->question_count, Question::count()),
            'questionCount' => $game->question_count,
            'bankSize' => Question::count(),
            'questionSeconds' => $game->question_seconds,
            'allowChange' => $game->allow_change,
            'allowLateJoin' => $game->allow_late_join,
            'question' => null,
            'me' => null,
        ];

        if ($forHost) {
            $answeredIds = $showQuestion
                ? Answer::where('question_id', $question->id)->pluck('player_id')->all()
                : [];

            $payload['roster'] = Player::orderBy('name')->get(['id', 'name', 'pin', 'score'])
                ->makeVisible('pin')
                ->each(fn (Player $p) => $p->setAttribute('answered', in_array($p->id, $answeredIds)))
                ->makeHidden('id');
        }

        if ($showQuestion) {
            $answers = Answer::where('question_id', $question->id)->get();

            $payload['question'] = [
                'number' => $game->questionNumber(),
                'body' => $question->body,
                'image' => $question->image,
                'options' => $question->options,
                'seconds' => $game->question_seconds,
                'remaining' => round($game->remainingSeconds(), 2),
                'answered' => $answers->count(),
                'correct' => $revealed || $forHost ? $question->correct : null,
                'counts' => $revealed
                    ? collect(range(0, count($question->options) - 1))
                        ->map(fn ($i) => $answers->where('choice', $i)->count())
                        ->all()
                    : null,
            ];
        }

        if ($token && $player = Player::where('token', $token)->first()) {
            $mine = $showQuestion ? Answer::where(['player_id' => $player->id, 'question_id' => $question->id])->first() : null;

            $payload['me'] = [
                'name' => $player->name,
                'pin' => $player->pin,
                'score' => $player->score,
                'rank' => Player::where('score', '>', $player->score)->count() + 1,
                'choice' => $mine?->choice,
                'potential' => $mine?->potential,
                'points' => $revealed ? ($mine?->points ?? 0) : null,
                'review' => $game->state === Game::FINISHED ? self::review($player) : null,
            ];
        }

        return $payload;
    }
}
