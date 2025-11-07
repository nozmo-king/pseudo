<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ChorumPost;
use Illuminate\Http\Request;

class ChorumController extends Controller
{
    public function index($username)
    {
        $user = User::where('name', $username)->firstOrFail();
        $posts = ChorumPost::where('user_id', $user->id)
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('chorum.index', compact('user', 'posts'));
    }

    public function show($username, $postId)
    {
        $user = User::where('name', $username)->firstOrFail();
        $post = ChorumPost::where('user_id', $user->id)
            ->where('is_published', true)
            ->findOrFail($postId);
        
        return view('chorum.show', compact('user', 'post'));
    }
}
