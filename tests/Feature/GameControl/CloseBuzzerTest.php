<?php

namespace Tests\Feature\GameControl;

use App\Buzzer;
use App\BuzzerStatus;
use Tests\TestCase;

class CloseBuzzerTest extends TestCase
{

    /**
     * @test
     */
    public function it_can_close_buzzer()
    {
        $this->actingAs($this->adminUser());

        $game = $this->unitTestGame();

        $this->postJson(route('buzzer.control', [ 'game' => $game->slug ]), [ 'status' => BuzzerStatus::Closed ])
            ->assertOk();

        $game->refresh();

        $this->assertEquals((new Buzzer($game))->status(), BuzzerStatus::Closed);
    }

}
