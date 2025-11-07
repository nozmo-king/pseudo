<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ChorumPost extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'content', 'points', 'is_published'];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proofOfWorks(): MorphMany
    {
        return $this->morphMany(ProofOfWork::class, 'powable');
    }
}
