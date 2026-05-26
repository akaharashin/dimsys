<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE foto_pindah_stok MODIFY COLUMN tipe ENUM('foto_real','berita_acara','video') NOT NULL");

        Schema::table('foto_pindah_stok', function (Blueprint $table) {
            $table->unsignedInteger('durasi')->nullable()->after('ukuran'); // detik
        });
    }

    public function down(): void
    {
        Schema::table('foto_pindah_stok', function (Blueprint $table) {
            $table->dropColumn('durasi');
        });

        DB::statement("ALTER TABLE foto_pindah_stok MODIFY COLUMN tipe ENUM('foto_real','berita_acara') NOT NULL");
    }
};
