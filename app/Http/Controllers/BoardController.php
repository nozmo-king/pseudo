<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Thread;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index()
    {
        $boards = Board::withCount('threads')->get();
        return view('boards.index', compact('boards'));
    }

    public function show($slug)
    {
        $board = Board::where('slug', $slug)->firstOrFail();
        $threads = Thread::where('board_id', $board->id)
            ->with('user')
            ->withCount('posts')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('boards.show', compact('board', 'threads'));
    }
}
