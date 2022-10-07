<?php

namespace Tests;

use App\GameRound;
use App\Models\Category;
use App\Models\Clue;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;

    public function adminUser(): User
    {
        return User::query()
            ->firstOrCreate([ 'name' => 'admin' ], [ 'is_admin' => true ]);
    }

    public function unitTestUser(string $named): User
    {
        return User::query()
            ->firstOrCreate([ 'name' => $named ], [ 'is_admin' => false ]);
    }

    public function unitTestGame(): Game
    {
        $rounds = [
            GameRound::Single->value => collect([
                Category::factory()->create([ 'name' => "Potent Potables" ]),
                Category::factory()->create([ "name" => "Before and After" ]),
                Category::factory()->create([ "name" => "TV Episode Names" ]),
                Category::factory()->create([ "name" => "Alphabetically Last" ]),
                Category::factory()->create([ "name" => "Opera" ]),
            ]),
            GameRound::Double->value => collect([
                Category::factory()->create([ 'name' => "Rhyme Time" ]),
                Category::factory()->create([ "name" => "12 Letter Words" ]),
                Category::factory()->create([ "name" => "World Rivers" ]),
                Category::factory()->create([ "name" => "Before, Middle and After" ]),
                Category::factory()->create([ "name" => "Famous Initials" ]),
            ])
        ];

        $factory = Game::factory();


        foreach ($rounds as $round => $categories) {
            $round = GameRound::from($round);
            $categories->each(function (Category $category, $index) use (&$factory, $round) {
                $factory = $factory
                    ->hasAttached($category, [ 'file' => $index + 1, 'round' => $round ])
                    ->has(
                        Clue::factory()
                            ->for($category)
                            ->count(5)
                            ->sequence(fn ($sequence) => [ 'value' => ($sequence->index + 1) * ($round->toRoundNumber() * 200) ])
                    );
            });
        }

        return $factory->create();
    }
}
