<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = [
        'user_id',
        'difficulty',
        'hash',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getDiamondSymbol(int $difficulty): string
    {
        $symbols = ['◇', '◆', '◈', '♦', '⬙', '⬘', '⯁', '⬖', '⬗', '⬢', '⬣'];
        return $symbols[$difficulty] ?? '⬣';
    }

    public static function getDifficultyName(int $difficulty): string
    {
        $zeros = str_repeat('0', $difficulty);
        return "21e8{$zeros}";
    }
}
