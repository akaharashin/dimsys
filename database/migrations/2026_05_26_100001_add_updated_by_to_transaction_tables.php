<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['stok_masuk', 'distribusi', 'laporan_harian', 'kas', 'penjualan_wilayah', 'stok_opname'];

        foreach ($tables as $tableName) {
            if (!Schema::hasColumn($tableName, 'updated_by')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->uuid('updated_by')->nullable()->after('created_by');
                    $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
                });
            }
        }

        // Fix kas: add deleted_by if not already present
        if (!Schema::hasColumn('kas', 'deleted_by')) {
            Schema::table('kas', function (Blueprint $table) {
                $table->uuid('deleted_by')->nullable()->after('updated_by');
                $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tables = ['stok_masuk', 'distribusi', 'laporan_harian', 'kas', 'penjualan_wilayah', 'stok_opname'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropForeign([$tableName . '_updated_by_foreign']);
                $table->dropColumn('updated_by');
            });
        }

        Schema::table('kas', function (Blueprint $table) {
            $table->dropForeign(['kas_deleted_by_foreign']);
            $table->dropColumn('deleted_by');
        });
    }
};
