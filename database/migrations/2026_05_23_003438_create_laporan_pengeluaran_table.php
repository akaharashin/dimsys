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
        Schema::create('laporan_pengeluaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('laporan_id');
            $table->foreign('laporan_id')->references('id')->on('laporan_harian')->onDelete('cascade');
            $table->string('keterangan');
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_pengeluaran');
    }
};
