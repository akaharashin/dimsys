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
        Schema::create('stok_opname', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('wilayah_id')->constrained('wilayah');
            $table->date('tanggal');
            $table->string('keterangan')->nullable();
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->foreignUuid('deleted_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('stok_opname_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('stok_opname_id')->constrained('stok_opname')->cascadeOnDelete();
            $table->foreignUuid('produk_id')->constrained('produk');
            $table->integer('stok_sistem')->default(0);
            $table->integer('stok_fisik')->default(0);
            $table->integer('selisih')->default(0);
            $table->decimal('hpp_snapshot', 10, 2)->default(0);
            $table->decimal('nilai_selisih', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_opname_details');
        Schema::dropIfExists('stok_opname');
    }
};
