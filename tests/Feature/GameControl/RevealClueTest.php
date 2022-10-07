<?php

namespace Tests\Feature\GameControl;

use App\Events\ClueRevealed;
use App\GameStatus;
use App\Models\Category;
use App\Models\Clue;
use App\Models\Game;
use App\Models\GameClue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RevealClueTest extends TestCase
{

    /**
     * @test
     */
    public function it_restricts_showing_clues_to_administrators()
    {
        $this->actingAs($this->unitTestUser('Roger'));

        $game = $this->unitTestGame();

        $clue = $game->clueAt(Category::findNamed("TV Episode Names"), 600);

        $this->postJson(route('clue.reveal', [ 'game' => $game->slug, 'clue' => $clue->uuid ]))
            ->assertForbidden();
    }

    /**
     * @test
     */
    public function it_can_show_a_clue()
    {
        $this->actingAs($this->adminUser());

        $game = $this->unitTestGame();
        $clue = $game->clueAt(Category::findNamed("TV Episode Names"), 600);

        Event::fake();

        $this->postJson(route('clue.reveal', [ 'game' => $game->slug, 'clue' => $clue->uuid ] ))
            ->assertOk();

        Event::assertDispatched(ClueRevealed::class);

        $game->refresh();

        tap($game->clues->first(fn (Clue $gameClue) => $gameClue->id === $clue->id), function (Clue $gameClue) {
            $this->assertFalse($gameClue->pivot->isAvailable());
        });

        $this->assertEquals($game->status, GameStatus::ShowingClue);
    }

}
