<?php

namespace App\Http\Controllers;

use App\Models\Chatroom;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $chatrooms = Chatroom::withCount('messages')->get();
        return view('chat.index', compact('chatrooms'));
    }

    public function show($slug)
    {
        $chatroom = Chatroom::where('slug', $slug)->firstOrFail();
        $messages = ChatMessage::where('chatroom_id', $chatroom->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();
        
        return view('chat.show', compact('chatroom', 'messages'));
    }

    public function store(Request $request, $slug)
    {
        $chatroom = Chatroom::where('slug', $slug)->firstOrFail();
        
        $validated = $request->validate([
            'message' => 'required|max:1000',
            'pow_nonce' => 'required',
            'pow_difficulty' => 'required',
        ]);

        // Verify proof of work
        if (!\App\Models\ProofOfWork::verifyHash($validated['message'], $validated['pow_nonce'], $validated['pow_difficulty'])) {
            return response()->json(['error' => 'Invalid proof of work'], 422);
        }

        $message = ChatMessage::create([
            'chatroom_id' => $chatroom->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'points' => \App\Models\ProofOfWork::getPointsForDifficulty($validated['pow_difficulty']),
        ]);

        // Store proof of work
        \App\Models\ProofOfWork::create([
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'puzzle_difficulty' => $validated['pow_difficulty'],
            'hash' => hash('sha256', $validated['message'] . $validated['pow_nonce']),
            'nonce' => $validated['pow_nonce'],
            'points' => $message->points,
            'ip_address' => $request->ip(),
            'powable_id' => $message->id,
            'powable_type' => ChatMessage::class,
        ]);

        return response()->json(['success' => true, 'message' => $message]);
    }
}
