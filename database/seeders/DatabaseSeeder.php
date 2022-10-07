<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Clue;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Build the required administrative user
        User::query()
            ->firstOrCreate([ 'name' => 'admin' ], [ 'is_admin' => true ]);

        // We want three users to play the game
        User::factory()->count(3)->create();

        $categories = [
            "Potent Potables",
            "Before and After",
            "TV Episode Names",
            "Alphabetically Last",
            "Opera",
        ];

        $rounds = [ 1, 2 ];

        $factory = Game::factory();

        foreach ($rounds as $round) {
            foreach ($categories as $category) {
                $factory = $factory->has(
                    Clue::factory()->count(5)
                        ->sequence(fn ($sequence) => [ 'value' => ($sequence->index + 1) * ($round * 200) ])
                        ->state(fn() => [ 'category' => $category ])
                );
            }
        }

        return $factory->create();
    }
}
