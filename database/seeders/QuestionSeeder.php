<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /** Loads database/data/questions.php. Safe to re-run: rows are matched on their key. */
    public function run(): void
    {
        $rows = require database_path('data/questions.php');

        // Anything no longer in the file (including the old placeholders) goes away.
        Question::whereNull('key')->orWhereNotIn('key', array_column($rows, 0))->delete();

        // Positions are unique, so move the survivors out of the way before renumbering.
        Question::query()->increment('position', 100000);

        foreach ($rows as $position => $row) {
            [$key, $category, $body, $correct, $wrong] = $row;
            $image = $row[5] ?? null;
            $options = $this->orderOptions($key, [$correct, ...$wrong]);

            Question::updateOrCreate(['key' => $key], [
                'category' => $category,
                'position' => $position + 1,
                'body' => $body,
                'image' => $image,
                'options' => $options,
                'correct' => array_search($correct, $options, true),
            ]);
        }

        // Seeding runs on every deploy. Only start the game over if it points at a question that's gone.
        $game = Game::current();
        $ids = $game->question_ids ?? [];

        if ($ids && Question::whereIn('id', $ids)->count() !== count($ids)) {
            $game->reset();
        }
    }

    /**
     * Numbers read best in ascending order; everything else is shuffled. The shuffle is seeded
     * by the key so re-seeding never moves the correct answer around.
     */
    private function orderOptions(string $key, array $options): array
    {
        $numeric = array_map(fn ($o) => str_replace(',', '', $o), $options);

        if ($numeric === array_filter($numeric, 'is_numeric')) {
            array_multisort(array_map('floatval', $numeric), $options);

            return array_values($options);
        }

        mt_srand(crc32($key));
        shuffle($options);
        mt_srand();

        return $options;
    }
}
