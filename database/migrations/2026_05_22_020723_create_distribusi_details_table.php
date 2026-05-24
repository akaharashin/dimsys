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
        Schema::create('distribusi_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('distribusi_id');
            $table->foreign('distribusi_id')->references('id')->on('distribusi')->onDelete('cascade');
            $table->uuid('produk_id');
            $table->foreign('produk_id')->references('id')->on('produk');
            $table->integer('jumlah_out')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribusi_details');
    }
};
