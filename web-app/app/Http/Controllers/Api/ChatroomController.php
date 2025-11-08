<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChatroomController extends Controller
{
    public function index()
    {
        $chatrooms = \App\Models\Chatroom::with('creator')
            ->withCount('messages')
            ->orderBy('created_at')
            ->get();

        return response()->json($chatrooms);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'pow_hash' => 'required|string|regex:/^21e80{4}/',
        ]);

        $hash = $request->input('pow_hash');
        if (!str_starts_with($hash, '21e80000')) {
            return response()->json(['error' => 'Invalid POW hash'], 400);
        }

        $chatroom = \App\Models\Chatroom::create([
            'name' => $request->input('name'),
            'slug' => \Illuminate\Support\Str::slug($request->input('name')),
            'created_by_user_id' => auth()->id(),
            'required_hash' => $hash,
        ]);

        return response()->json($chatroom, 201);
    }
}
