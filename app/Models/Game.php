<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Events\GameUpdated;
use App\Support\QuestionSelector;
use Illuminate\Support\Facades\DB;

class Game extends Model
{
    public const LOBBY = 'lobby';
    public const QUESTION = 'question';
    public const REVEAL = 'reveal';
    public const FINISHED = 'finished';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['allow_change' => 'boolean', 'allow_late_join' => 'boolean', 'question_ids' => 'array'];
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], ['state' => self::LOBBY, 'question_seconds' => 20, 'allow_change' => false, 'question_count' => 20, 'allow_late_join' => true]);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public static function nowMs(): int
    {
        return (int) (microtime(true) * 1000);
    }

    public function remainingSeconds(): float
    {
        if ($this->state !== self::QUESTION || ! $this->question) {
            return 0;
        }

        $elapsed = (static::nowMs() - $this->started_at_ms) / 1000;

        return max(0, $this->question_seconds - $elapsed);
    }

    /** Reveal automatically once time is up or everyone has answered. */
    public function tick(): void
    {
        if ($this->state !== self::QUESTION) {
            return;
        }

        $players = Player::count();
        $answered = Answer::where('question_id', $this->question_id)->count();

        // With changeable answers, "everyone answered" isn't final, so only the clock (or the host) ends the question.
        $everyoneIn = ! $this->allow_change && $players > 0 && $answered >= $players;

        if ($this->remainingSeconds() <= 0 || $everyoneIn) {
            $this->reveal();
        }
    }

    public function start(): void
    {
        $ids = QuestionSelector::pick($this->question_count);
        $this->update(['question_ids' => $ids]);

        $this->showQuestion(Question::find($ids[0] ?? null));
    }

    public function next(): void
    {
        $ids = $this->question_ids ?? [];
        $nextId = $ids[array_search($this->question_id, $ids, true) + 1] ?? null;

        $this->showQuestion($nextId ? Question::find($nextId) : null);
    }

    /** 1-based place of the current question within this game. */
    public function questionNumber(): ?int
    {
        $index = array_search($this->question_id, $this->question_ids ?? [], true);

        return $index === false ? null : $index + 1;
    }

    public function reveal(): void
    {
        $revealed = false;

        DB::transaction(function () use (&$revealed) {
            // Atomic flip so concurrent polls only score once.
            $flipped = static::where('id', $this->id)
                ->where('state', self::QUESTION)
                ->update(['state' => self::REVEAL]);

            if ($flipped !== 1) {
                return;
            }

            $revealed = true;

            Answer::where('question_id', $this->question_id)
                ->where('points', '>', 0)
                ->get()
                ->each(fn (Answer $a) => Player::whereKey($a->player_id)->increment('score', $a->points));
        });

        $this->refresh();

        if ($revealed) {
            GameUpdated::send();
        }
    }

    public function finish(): void
    {
        $this->update(['state' => self::FINISHED]);
        GameUpdated::send();
    }

    /** Back to the lobby with scores cleared. Pass $wipePlayers to drop everyone too. */
    public function reset(bool $wipePlayers = false): void
    {
        DB::transaction(function () use ($wipePlayers) {
            Answer::query()->delete();
            $wipePlayers ? Player::query()->delete() : Player::query()->update(['score' => 0]);
            $this->update(['state' => self::LOBBY, 'question_id' => null, 'started_at_ms' => null, 'question_ids' => null]);
        });

        GameUpdated::send();
    }

    private function showQuestion(?Question $question): void
    {
        if (! $question) {
            $this->finish();

            return;
        }

        $this->update([
            'state' => self::QUESTION,
            'question_id' => $question->id,
            'started_at_ms' => static::nowMs(),
        ]);
        $this->load('question');
        GameUpdated::send();
    }
}
