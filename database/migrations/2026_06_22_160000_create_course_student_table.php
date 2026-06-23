<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel pivot many-to-many antara courses dan users (students).
     * Menyimpan data pendaftaran kelas oleh siswa.
     */
    public function up(): void
    {
        Schema::create('course_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')
                  ->constrained('courses')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamp('enrolled_at')->useCurrent();

            // Satu siswa hanya bisa daftar ke satu kelas yang sama sekali saja
            $table->unique(['course_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_student');
    }
};
