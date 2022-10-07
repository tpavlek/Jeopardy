<?php

namespace App;

use App\Events\ClueRevealed;
use App\Models\Clue;
use App\Models\Game;

class Transition
{

    public function __construct(public Game $game)
    {
    }

    public function revealClue(Clue $clue)
    {
        $game_clue = $this->game->getGameClue($clue);

        $this->game->markShowingClue();
        $game_clue->markRevealed();

        broadcast(new ClueRevealed($game_clue));
    }

}
