<?php

use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProofOfWorkController;
use App\Http\Controllers\Api\ThreadController;
use App\Http\Controllers\Auth\PseudoKeyAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/challenge', [PseudoKeyAuthController::class, 'challenge']);
Route::post('/auth/verify', [PseudoKeyAuthController::class, 'verify']);

Route::get('/pow/challenge', [ProofOfWorkController::class, 'challenge']);
Route::post('/pow/submit', [ProofOfWorkController::class, 'submit']);
Route::get('/pow/leaderboard', [ProofOfWorkController::class, 'leaderboard']);

Route::get('/threads', [ThreadController::class, 'index']);
Route::get('/threads/{thread}', [ThreadController::class, 'show']);

Route::middleware('auth')->group(function () {
    Route::post('/threads', [ThreadController::class, 'store']);
    Route::get('/threads/{thread}/posts', [PostController::class, 'index']);
    Route::post('/threads/{thread}/posts', [PostController::class, 'store']);
});
