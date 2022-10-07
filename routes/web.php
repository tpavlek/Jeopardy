<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\BuzzerController;
use App\Http\Controllers\ClueController;
use App\Http\Controllers\PlayController;
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

Route::get('/', [ PlayController::class, 'index' ])->name('play');

Route::get('/game/{game:slug}/board', [ BoardController::class, 'show' ])->name('board.show');
Route::post('/game/{game:slug}/clue/{clue:uuid}/reveal', [ ClueController::class, 'reveal' ])->name('clue.reveal');
Route::post('/game/{game:slug}/clue/{clue:uuid}/dismiss', [ ClueController::class, 'dismiss' ])->name('clue.dismiss');
Route::post('/game/{game:slug}/buzzer', [ BuzzerController::class, 'store' ])->name('buzzer.control');
