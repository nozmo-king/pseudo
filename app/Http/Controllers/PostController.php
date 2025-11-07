<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function store(Request $request, $threadId)
    {
        $thread = Thread::findOrFail($threadId);
        
        if ($thread->is_locked) {
            return back()->with('error', 'Thread is locked');
        }

        $validated = $request->validate([
            'content' => 'required',
            'pow_nonce' => 'required',
            'pow_difficulty' => 'required',
        ]);

        // Verify proof of work
        if (!\App\Models\ProofOfWork::verifyHash($validated['content'], $validated['pow_nonce'], $validated['pow_difficulty'])) {
            return back()->with('error', 'Invalid proof of work');
        }

        $post = Post::create([
            'thread_id' => $thread->id,
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'points' => \App\Models\ProofOfWork::getPointsForDifficulty($validated['pow_difficulty']),
        ]);

        // Store proof of work
        \App\Models\ProofOfWork::create([
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'puzzle_difficulty' => $validated['pow_difficulty'],
            'hash' => hash('sha256', $validated['content'] . $validated['pow_nonce']),
            'nonce' => $validated['pow_nonce'],
            'points' => $post->points,
            'ip_address' => $request->ip(),
            'powable_id' => $post->id,
            'powable_type' => Post::class,
        ]);

        return back()->with('success', 'Post created successfully');
    }
}
