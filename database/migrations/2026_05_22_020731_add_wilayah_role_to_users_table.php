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
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('wilayah_id')->nullable()->after('id');
            $table->foreign('wilayah_id')->references('id')->on('wilayah')->nullOnDelete();
            $table->enum('role', ['owner', 'admin_pusat', 'koordinator'])->default('koordinator')->after('wilayah_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['wilayah_id']);
            $table->dropColumn(['wilayah_id', 'role']);
        });
    }
};
