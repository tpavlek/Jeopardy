<?php

namespace Tests\Feature\GameControl;

use App\Buzzer;
use App\BuzzerStatus;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class OpenBuzzerTest extends TestCase
{

    /**
     * @test
     */
    public function it_can_open_buzzer()
    {
        $this->actingAs($this->adminUser());

        $game = $this->unitTestGame();

        $this->postJson(route('buzzer.control', [ 'game' => $game->slug ]), [ 'status' => BuzzerStatus::Open ])
            ->assertOk();

        $game->refresh();

        $this->assertEquals((new Buzzer($game))->status(), BuzzerStatus::Open);
    }

}
