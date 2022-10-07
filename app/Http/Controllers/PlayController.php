<?php

namespace App\Http\Controllers;

use App\Models\Game;

class PlayController extends Controller
{

    public function index()
    {
        return view('game');
    }

}
