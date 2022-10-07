<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function show(Game $game)
    {
        return response()->json([ 'categories' => $game->getBoard() ]);
    }
}
