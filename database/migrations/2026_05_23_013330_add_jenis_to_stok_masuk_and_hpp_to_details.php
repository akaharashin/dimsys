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
        // Tambah jenis di stok_masuk (awal = stok awal bulan, masuk = dari supplier)
        Schema::table('stok_masuk', function (Blueprint $table) {
            $table->enum('jenis', ['awal', 'masuk'])->default('masuk')->after('tanggal');
        });

        // Tambah hpp snapshot di stok_masuk_details
        Schema::table('stok_masuk_details', function (Blueprint $table) {
            $table->decimal('hpp', 10, 2)->default(0)->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('stok_masuk', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
        Schema::table('stok_masuk_details', function (Blueprint $table) {
            $table->dropColumn('hpp');
        });
    }
};
