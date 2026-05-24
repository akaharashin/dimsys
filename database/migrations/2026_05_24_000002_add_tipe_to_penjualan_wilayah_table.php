<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('penjualan_wilayah', function (Blueprint $table) {
            $table->enum('tipe', ['transfer', 'penjualan'])->default('penjualan')->after('wilayah_tujuan_id');
            $table->uuid('transfer_stok_masuk_id')->nullable()->after('keterangan');
        });

        // Make status_bayar nullable so transfer type can store null
        DB::statement("ALTER TABLE penjualan_wilayah MODIFY COLUMN status_bayar ENUM('lunas','belum_lunas','sebagian') NULL DEFAULT 'belum_lunas'");
    }

    public function down(): void
    {
        DB::statement("UPDATE penjualan_wilayah SET status_bayar = 'belum_lunas' WHERE status_bayar IS NULL");
        DB::statement("ALTER TABLE penjualan_wilayah MODIFY COLUMN status_bayar ENUM('lunas','belum_lunas','sebagian') NOT NULL DEFAULT 'belum_lunas'");

        Schema::table('penjualan_wilayah', function (Blueprint $table) {
            $table->dropColumn(['tipe', 'transfer_stok_masuk_id']);
        });
    }
};
