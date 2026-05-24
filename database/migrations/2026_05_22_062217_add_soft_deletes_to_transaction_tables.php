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
        Schema::table('distribusi', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('penjualan_wilayah', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('kas', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('distribusi', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('laporan_harian', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('penjualan_wilayah', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('kas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
