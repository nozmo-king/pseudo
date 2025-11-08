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
        'username',
        'password',
        'pubkey',
        'bitcoin_address',
        'bitcoin_privkey',
        'display_name',
        'avatar_path',
        'is_admin',
        'invite_code',
    ];

    protected $hidden = [
        'password',
        'bitcoin_privkey',
    ];

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

    public function achievements()
    {
        return $this->hasMany(Achievement::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function createdInvites()
    {
        return $this->hasMany(InviteCode::class, 'created_by_user_id');
    }

    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }
}
