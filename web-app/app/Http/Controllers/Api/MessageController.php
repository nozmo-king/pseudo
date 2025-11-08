<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Services\ChatCommandService;
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

        $commandService = new ChatCommandService();
        $body = $request->input('body');

        if ($commandService->isCommand($body)) {
            $result = $commandService->execute(
                $body,
                auth()->id(),
                $request->input('chatroom_id')
            );

            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], 400);
            }

            return response()->json($result);
        }

        $userId = auth()->id();

        // Generate random food emoji name for anonymous users
        if (!$userId) {
            if (!session()->has('anon_name')) {
                $foodEmojis = ['🍕', '🍔', '🍟', '🌭', '🍿', '🧂', '🥓', '🥚', '🍳', '🧇', '🥞', '🧈', '🍞', '🥐', '🥨', '🥯', '🥖', '🫓', '🥪', '🌮', '🌯', '🫔', '🥙', '🧆', '🥚', '🍖', '🍗', '🥩', '🍠', '🥟', '🥠', '🥡', '🍱', '🍘', '🍙', '🍚', '🍛', '🍜', '🍝', '🍢', '🍣', '🍤', '🍥', '🥮', '🍡', '🥘', '🍲', '🫕', '🍵', '🥣', '🥗', '🍿', '🧈', '🧇', '🥞', '🧆', '🫓', '🥙', '🌮', '🌯', '🫔', '🥪', '🥨', '🥯', '🥖', '🍞', '🥐', '🧀', '🥚', '🍳', '🧈', '🥞', '🧇', '🥓', '🍔', '🍟', '🌭', '🍕', '🥪'];
                session(['anon_name' => $foodEmojis[array_rand($foodEmojis)]]);
            }
        }

        $message = Message::create([
            'user_id' => $userId,
            'chatroom_id' => $request->input('chatroom_id'),
            'body' => $body,
            'anonymous_name' => !$userId ? session('anon_name') : null,
        ]);

        $message->load('user');

        return response()->json($message, 201);
    }
}
