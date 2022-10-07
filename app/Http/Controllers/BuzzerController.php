<?php

namespace App\Http\Controllers;

use App\Buzzer;
use App\Models\Game;
use Illuminate\Http\Request;

class BuzzerController extends Controller
{
    public function open(Game $game)
    {
        (new Buzzer($game))->open();

        return response()->json();
    }
}
