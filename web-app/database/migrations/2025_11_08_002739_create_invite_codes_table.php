<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invite_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('used_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index('code');
            $table->index('created_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invite_codes');
    }
};
