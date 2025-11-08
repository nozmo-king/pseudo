<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pubkey',
        'display_name',
        'avatar_path',
    ];

    protected $hidden = [];

    protected function casts(): array
    {
        return [];
    }

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
