<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_pindah_stok', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('penjualan_wilayah_id')->constrained('penjualan_wilayah')->cascadeOnDelete();
            $table->enum('tipe', ['foto_real', 'berita_acara']);
            $table->string('nama_file');
            $table->string('nama_asli');
            $table->integer('ukuran'); // KB setelah compress
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_pindah_stok');
    }
};
