<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RespondWithTalky;
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
        $anonName = null;

        // Generate random food emoji name for anonymous users using cookie
        if (!$userId) {
            $anonName = $request->cookie('anon_name');

            if (!$anonName) {
                $foodEmojis = ['🍕', '🍔', '🍟', '🌭', '🍿', '🧂', '🥓', '🥚', '🍳', '🧇', '🥞', '🧈', '🍞', '🥐', '🥨', '🥯', '🥖', '🫓', '🥪', '🌮', '🌯', '🫔', '🥙', '🧆', '🍖', '🍗', '🥩', '🍠', '🥟', '🥠', '🥡', '🍱', '🍘', '🍙', '🍚', '🍛', '🍜', '🍝', '🍢', '🍣', '🍤', '🍥', '🥮', '🍡', '🥘', '🍲', '🫕', '🍵', '🥣', '🥗', '🧀'];
                $anonName = $foodEmojis[array_rand($foodEmojis)];
            }
        }

        $message = Message::create([
            'user_id' => $userId,
            'chatroom_id' => $request->input('chatroom_id'),
            'body' => $body,
            'anonymous_name' => $anonName,
        ]);

        $message->load('user');

        // Check if we should trigger talky bot response (async)
        $this->maybeRespondWithTalky($request->input('chatroom_id'));

        $response = response()->json($message, 201);

        // Set cookie for anonymous users
        if (!$userId && $anonName) {
            $response->cookie('anon_name', $anonName, 60 * 24 * 365); // 1 year
        }

        return $response;
    }

    private function maybeRespondWithTalky($chatroomId)
    {
        // Get recent messages (last 5 minutes)
        $recentMessages = Message::where('chatroom_id', $chatroomId)
            ->where('created_at', '>', now()->subMinutes(5))
            ->where('anonymous_name', '!=', 'talky')
            ->whereNull('user_id')
            ->orWhere(function($q) use ($chatroomId) {
                $q->where('chatroom_id', $chatroomId)
                  ->where('created_at', '>', now()->subMinutes(5))
                  ->whereNotNull('user_id');
            })
            ->count();

        // Only respond if chat has been quiet (less than 3 messages in last 5 mins)
        if ($recentMessages <= 2) {
            // Dispatch job to respond after delay (non-blocking)
            RespondWithTalky::dispatch($chatroomId);
        }
    }
}
