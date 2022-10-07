<?php

namespace App;

use App\Events\ClueRevealed;
use App\Models\Clue;
use App\Models\Game;
use App\Models\GameClue;

class Transition
{

    public function __construct(public Game $game)
    {
    }

    public function dismissClue(Clue $clue): GameClue
    {
        $game_clue = $this->game->getGameClue($clue);

        $this->game->markWaitingForSelection();
        $game_clue->markDismissed();

        broadcast(new ClueRevealed($game_clue));

        return $game_clue;
    }

    public function revealClue(Clue $clue): GameClue
    {
        $game_clue = $this->game->getGameClue($clue);

        $this->game->markShowingClue();
        $game_clue->markRevealed();

        broadcast(new ClueRevealed($game_clue));

        return $game_clue;
    }

}
