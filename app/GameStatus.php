<?php

namespace App;

enum GameStatus: string
{

    case Unstarted = "unstarted";
    case ShowingClue = "showing-clue";
    case Finished = "finished";

}
