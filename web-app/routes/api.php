<?php

use App\Http\Controllers\Admin\BoardController as AdminBoardController;
use App\Http\Controllers\Admin\FileUploadController;
use App\Http\Controllers\Admin\InviteCodeController;
use App\Http\Controllers\Api\BlogPostController;
use App\Http\Controllers\Api\ChatroomController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProofOfWorkController;
use App\Http\Controllers\Api\ThreadController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\PseudoKeyAuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/challenge', [PseudoKeyAuthController::class, 'challenge']);
Route::post('/auth/verify', [PseudoKeyAuthController::class, 'verify']);

Route::get('/pow/challenge', [ProofOfWorkController::class, 'challenge']);
Route::post('/pow/submit', [ProofOfWorkController::class, 'submit']);
Route::get('/pow/leaderboard', [ProofOfWorkController::class, 'leaderboard']);

Route::get('/boards', function() {
    return response()->json(\App\Models\Board::withCount('threads')->orderBy('position')->get());
});
Route::get('/boards/{board}', function(\App\Models\Board $board) {
    return response()->json($board->load('threads'));
});

Route::get('/threads', [ThreadController::class, 'index']);
Route::get('/threads/{thread}', [ThreadController::class, 'show']);

Route::get('/chatrooms', [ChatroomController::class, 'index']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::get('/users/{user}/blog', [BlogPostController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::post('/threads', [ThreadController::class, 'store']);
    Route::get('/threads/{thread}/posts', [PostController::class, 'index']);
    Route::post('/threads/{thread}/posts', [PostController::class, 'store']);

    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);

    Route::post('/chatrooms', [ChatroomController::class, 'store']);

    Route::post('/blog', [BlogPostController::class, 'store']);
    Route::put('/blog/{blogPost}', [BlogPostController::class, 'update']);
    Route::delete('/blog/{blogPost}', [BlogPostController::class, 'destroy']);

    Route::put('/profile', [UserController::class, 'update']);
    Route::get('/profile/achievements', [UserController::class, 'achievements']);
});

Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->group(function () {
    Route::get('/invites', [InviteCodeController::class, 'index']);
    Route::post('/invites', [InviteCodeController::class, 'store']);
    Route::delete('/invites/{inviteCode}', [InviteCodeController::class, 'destroy']);

    Route::get('/boards', [AdminBoardController::class, 'index']);
    Route::post('/boards', [AdminBoardController::class, 'store']);
    Route::put('/boards/{board}', [AdminBoardController::class, 'update']);
    Route::delete('/boards/{board}', [AdminBoardController::class, 'destroy']);

    Route::get('/files', [FileUploadController::class, 'index']);
    Route::post('/files', [FileUploadController::class, 'upload']);
});
