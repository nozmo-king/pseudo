<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProofOfWork extends Model
{
    protected $table = 'proof_of_work';

    protected $fillable = [
        'user_id',
        'thread_id',
        'challenge',
        'nonce',
        'hash',
        'difficulty',
        'points',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public static function calculatePoints($hash)
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

        $extraZeros = $matchLength - strlen($prefix);
        return 15 * pow(4, $extraZeros);
    }
}
