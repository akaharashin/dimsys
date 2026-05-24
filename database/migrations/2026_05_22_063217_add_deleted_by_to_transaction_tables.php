<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['stok_masuk', 'distribusi', 'laporan_harian', 'penjualan_wilayah', 'kas'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->uuid('deleted_by')->nullable()->after('deleted_at');
                $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tables = ['stok_masuk', 'distribusi', 'laporan_harian', 'penjualan_wilayah', 'kas'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['deleted_by']);
                $table->dropColumn('deleted_by');
            });
        }
    }
};
