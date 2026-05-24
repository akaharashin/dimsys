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
        Schema::create('laporan_harian_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('laporan_id');
            $table->foreign('laporan_id')->references('id')->on('laporan_harian')->onDelete('cascade');
            $table->uuid('produk_id');
            $table->foreign('produk_id')->references('id')->on('produk');
            $table->integer('sisa')->default(0);
            $table->integer('terjual')->default(0);
            $table->decimal('omset', 15, 2)->default(0);
            $table->decimal('modal', 15, 2)->default(0);
            $table->decimal('komisi', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_harian_details');
    }
};
