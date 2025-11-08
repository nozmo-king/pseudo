<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteCode extends Model
{
    protected $fillable = [
        'code',
        'created_by_user_id',
        'used_by_user_id',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }

    public function scopeUnused($query)
    {
        return $query->whereNull('used_at');
    }
}
