<?php

namespace Tests\Feature\GameControl;

use App\Buzzer;
use App\BuzzerStatus;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class EnableBuzzerTest extends TestCase
{

    /**
     * @test
     */
    public function it_can_enable_buzzer()
    {
        $game = $this->unitTestGame();

        $this->postJson(route('buzzer.open', [ 'game' => $game->slug ]))
            ->assertOk();

        $game->refresh();

        $this->assertEquals((new Buzzer($game))->status(), BuzzerStatus::Open);
    }

}
