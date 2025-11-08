<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chatroom extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'created_by_user_id',
        'required_hash',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
