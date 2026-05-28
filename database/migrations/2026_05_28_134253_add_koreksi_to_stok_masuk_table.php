<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE stok_masuk MODIFY jenis ENUM('awal','masuk','koreksi') NOT NULL DEFAULT 'masuk'");

        Schema::table('stok_masuk', function (Blueprint $table) {
            $table->uuid('stok_opname_id')->nullable()->after('jenis');
            $table->foreign('stok_opname_id')
                ->references('id')->on('stok_opname')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stok_masuk', function (Blueprint $table) {
            $table->dropForeign(['stok_opname_id']);
            $table->dropColumn('stok_opname_id');
        });

        DB::statement("ALTER TABLE stok_masuk MODIFY jenis ENUM('awal','masuk') NOT NULL DEFAULT 'masuk'");
    }
};
