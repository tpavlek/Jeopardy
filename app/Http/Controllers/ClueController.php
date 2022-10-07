<?php

namespace App\Http\Controllers;

use App\Events\ClueRevealed;
use App\Http\Requests\RevealClueRequest;
use App\Models\Clue;
use App\Models\Game;
use App\Transition;
use Illuminate\Http\JsonResponse;

class ClueController extends Controller
{

    public function reveal(RevealClueRequest $request, Game $game, Clue $clue): JsonResponse
    {
        (new Transition($game))->revealClue($clue);

        return response()->json();
    }
}
