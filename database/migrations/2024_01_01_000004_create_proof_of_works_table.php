<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proof_of_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id')->index(); // For anonymous users
            $table->string('puzzle_difficulty'); // 21e8, 21e80, etc.
            $table->string('hash');
            $table->string('nonce');
            $table->integer('points');
            $table->string('ip_address', 45)->nullable();
            $table->morphs('powable'); // threads or posts
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proof_of_works');
    }
};
