<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outlet', function (Blueprint $table) {
            $table->text('alamat_lengkap')->nullable()->after('tipe');
            $table->decimal('latitude', 10, 8)->nullable()->after('alamat_lengkap');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('outlet', function (Blueprint $table) {
            $table->dropColumn(['alamat_lengkap', 'latitude', 'longitude']);
        });
    }
};
