<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThreadController extends Controller
{
    public function index(Request $request)
    {
        $threads = Thread::select('threads.*')
            ->leftJoin('proof_of_work', 'threads.id', '=', 'proof_of_work.thread_id')
            ->selectRaw('threads.*, COALESCE(SUM(proof_of_work.points), 0) as total_pow')
            ->groupBy('threads.id')
            ->with('user')
            ->withCount('posts')
            ->orderByDesc('total_pow')
            ->get();

        return response()->json($threads);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $thread = Thread::create([
            'title' => $request->input('title'),
            'user_id' => auth()->id(),
        ]);

        $thread->load('user');

        return response()->json($thread, 201);
    }

    public function show(Thread $thread)
    {
        $thread->load([
            'user',
            'posts' => function ($query) {
                $query->with('user', 'replies.user')
                      ->whereNull('parent_id')
                      ->orderBy('created_at', 'asc');
            }
        ]);

        $thread->total_pow = $thread->proofOfWork()->sum('points') ?? 0;

        return response()->json($thread);
    }
}
