<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\ProofOfWork;
use Illuminate\Support\Str;

class ProofOfWorkService
{
    public function generateChallenge(): array
    {
        $challenge = Str::random(64);

        return [
            'challenge' => $challenge,
            'target_prefix' => '21e8',
        ];
    }

    public function validateProof(string $challenge, string $nonce, ?int $userId = null, ?int $threadId = null): ?array
    {
        $hash = hash('sha256', $challenge . $nonce);

        $points = ProofOfWork::calculatePoints($hash);

        if ($points > 0) {
            $difficulty = $this->calculateDifficulty($hash);

            $proof = ProofOfWork::create([
                'user_id' => $userId,
                'thread_id' => $threadId,
                'challenge' => $challenge,
                'nonce' => $nonce,
                'hash' => $hash,
                'difficulty' => $difficulty,
                'points' => $points,
            ]);

            $achievementUnlocked = null;
            if ($userId) {
                $zeroCount = $this->countTrailingZeros($hash);
                if ($zeroCount > 0 && $zeroCount <= 10) {
                    $existing = Achievement::where('user_id', $userId)
                        ->where('difficulty', $zeroCount)
                        ->first();

                    if (!$existing) {
                        Achievement::create([
                            'user_id' => $userId,
                            'difficulty' => $zeroCount,
                            'hash' => $hash,
                        ]);
                        $achievementUnlocked = $zeroCount;
                    }
                }
            }

            return [
                'valid' => true,
                'hash' => $hash,
                'points' => $points,
                'proof_id' => $proof->id,
                'achievement_unlocked' => $achievementUnlocked,
            ];
        }

        return null;
    }

    private function calculateDifficulty(string $hash): int
    {
        $prefix = '21e8';
        if (!str_starts_with($hash, $prefix)) {
            return 0;
        }

        $matchLength = strlen($prefix);
        for ($i = strlen($prefix); $i < strlen($hash); $i++) {
            if ($hash[$i] === '0') {
                $matchLength++;
            } else {
                break;
            }
        }

        return pow(16, $matchLength - strlen($prefix));
    }

    public function getUserTotalPoints(int $userId): float
    {
        return ProofOfWork::where('user_id', $userId)->sum('points');
    }

    private function countTrailingZeros(string $hash): int
    {
        $prefix = '21e8';
        if (!str_starts_with($hash, $prefix)) {
            return 0;
        }

        $count = 0;
        for ($i = strlen($prefix); $i < strlen($hash) && $i < strlen($prefix) + 10; $i++) {
            if ($hash[$i] === '0') {
                $count++;
            } else {
                break;
            }
        }

        return $count;
    }
}
