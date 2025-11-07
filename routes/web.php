<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\ThreadController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ChorumController;
use App\Http\Controllers\ChatController;

// Home - Board listing
Route::get('/', [BoardController::class, 'index'])->name('home');

// Boards
Route::get('/{board}/', [BoardController::class, 'show'])
    ->where('board', 'gen|tech|doodle|meta')
    ->name('boards.show');

// Threads
Route::get('/{board}/{thread}', [ThreadController::class, 'show'])
    ->where('board', 'gen|tech|doodle|meta')
    ->name('threads.show');
Route::post('/{board}/threads', [ThreadController::class, 'store'])
    ->where('board', 'gen|tech|doodle|meta')
    ->name('threads.store');

// Posts
Route::post('/threads/{thread}/posts', [PostController::class, 'store'])
    ->name('posts.store');

// Chorum (user blogs)
Route::get('/chorum/{username}', [ChorumController::class, 'index'])
    ->name('chorum.index');
Route::get('/chorum/{username}/{post}', [ChorumController::class, 'show'])
    ->name('chorum.show');

// Chat
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::get('/chat/{chatroom}', [ChatController::class, 'show'])->name('chat.show');
Route::post('/chat/{chatroom}/messages', [ChatController::class, 'store'])->name('chat.store');
