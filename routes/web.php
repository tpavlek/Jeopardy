<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\BuzzerController;
use App\Http\Controllers\ClueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/game/{game:slug}/board', [ BoardController::class, 'show' ])->name('board.show');
Route::post('/game/{game:slug}/clue/{clue:uuid}/reveal', [ ClueController::class, 'reveal' ])->name('clue.reveal');
Route::post('/game/{game:slug}/buzzer', [ BuzzerController::class, 'open' ])->name('buzzer.open');
