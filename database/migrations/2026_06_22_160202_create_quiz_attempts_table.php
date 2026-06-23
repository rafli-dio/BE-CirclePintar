<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel riwayat pengerjaan kuis oleh siswa.
     * Siswa boleh mengerjakan kuis lebih dari sekali (tanpa unique constraint).
     */
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')
                  ->constrained('quizzes')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->unsignedInteger('score')->default(0)->comment('Nilai 0-100');
            $table->unsignedInteger('earned_xp')->default(0)->comment('XP yang diperoleh dari attempt ini');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
