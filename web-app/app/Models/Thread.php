<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    protected $fillable = [
        'title',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function proofOfWork()
    {
        return $this->hasMany(ProofOfWork::class);
    }

    public function getTotalPowAttribute()
    {
        return $this->proofOfWork()->sum('points');
    }
}
