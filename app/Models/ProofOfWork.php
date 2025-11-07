<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProofOfWork extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'puzzle_difficulty',
        'hash',
        'nonce',
        'points',
        'ip_address',
        'powable_id',
        'powable_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function powable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function getPointsForDifficulty(string $difficulty): int
    {
        $pointsMap = [
            '21e8' => 5,
            '21e80' => 15,
            '21e800' => 45,
            '21e8000' => 100,
            '21e80000' => 675,
            '21e800000' => 1000,
            '21e8000000' => 5000,
            '21e800000000' => 25000,
        ];

        return $pointsMap[$difficulty] ?? 0;
    }

    public static function verifyHash(string $data, string $nonce, string $difficulty): bool
    {
        $hash = hash('sha256', $data . $nonce);
        return str_starts_with($hash, $difficulty);
    }
}
