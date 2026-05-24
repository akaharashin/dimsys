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
        Schema::create('stok_masuk_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('stok_masuk_id');
            $table->foreign('stok_masuk_id')->references('id')->on('stok_masuk')->onDelete('cascade');
            $table->uuid('produk_id');
            $table->foreign('produk_id')->references('id')->on('produk');
            $table->integer('jumlah')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_masuk_details');
    }
};
