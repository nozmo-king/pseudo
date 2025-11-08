<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $chatroomId = $request->input('chatroom_id', 1);

        $messages = Message::where('chatroom_id', $chatroomId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'chatroom_id' => 'required|exists:chatrooms,id',
            'body' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'user_id' => auth()->id(),
            'chatroom_id' => $request->input('chatroom_id'),
            'body' => $request->input('body'),
        ]);

        $message->load('user');

        return response()->json($message, 201);
    }
}
