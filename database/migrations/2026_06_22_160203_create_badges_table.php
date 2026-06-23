<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel definisi badge/penghargaan.
     *
     * Tipe badge (badge_type):
     *   - quiz_score       : Diberikan saat siswa mendapat skor >= requirement_value pada kuis apapun.
     *                        Contoh: "Nilai Sempurna" → requirement_value = 100
     *   - xp_milestone     : Diberikan saat total XP siswa mencapai >= requirement_value.
     *                        Contoh: "XP Legend" → requirement_value = 1000
     *   - course_complete  : Diberikan saat siswa menyelesaikan >= requirement_value kelas (100% materi).
     *                        Contoh: "Rajin Belajar" → requirement_value = 3
     */
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description');
            $table->string('icon')->nullable()->comment('Nama file ikon atau URL gambar badge');
            $table->enum('badge_type', ['quiz_score', 'xp_milestone', 'course_complete']);
            $table->unsignedInteger('requirement_value')->comment('Nilai threshold untuk mendapatkan badge');
            $table->unsignedInteger('reward_xp')->default(0)->comment('Bonus XP saat badge diterima');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
