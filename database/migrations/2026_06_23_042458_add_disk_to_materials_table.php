<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom `disk` untuk membedakan file lokal (PDF upload)
     * vs URL eksternal (YouTube, Vimeo, link lainnya).
     *
     * Juga ubah content_url menjadi `text` agar mampu menampung
     * path panjang dari storage dan URL eksternal.
     */
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            // 'local'    → file PDF yang diupload ke storage/app/public/materials/
            // 'external' → URL eksternal (YouTube, Vimeo, link biasa, dsb.)
            $table->enum('disk', ['local', 'external'])
                  ->default('external')
                  ->after('content_url');

            // Perlebar kolom agar mampu menampung path storage yang panjang
            $table->text('content_url')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('disk');
            $table->string('content_url')->change();
        });
    }
};
