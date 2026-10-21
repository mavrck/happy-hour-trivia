<?php

namespace App\Http\Controllers;

use App\Events\GameUpdated;
use App\Models\Answer;
use App\Models\Game;
use App\Models\Player;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HostController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $password = (string) $request->input('password');

        if (! hash_equals(config('game.host_password'), $password)) {
            return response()->json(['message' => 'Wrong password.'], 422);
        }

        $request->session()->put('host', true);

        return response()->json(['ok' => true]);
    }

    public function state(): JsonResponse
    {
        return response()->json(GameController::payload(null, forHost: true));
    }

    public function action(Request $request): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:start,reveal,next,reset,wipe,seconds,change,count,late,kick,ban,unban,rename'],
            'player' => ['required_if:action,kick,ban,unban,rename', 'integer'],
            'name' => ['required_if:action,rename', 'string', 'max:24'],
            'allow_late' => ['required_if:action,late', 'boolean'],
            'count' => ['required_if:action,count', 'integer', 'min:5', 'max:40'],
            'allow' => ['required_if:action,change', 'boolean'],
            'seconds' => ['required_if:action,seconds', 'integer', 'min:5', 'max:30'],
        ]);
        $action = $data['action'];

        $game = Game::current()->load('question');

        match ($action) {
            'start' => $game->state === Game::LOBBY ? $game->start() : null,
            'reveal' => $game->reveal(),
            'next' => $game->state === Game::REVEAL ? $game->next() : null,
            'reset' => $game->reset(),
            'wipe' => $game->reset(wipePlayers: true),
            'change' => $game->state === Game::LOBBY ? $game->update(['allow_change' => (bool) $data['allow']]) : null,
            'kick' => $this->kick($data['player']),
            'ban' => $this->ban($data['player']),
            'unban' => $this->unban($data['player']),
            'rename' => $this->rename($data['player'], $data['name']),
            'late' => $game->update(['allow_late_join' => (bool) $data['allow_late']]),
            'count' => $game->state === Game::LOBBY ? $game->update(['question_count' => $data['count']]) : null,
            'seconds' => $game->state === Game::LOBBY ? $game->update(['question_seconds' => $data['seconds']]) : null,
        };

        return response()->json(GameController::payload(null, forHost: true));
    }

    /** Removes the player and their answers. They can rejoin, under a new name if they like. */
    private function kick(int $id): void
    {
        Player::whereKey($id)->delete();
        GameUpdated::send();
    }

    /** Hides the player from the game for good: their name and device can't be used to rejoin. */
    private function ban(int $id): void
    {
        Answer::where('player_id', $id)->delete();
        Player::whereKey($id)->update(['banned_at' => now()]);
        GameUpdated::send();
    }

    private function unban(int $id): void
    {
        Player::withoutGlobalScopes()->whereKey($id)->update(['banned_at' => null]);
        GameUpdated::send();
    }

    private function rename(int $id, string $name): void
    {
        $name = trim($name);

        $taken = Player::withoutGlobalScopes()
            ->whereKeyNot($id)
            ->whereRaw('lower(name) = ?', [Str::lower($name)])
            ->exists();

        if ($name === '' || $taken) {
            throw ValidationException::withMessages(['name' => 'That name is empty or already taken.']);
        }

        Player::whereKey($id)->update(['name' => $name]);
        GameUpdated::send();
    }
}
