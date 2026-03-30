<?php

use App\Http\Controllers\MatchController;
use Illuminate\Support\Facades\Route;


Route::get('/', [MatchController::class, 'index'])->name('matches.index');
Route::get('/matches', [MatchController::class, 'index'])->name('matches.all');
Route::get('/matches/{id}', [MatchController::class, 'show'])->name('matches.show');
Route::get('/api/matches', [MatchController::class, 'apiMatches'])->name('api.matches');
