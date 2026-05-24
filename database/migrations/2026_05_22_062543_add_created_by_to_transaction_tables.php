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
        Schema::table('stok_masuk', function (Blueprint $table) {
            $table->uuid('created_by')->nullable()->after('keterangan');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
        Schema::table('distribusi', function (Blueprint $table) {
            $table->uuid('created_by')->nullable()->after('keterangan');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->uuid('created_by')->nullable()->after('status');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
        Schema::table('penjualan_wilayah', function (Blueprint $table) {
            $table->uuid('created_by')->nullable()->after('keterangan');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
        Schema::table('kas', function (Blueprint $table) {
            $table->uuid('created_by')->nullable()->after('saldo');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stok_masuk', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
        Schema::table('distribusi', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
        Schema::table('penjualan_wilayah', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
        Schema::table('kas', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
