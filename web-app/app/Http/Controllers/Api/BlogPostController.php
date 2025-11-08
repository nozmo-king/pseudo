<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    public function index(\App\Models\User $user)
    {
        $posts = $user->blogPosts()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'pow_hash' => 'required|string|regex:/^21e8/',
        ]);

        $hash = $request->input('pow_hash');
        $points = \App\Models\ProofOfWork::calculatePoints($hash);

        if ($points < 1) {
            return response()->json(['error' => 'Invalid POW hash'], 400);
        }

        $post = \App\Models\BlogPost::create([
            'user_id' => auth()->id(),
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'pow_hash' => $hash,
            'pow_points' => $points,
        ]);

        return response()->json($post, 201);
    }

    public function update(Request $request, \App\Models\BlogPost $blogPost)
    {
        if ($blogPost->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'body' => 'sometimes|string',
        ]);

        $blogPost->update($request->only(['title', 'body']));

        return response()->json($blogPost);
    }

    public function destroy(\App\Models\BlogPost $blogPost)
    {
        if ($blogPost->user_id !== auth()->id()) {
            abort(403);
        }

        $blogPost->delete();
        return response()->json(['success' => true]);
    }
}
