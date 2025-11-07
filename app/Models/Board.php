<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'name', 'description'];

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class);
    }
}
