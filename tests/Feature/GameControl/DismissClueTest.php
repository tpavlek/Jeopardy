<?php

namespace Tests\Feature\GameControl;

use App\ClueStatus;
use App\Events\ClueRevealed;
use App\Models\Category;
use App\Transition;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DismissClueTest extends TestCase
{

    /**
     * @test
     */
    public function it_can_dismiss_a_clue()
    {
        Event::fake();
        $this->actingAs($this->adminUser());

        $game = $this->unitTestGame();
        $clue = $game->clueAt(Category::findNamed("TV Episode Names"), 600);

        $game_clue = (new Transition($game))->revealClue($clue);

        $this->postJson(route('clue.dismiss', [ 'game' => $game->slug, 'clue' => $clue->uuid ]))
            ->assertOk();

        Event::assertDispatched(ClueRevealed::class);

        $game_clue->refresh();

        $this->assertEquals(ClueStatus::Unavailable, $game_clue->status);
    }

}
