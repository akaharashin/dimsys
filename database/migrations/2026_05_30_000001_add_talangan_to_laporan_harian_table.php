<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A-S3: kolom 'talangan' menampung kelebihan pengeluaran outlet di atas
     * (omset - komisi). Sebelumnya selisih ini hilang karena total_setor di-max(0).
     * Dengan kolom ini total_setor tetap >= 0 (kas tidak terganggu) sementara
     * kekurangan yang harus ditalangi perusahaan tetap tercatat & bisa direkonsiliasi.
     */
    public function up(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            if (!Schema::hasColumn('laporan_harian', 'talangan')) {
                $table->decimal('talangan', 15, 2)->default(0)->after('total_pengeluaran');
            }
        });
    }

    public function down(): void
    {
        Schema::table('laporan_harian', function (Blueprint $table) {
            if (Schema::hasColumn('laporan_harian', 'talangan')) {
                $table->dropColumn('talangan');
            }
        });
    }
};
