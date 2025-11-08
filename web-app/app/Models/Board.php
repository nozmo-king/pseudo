<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'position',
    ];

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
