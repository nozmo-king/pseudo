<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(User $user)
    {
        $user->load('achievements');
        $user->total_pow = $user->proofOfWork()->sum('points');
        $user->post_count = $user->posts()->count();
        $user->thread_count = $user->threads()->count();

        return response()->json($user);
    }

    public function update(Request $request)
    {
        $request->validate([
            'display_name' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user = auth()->user();

        if ($request->has('display_name')) {
            $user->display_name = $request->input('display_name');
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
        }

        $user->save();

        return response()->json($user);
    }

    public function achievements()
    {
        $user = auth()->user();
        $achievements = $user->achievements()
            ->orderBy('difficulty')
            ->get()
            ->map(function ($achievement) {
                return [
                    'difficulty' => $achievement->difficulty,
                    'name' => \App\Models\Achievement::getDifficultyName($achievement->difficulty),
                    'symbol' => \App\Models\Achievement::getDiamondSymbol($achievement->difficulty),
                    'hash' => $achievement->hash,
                    'achieved_at' => $achievement->created_at,
                ];
            });

        return response()->json($achievements);
    }
}
