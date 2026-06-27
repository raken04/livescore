<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiveScoreController;
use App\Http\Controllers\StandingsController;

Route::get('/', [LiveScoreController::class, 'index'])->name('livescore.index');
Route::get('/match/{id}', [LiveScoreController::class, 'show'])->name('livescore.show');
Route::get('/standings', [StandingsController::class, 'index'])->name('standings.index');
