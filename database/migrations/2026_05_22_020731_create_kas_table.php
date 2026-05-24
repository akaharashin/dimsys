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
        Schema::create('kas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('rekening_id');
            $table->foreign('rekening_id')->references('id')->on('rekening');
            $table->uuid('outlet_id')->nullable();
            $table->foreign('outlet_id')->references('id')->on('outlet')->nullOnDelete();
            $table->date('tanggal');
            $table->enum('tipe', ['debit', 'kredit']);
            $table->string('kategori');
            $table->string('keterangan')->nullable();
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->decimal('saldo', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas');
    }
};
