<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel pivot antara users dan badges.
     * Menyimpan badge yang telah diraih oleh setiap siswa.
     */
    public function up(): void
    {
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('badge_id')
                  ->constrained('badges')
                  ->cascadeOnDelete();
            $table->timestamp('earned_at')->useCurrent();

            // Satu siswa hanya bisa mendapatkan badge yang sama satu kali
            $table->unique(['user_id', 'badge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};
