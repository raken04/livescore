<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiveScoreController;

Route::get('/', [LiveScoreController::class, 'index'])->name('livescore.index');
