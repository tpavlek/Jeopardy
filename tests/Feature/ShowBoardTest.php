<?php

namespace Tests\Feature;

use App\ClueStatus;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ShowBoardTest extends TestCase
{

    /**
     * @test
     */
    public function it_can_show_the_current_game_board()
    {
        $game = $this->unitTestGame();

        $json = $this->getJson(route('board.show', [ 'game' => $game ]))
            ->assertOk()
            ->assertJsonFragment([
                'name' => "Potent Potables",
                'clues' => [
                    [
                        'value' => 200,
                        'status' => ClueStatus::Available,
                    ],
                    [
                        'value' => 400,
                        'status' => ClueStatus::Available,
                    ],
                    [
                        'value' => 600,
                        'status' => ClueStatus::Available,
                    ],
                    [
                        'value' => 800,
                        'status' => ClueStatus::Available,
                    ],
                    [
                        'value' => 1000,
                        'status' => ClueStatus::Available,
                    ]
                ]
            ])
            ->json();

        // We want to ensure the categories are correctly ordered, by file.
        $this->assertEquals(
            [ "Potent Potables", "Before and After", "TV Episode Names", "Alphabetically Last", "Opera"],
            collect($json['categories'])->pluck('name')->all()
        );
    }

}
