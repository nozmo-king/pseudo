<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create boards
        $boards = [
            ['slug' => 'gen', 'name' => 'General', 'description' => 'General discussion'],
            ['slug' => 'tech', 'name' => 'Technology', 'description' => 'Technology and programming'],
            ['slug' => 'doodle', 'name' => 'Doodle', 'description' => 'Art and doodles'],
            ['slug' => 'meta', 'name' => 'Meta', 'description' => 'Discussion about the board itself'],
        ];

        foreach ($boards as $board) {
            DB::table('boards')->insert([
                'slug' => $board['slug'],
                'name' => $board['name'],
                'description' => $board['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create default chatroom
        DB::table('chatrooms')->insert([
            'name' => 'General Chat',
            'slug' => 'general',
            'description' => 'General discussion chatroom',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
