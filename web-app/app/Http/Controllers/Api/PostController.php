<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Thread;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Thread $thread)
    {
        $posts = $thread->posts()
            ->with('user', 'replies.user')
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($posts);
    }

    public function store(Request $request, Thread $thread)
    {
        $request->validate([
            'body' => 'required|string',
            'parent_id' => 'nullable|exists:posts,id',
            'image' => 'nullable|image|max:10240',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
        }

        $post = Post::create([
            'thread_id' => $thread->id,
            'parent_id' => $request->input('parent_id'),
            'user_id' => auth()->id(),
            'body' => $request->input('body'),
            'image_path' => $imagePath,
        ]);

        $post->load('user');

        return response()->json($post, 201);
    }
}
