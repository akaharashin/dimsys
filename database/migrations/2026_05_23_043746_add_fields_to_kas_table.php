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
        Schema::table('kas', function (Blueprint $table) {
            $table->string('sub_kategori')->nullable()->after('kategori');
            $table->string('penerima')->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('kas', function (Blueprint $table) {
            $table->dropColumn(['sub_kategori', 'penerima']);
        });
    }
};
