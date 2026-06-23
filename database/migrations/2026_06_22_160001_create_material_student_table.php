<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel pencatat progres belajar siswa per materi.
     * Menyimpan status selesai atau belum selesai setiap materi.
     */
    public function up(): void
    {
        Schema::create('material_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')
                  ->constrained('materials')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();

            // Satu siswa hanya punya satu record progres per materi
            $table->unique(['material_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_student');
    }
};
