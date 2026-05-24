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
        Schema::create('penjualan_wilayah_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('penjualan_id');
            $table->foreign('penjualan_id')->references('id')->on('penjualan_wilayah')->onDelete('cascade');
            $table->uuid('produk_id');
            $table->foreign('produk_id')->references('id')->on('produk');
            $table->integer('jumlah')->default(0);
            $table->decimal('harga_agen', 10, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_wilayah_details');
    }
};
