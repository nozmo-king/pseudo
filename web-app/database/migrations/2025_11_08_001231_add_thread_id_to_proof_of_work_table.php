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
        Schema::table('proof_of_work', function (Blueprint $table) {
            $table->foreignId('thread_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
            $table->index(['thread_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proof_of_work', function (Blueprint $table) {
            $table->dropForeign(['thread_id']);
            $table->dropColumn('thread_id');
        });
    }
};
