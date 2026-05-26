<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_pengeluaran', function (Blueprint $table) {
            $table->uuid('created_by')->nullable()->after('jumlah');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->uuid('updated_by')->nullable()->after('created_by');
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->uuid('deleted_by')->nullable()->after('updated_by');
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_pengeluaran', function (Blueprint $table) {
            $table->dropForeign(['laporan_pengeluaran_created_by_foreign']);
            $table->dropForeign(['laporan_pengeluaran_updated_by_foreign']);
            $table->dropForeign(['laporan_pengeluaran_deleted_by_foreign']);
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });
    }
};
