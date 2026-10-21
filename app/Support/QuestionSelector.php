<?php

namespace App\Support;

use App\Models\Question;
use Illuminate\Support\Collection;

class QuestionSelector
{
    /** Category => how many are guaranteed in every game ("all" means every question in it). */
    private const GUARANTEED = ['luma' => 'all', 'monsters' => 2];

    /** @return list<int> question ids in play order */
    public static function pick(int $count): array
    {
        $bank = Question::get(['id', 'category']);
        $count = min($count, $bank->count());

        $chosen = collect();
        foreach (self::GUARANTEED as $category => $amount) {
            $inCategory = $bank->where('category', $category)->shuffle();
            $chosen = $chosen->merge($amount === 'all' ? $inCategory : $inCategory->take($amount));
        }

        // Too many guarantees for a tiny game: trim at random.
        $chosen = $chosen->unique('id')->shuffle()->take($count);

        $fill = $bank->whereNotIn('id', $chosen->pluck('id'))->shuffle()->take($count - $chosen->count());

        return self::separateNeighbours($chosen->merge($fill)->shuffle())->pluck('id')->all();
    }

    /** Reorders so the same category rarely appears twice in a row. */
    private static function separateNeighbours(Collection $questions): Collection
    {
        $remaining = $questions->values();
        $ordered = collect();

        while ($remaining->isNotEmpty()) {
            $lastCategory = $ordered->last()?->category;
            $index = $remaining->search(fn ($q) => $q->category !== $lastCategory);
            $index = $index === false ? 0 : $index;

            $ordered->push($remaining->get($index));
            $remaining->forget($index);
            $remaining = $remaining->values();
        }

        return $ordered;
    }
}
