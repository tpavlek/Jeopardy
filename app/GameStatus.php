<?php

namespace App;

enum GameStatus: string
{

    case Unstarted = "unstarted";
    case WaitingForSelection = "waiting-for-selection";
    case ShowingClue = "showing-clue";
    case Finished = "finished";

}
