<?php

namespace App\Http\Controllers;

use App\Buzzer;
use App\Http\Requests\ControlBuzzerRequest;
use App\Models\Game;
use Illuminate\Http\Request;

class BuzzerController extends Controller
{
    public function store(Game $game, ControlBuzzerRequest $request)
    {
        (new Buzzer($game))->control($request->buzzerStatus());

        return response()->json();
    }
}
