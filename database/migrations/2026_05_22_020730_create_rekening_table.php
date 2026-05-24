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
        Schema::create('rekening', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wilayah_id');
            $table->foreign('wilayah_id')->references('id')->on('wilayah');
            $table->string('nama');
            $table->enum('tipe', ['kas_kecil', 'bank'])->default('kas_kecil');
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekening');
    }
};
