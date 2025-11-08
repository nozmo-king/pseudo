<?php

namespace App\Services;

use App\Models\User;
use App\Models\Message;
use App\Models\Achievement;
use App\Models\ProofOfWork;

class ChatCommandService
{
    public function isCommand(string $body): bool
    {
        return str_starts_with(trim($body), '/');
    }

    public function execute(string $body, int $userId, int $chatroomId): array
    {
        $parts = explode(' ', trim($body));
        $command = strtolower(substr($parts[0], 1));
        $args = array_slice($parts, 1);

        return match ($command) {
            'statusline' => $this->statusline($userId),
            'help' => $this->help(),
            'whois' => $this->whois($args),
            'list' => $this->listUsers($chatroomId),
            'achievements' => $this->achievements($userId),
            'leaderboard' => $this->leaderboard(),
            default => ['error' => 'Unknown command. Type /help for available commands.'],
        };
    }

    private function statusline(int $userId): array
    {
        $user = User::find($userId);
        $totalPow = ProofOfWork::where('user_id', $userId)->sum('points');
        $postCount = \App\Models\Post::where('user_id', $userId)->count();
        $threadCount = \App\Models\Thread::where('user_id', $userId)->count();
        $blogCount = \App\Models\BlogPost::where('user_id', $userId)->count();
        $achievementCount = Achievement::where('user_id', $userId)->count();
        $highestAchievement = Achievement::where('user_id', $userId)
            ->orderBy('difficulty', 'desc')
            ->first();

        $diamond = $highestAchievement
            ? Achievement::getDiamondSymbol($highestAchievement->difficulty)
            : '○';

        return [
            'type' => 'system',
            'message' => sprintf(
                "%s %s | POW: %d | Posts: %d | Threads: %d | Blogs: %d | Achievements: %d/11",
                $diamond,
                $user->display_name ?: substr($user->pubkey, 0, 8),
                (int)$totalPow,
                $postCount,
                $threadCount,
                $blogCount,
                $achievementCount
            )
        ];
    }

    private function help(): array
    {
        return [
            'type' => 'system',
            'message' => implode(' | ', [
                '/statusline - your stats',
                '/whois <user> - user info',
                '/list - users in room',
                '/achievements - your diamonds',
                '/leaderboard - top miners',
                '/help - this message'
            ])
        ];
    }

    private function whois(array $args): array
    {
        if (empty($args)) {
            return ['error' => 'Usage: /whois <username or pubkey>'];
        }

        $search = implode(' ', $args);
        $user = User::where('display_name', 'like', "%{$search}%")
            ->orWhere('pubkey', 'like', "%{$search}%")
            ->first();

        if (!$user) {
            return ['error' => 'User not found'];
        }

        $totalPow = ProofOfWork::where('user_id', $user->id)->sum('points');
        $achievementCount = Achievement::where('user_id', $user->id)->count();
        $highestAchievement = Achievement::where('user_id', $user->id)
            ->orderBy('difficulty', 'desc')
            ->first();

        $diamond = $highestAchievement
            ? Achievement::getDiamondSymbol($highestAchievement->difficulty)
            : '○';

        return [
            'type' => 'system',
            'message' => sprintf(
                "%s %s | Pubkey: %s | POW: %d | Achievements: %d/11 | Admin: %s",
                $diamond,
                $user->display_name ?: 'Anonymous',
                substr($user->pubkey, 0, 16) . '...',
                (int)$totalPow,
                $achievementCount,
                $user->is_admin ? 'Yes' : 'No'
            )
        ];
    }

    private function listUsers(int $chatroomId): array
    {
        $userIds = Message::where('chatroom_id', $chatroomId)
            ->distinct()
            ->pluck('user_id');

        $users = User::whereIn('id', $userIds)
            ->get()
            ->map(function ($user) {
                $highestAchievement = Achievement::where('user_id', $user->id)
                    ->orderBy('difficulty', 'desc')
                    ->first();
                $diamond = $highestAchievement
                    ? Achievement::getDiamondSymbol($highestAchievement->difficulty)
                    : '○';
                return sprintf(
                    "%s %s",
                    $diamond,
                    $user->display_name ?: substr($user->pubkey, 0, 8)
                );
            })
            ->join(' | ');

        return [
            'type' => 'system',
            'message' => sprintf("Users in room (%d): %s", count($userIds), $users)
        ];
    }

    private function achievements(int $userId): array
    {
        $achievements = Achievement::where('user_id', $userId)
            ->orderBy('difficulty')
            ->get();

        if ($achievements->isEmpty()) {
            return [
                'type' => 'system',
                'message' => 'No achievements yet. Mine 21e8 hashes to unlock diamonds!'
            ];
        }

        $diamonds = $achievements->map(function ($achievement) {
            $diamond = Achievement::getDiamondSymbol($achievement->difficulty);
            $zeros = str_repeat('0', $achievement->difficulty);
            return sprintf(
                "%s 21e8%s (%s)",
                $diamond,
                $zeros,
                substr($achievement->hash, 0, 16)
            );
        })->join(' | ');

        return [
            'type' => 'system',
            'message' => sprintf("Your diamonds: %s", $diamonds)
        ];
    }

    private function leaderboard(): array
    {
        $top = User::select('users.*')
            ->leftJoin('proof_of_works', 'users.id', '=', 'proof_of_works.user_id')
            ->selectRaw('COALESCE(SUM(proof_of_works.points), 0) as total_pow')
            ->groupBy('users.id')
            ->orderBy('total_pow', 'desc')
            ->limit(5)
            ->get();

        $lines = $top->map(function ($user, $index) {
            $highestAchievement = Achievement::where('user_id', $user->id)
                ->orderBy('difficulty', 'desc')
                ->first();
            $diamond = $highestAchievement
                ? Achievement::getDiamondSymbol($highestAchievement->difficulty)
                : '○';
            return sprintf(
                "#%d %s %s: %d POW",
                $index + 1,
                $diamond,
                $user->display_name ?: substr($user->pubkey, 0, 8),
                (int)$user->total_pow
            );
        })->join(' | ');

        return [
            'type' => 'system',
            'message' => sprintf("Top miners: %s", $lines)
        ];
    }
}
