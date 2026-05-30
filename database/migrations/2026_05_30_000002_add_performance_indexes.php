<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * B-S1: Index komposit & kolom non-FK untuk pola query nyata DIMSYS.
 *
 * Urutan kolom komposit: equality (wilayah_id/outlet_id/rekening_id) DULU,
 * baru range (tanggal) — agar index terpakai untuk filter wilayah+rentang tanggal.
 * Kolom FK (wilayah_id, outlet_id, produk_id, dsb) SUDAH ter-index otomatis
 * oleh foreign key, jadi tidak diduplikasi.
 *
 * Idempotent: index yang sudah ada dilewati.
 */
return new class extends Migration
{
    private array $plan = [
        // freezer cutoff: WHERE wilayah_id & jenis='awal' & tanggal<= ; masuk: wilayah_id & tanggal<=
        'stok_masuk' => [
            ['sm_wilayah_tanggal_idx', ['wilayah_id', 'tanggal']],
            ['sm_wilayah_jenis_tanggal_idx', ['wilayah_id', 'jenis', 'tanggal']],
            ['sm_tanggal_idx', ['tanggal']],
            ['sm_deleted_at_idx', ['deleted_at']],
        ],
        // join stok_masuk + group by produk_id
        'stok_masuk_details' => [
            ['smd_masuk_produk_idx', ['stok_masuk_id', 'produk_id']],
        ],
        // distribusi out: join outlet(wilayah) & tanggal<= ; index listing by outlet+tanggal
        'distribusi' => [
            ['dist_outlet_tanggal_idx', ['outlet_id', 'tanggal']],
            ['dist_tanggal_idx', ['tanggal']],
            ['dist_deleted_at_idx', ['deleted_at']],
        ],
        'distribusi_details' => [
            ['dd_dist_produk_idx', ['distribusi_id', 'produk_id']],
        ],
        // laporan: by outlet+tanggal ; filter status ; gerobak terjual join
        'laporan_harian' => [
            ['lh_outlet_tanggal_idx', ['outlet_id', 'tanggal']],
            ['lh_tanggal_status_idx', ['tanggal', 'status']],
            ['lh_deleted_at_idx', ['deleted_at']],
        ],
        'laporan_harian_details' => [
            ['lhd_laporan_produk_idx', ['laporan_id', 'produk_id']],
        ],
        // freezer keluar wilayah: wilayah_asal & status='disetujui' & tanggal<= ; approval by tujuan+tanggal
        'penjualan_wilayah' => [
            ['pw_asal_status_tanggal_idx', ['wilayah_asal_id', 'status', 'tanggal']],
            ['pw_tujuan_tanggal_idx', ['wilayah_tujuan_id', 'tanggal']],
            ['pw_tanggal_status_idx', ['tanggal', 'status']],
            ['pw_tipe_idx', ['tipe']],
            ['pw_deleted_at_idx', ['deleted_at']],
        ],
        'penjualan_wilayah_details' => [
            ['pwd_penjualan_produk_idx', ['penjualan_id', 'produk_id']],
        ],
        // kas: saldo berjalan by rekening+tanggal kronologis ; filter tipe/kategori
        'kas' => [
            ['kas_rekening_tanggal_idx', ['rekening_id', 'tanggal']],
            ['kas_tanggal_tipe_idx', ['tanggal', 'tipe']],
            ['kas_kategori_idx', ['kategori']],
            ['kas_deleted_at_idx', ['deleted_at']],
        ],
        // master: where aktif=true (dropdown)
        'outlet'  => [['outlet_aktif_idx', ['aktif']]],
        'produk'  => [['produk_aktif_idx', ['aktif']]],
        'wilayah' => [['wilayah_aktif_idx', ['aktif']]],
    ];

    public function up(): void
    {
        foreach ($this->plan as $table => $indexes) {
            if (!Schema::hasTable($table)) continue;
            $existing = $this->existingIndexNames($table);
            Schema::table($table, function (Blueprint $t) use ($indexes, $existing) {
                foreach ($indexes as [$name, $cols]) {
                    if (!in_array($name, $existing, true)) {
                        $t->index($cols, $name);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->plan as $table => $indexes) {
            if (!Schema::hasTable($table)) continue;
            $existing = $this->existingIndexNames($table);
            Schema::table($table, function (Blueprint $t) use ($indexes, $existing) {
                foreach ($indexes as [$name, $cols]) {
                    if (in_array($name, $existing, true)) {
                        $t->dropIndex($name);
                    }
                }
            });
        }
    }

    private function existingIndexNames(string $table): array
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')->unique()->all();
    }
};
