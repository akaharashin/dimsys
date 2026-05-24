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
        Schema::create('penjualan_wilayah', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wilayah_asal_id');
            $table->foreign('wilayah_asal_id')->references('id')->on('wilayah');
            $table->uuid('wilayah_tujuan_id');
            $table->foreign('wilayah_tujuan_id')->references('id')->on('wilayah');
            $table->date('tanggal');
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('status_bayar', ['lunas', 'belum_lunas', 'sebagian'])->default('belum_lunas');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_wilayah');
    }
};
