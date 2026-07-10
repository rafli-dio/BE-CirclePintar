<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom content_body (longText) ke tabel materials.
     * Kolom ini digunakan untuk menyimpan konten teks panjang secara langsung
     * (misalnya artikel, ringkasan materi, catatan pengajar, dsb.)
     * ketika tipe materi adalah 'text' dan guru memilih untuk mengetik isi,
     * bukan hanya menautkan URL eksternal.
     */
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            // Nullable agar tidak breaking untuk data materi lama (video/pdf/url)
            $table->longText('content_body')->nullable()->after('content_url');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('content_body');
        });
    }
};
