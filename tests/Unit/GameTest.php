<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class GameTest extends TestCase
{

    /**
     * @test
     */
    public function it_can_have_authorized_users()
    {
        $game = $this->unitTestGame();

        $game->authorizeUsers("Player1", "Player2", "Player4");
        $game->refresh();
        $this->assertCount(3, $game->users);

        tap($game->users->firstWhere('name', "Player1"), function (User $user) {
            $this->assertNotNull($user);
            $this->assertFalse($user->isAdmin());
        });

        tap($game->users->firstWhere('name', "Player2"), function (User $user) {
            $this->assertNotNull($user);
            $this->assertFalse($user->isAdmin());
        });

        tap($game->users->firstWhere('name', "Player4"), function (User $user) {
            $this->assertNotNull($user);
            $this->assertFalse($user->isAdmin());
        });
    }

}
