<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProofOfWork;
use App\Models\User;
use App\Services\ProofOfWorkService;
use Illuminate\Http\Request;

class ProofOfWorkController extends Controller
{
    private ProofOfWorkService $powService;

    public function __construct(ProofOfWorkService $powService)
    {
        $this->powService = $powService;
    }

    public function challenge()
    {
        return response()->json($this->powService->generateChallenge());
    }

    public function submit(Request $request)
    {
        $request->validate([
            'challenge' => 'required|string',
            'nonce' => 'required|string',
            'thread_id' => 'nullable|exists:threads,id',
        ]);

        $userId = auth()->check() ? auth()->id() : null;
        $result = $this->powService->validateProof(
            $request->input('challenge'),
            $request->input('nonce'),
            $userId
        );

        if ($result) {
            if ($request->input('thread_id')) {
                ProofOfWork::find($result['proof_id'])->update([
                    'thread_id' => $request->input('thread_id')
                ]);
            }

            return response()->json($result);
        }

        return response()->json(['error' => 'Invalid proof'], 400);
    }

    public function leaderboard()
    {
        $users = User::select('users.*')
            ->leftJoin('proof_of_work', 'users.id', '=', 'proof_of_work.user_id')
            ->selectRaw('users.*, COALESCE(SUM(proof_of_work.points), 0) as total_points')
            ->groupBy('users.id')
            ->orderByDesc('total_points')
            ->limit(100)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'display_name' => $user->display_name ?? 'Anonymous',
                    'pubkey' => substr($user->pubkey, 0, 10) . '...',
                    'total_points' => $user->total_points,
                ];
            });

        return response()->json($users);
    }
}
