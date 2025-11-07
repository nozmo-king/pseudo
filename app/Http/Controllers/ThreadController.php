<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Thread;
use App\Models\Post;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    public function show($boardSlug, $threadId)
    {
        $board = Board::where('slug', $boardSlug)->firstOrFail();
        $thread = Thread::with(['user', 'posts.user', 'proofOfWorks'])
            ->findOrFail($threadId);
        
        if ($thread->board_id !== $board->id) {
            abort(404);
        }

        return view('threads.show', compact('board', 'thread'));
    }

    public function store(Request $request, $boardSlug)
    {
        $board = Board::where('slug', $boardSlug)->firstOrFail();
        
        $validated = $request->validate([
            'subject' => 'required|max:255',
            'content' => 'required',
            'pow_nonce' => 'required',
            'pow_difficulty' => 'required',
        ]);

        // Verify proof of work
        $data = $validated['subject'] . $validated['content'];
        if (!\App\Models\ProofOfWork::verifyHash($data, $validated['pow_nonce'], $validated['pow_difficulty'])) {
            return back()->with('error', 'Invalid proof of work');
        }

        $thread = Thread::create([
            'board_id' => $board->id,
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'points' => \App\Models\ProofOfWork::getPointsForDifficulty($validated['pow_difficulty']),
        ]);

        // Store proof of work
        \App\Models\ProofOfWork::create([
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'puzzle_difficulty' => $validated['pow_difficulty'],
            'hash' => hash('sha256', $data . $validated['pow_nonce']),
            'nonce' => $validated['pow_nonce'],
            'points' => $thread->points,
            'ip_address' => $request->ip(),
            'powable_id' => $thread->id,
            'powable_type' => Thread::class,
        ]);

        return redirect()->route('threads.show', ['board' => $boardSlug, 'thread' => $thread->id]);
    }
}
