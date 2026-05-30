<?php

namespace App\Services;

use App\Models\StokMasuk;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Sumber kebenaran TUNGGAL untuk perhitungan stok wilayah.
 *
 * Dipakai oleh: validasi Distribusi & Pindah Stok, RekapStok (Stok Freezer),
 * StokApi (form distribusi), getStokSistem (STO), dan Laporan Stok. Tujuannya
 * agar rumus stok tidak pernah divergen antara "yang divalidasi" & "yang ditampilkan".
 *
 * KONSEP:
 *  - Stok Freezer  = stok yang masih bisa DIDISTRIBUSIKAN dari freezer wilayah.
 *      cutoff = tanggal stok_masuk jenis='awal' TERAKHIR di wilayah yang <= $sampai.
 *      freezer = Σ(masuk semua jenis sejak cutoff s/d $sampai)
 *              − Σ(distribusi keluar ke gerobak sejak cutoff s/d $sampai)
 *              − Σ(transfer keluar [disetujui] sejak cutoff s/d $sampai)
 *  - Stok Gerobak  = running balance ALL-TIME s/d $sampai:
 *      Σ(distribusi out s/d $sampai) − Σ(terjual laporan harian s/d $sampai)
 *  - Total Perusahaan = Freezer + Gerobak.
 *
 * DESAIN SKALA (B-K1): query builder inti (qMasuk/qDistribusiOut/qKeluarWilayah/
 * qTerjual) dipakai BERSAMA oleh versi SINGLE (1 produk, untuk validasi) dan
 * versi BATCH (groupBy produk_id, untuk halaman rekap). Karena keduanya berasal
 * dari builder yang SAMA, hasilnya tidak mungkin berbeda. Versi batch memangkas
 * N+1 (4-6 query/produk → beberapa query total).
 *
 * Join dipakai (bukan whereHas) agar bisa groupBy. Relasi detail→induk bersifat
 * many-to-one (tiap baris detail tepat 1 induk) sehingga SUM via join = SUM via
 * whereHas. deleted_at induk difilter manual (menyamai global scope SoftDeletes).
 */
class StokService
{
    private function today(?string $sampai): string
    {
        return $sampai ?: Carbon::today()->toDateString();
    }

    /** Tanggal cutoff freezer (stok_awal terakhir <= $sampai). Null bila belum ada. */
    public function freezerCutoff(string $wilayahId, ?string $sampai = null): ?string
    {
        $sampai = $this->today($sampai);

        return StokMasuk::where('wilayah_id', $wilayahId)
            ->where('jenis', 'awal')
            ->where('tanggal', '<=', $sampai)
            ->orderByDesc('tanggal')
            ->value('tanggal');
    }

    // ───────────────────────── Query builder inti (dipakai single & batch) ─────

    /** Σ masuk ke freezer (semua jenis), wilayah, cutoff..sampai. */
    private function qMasuk(string $wilayahId, string $sampai, ?string $cutoff): Builder
    {
        return DB::table('stok_masuk_details as smd')
            ->join('stok_masuk as sm', 'sm.id', '=', 'smd.stok_masuk_id')
            ->whereNull('sm.deleted_at')
            ->where('sm.wilayah_id', $wilayahId)
            ->where('sm.tanggal', '<=', $sampai)
            ->when($cutoff, fn($q) => $q->where('sm.tanggal', '>=', $cutoff));
    }

    /** Σ distribusi keluar (freezer→gerobak), outlet di wilayah, <=sampai [>=cutoff]. */
    private function qDistribusiOut(string $wilayahId, string $sampai, ?string $cutoff = null): Builder
    {
        return DB::table('distribusi_details as dd')
            ->join('distribusi as d', 'd.id', '=', 'dd.distribusi_id')
            ->join('outlet as o', 'o.id', '=', 'd.outlet_id')
            ->whereNull('d.deleted_at')
            ->where('o.wilayah_id', $wilayahId)
            ->where('d.tanggal', '<=', $sampai)
            ->when($cutoff, fn($q) => $q->where('d.tanggal', '>=', $cutoff));
    }

    /** Σ transfer keluar ke wilayah lain (disetujui), wilayah_asal, cutoff..sampai. */
    private function qKeluarWilayah(string $wilayahId, string $sampai, ?string $cutoff): Builder
    {
        return DB::table('penjualan_wilayah_details as pwd')
            ->join('penjualan_wilayah as pw', 'pw.id', '=', 'pwd.penjualan_id')
            ->whereNull('pw.deleted_at')
            ->where('pw.wilayah_asal_id', $wilayahId)
            ->where('pw.status', 'disetujui')
            ->where('pw.tanggal', '<=', $sampai)
            ->when($cutoff, fn($q) => $q->where('pw.tanggal', '>=', $cutoff));
    }

    /** Σ terjual di gerobak (laporan harian), outlet di wilayah, <=sampai. */
    private function qTerjual(string $wilayahId, string $sampai): Builder
    {
        return DB::table('laporan_harian_details as lhd')
            ->join('laporan_harian as lh', 'lh.id', '=', 'lhd.laporan_id')
            ->join('outlet as o', 'o.id', '=', 'lh.outlet_id')
            ->whereNull('lh.deleted_at')
            ->where('o.wilayah_id', $wilayahId)
            ->where('lh.tanggal', '<=', $sampai);
    }

    /** Helper: SUM kolom untuk 1 produk dari builder inti. */
    private function sumOne(Builder $q, string $aliasCol, string $produkCol, string $produkId): int
    {
        return (int) $q->where($produkCol, $produkId)->sum($aliasCol);
    }

    /** Helper: SUM kolom per produk (groupBy) → [produk_id => total]. */
    private function sumGroup(Builder $q, string $aliasCol, string $produkCol): array
    {
        return $q->groupBy($produkCol)
            ->selectRaw("$produkCol as pid, SUM($aliasCol) as total")
            ->pluck('total', 'pid')
            ->map(fn($v) => (int) $v)
            ->all();
    }

    // ───────────────────────── SINGLE (1 produk — untuk validasi) ──────────────

    /** Rincian freezer 1 produk: ['masuk','out','keluar','freezer']. */
    public function freezerBreakdown(string $wilayahId, string $produkId, ?string $sampai = null, $cutoff = false): array
    {
        $sampai = $this->today($sampai);
        if ($cutoff === false) $cutoff = $this->freezerCutoff($wilayahId, $sampai);

        $masuk  = $this->sumOne($this->qMasuk($wilayahId, $sampai, $cutoff), 'smd.jumlah', 'smd.produk_id', $produkId);
        $out    = $this->sumOne($this->qDistribusiOut($wilayahId, $sampai, $cutoff), 'dd.jumlah_out', 'dd.produk_id', $produkId);
        $keluar = $this->sumOne($this->qKeluarWilayah($wilayahId, $sampai, $cutoff), 'pwd.jumlah', 'pwd.produk_id', $produkId);

        return ['masuk' => $masuk, 'out' => $out, 'keluar' => $keluar, 'freezer' => $masuk - $out - $keluar];
    }

    public function stokFreezer(string $wilayahId, string $produkId, ?string $sampai = null, $cutoff = false): int
    {
        return $this->freezerBreakdown($wilayahId, $produkId, $sampai, $cutoff)['freezer'];
    }

    /** Rincian gerobak 1 produk: ['out_all','terjual','gerobak']. */
    public function gerobakBreakdown(string $wilayahId, string $produkId, ?string $sampai = null): array
    {
        $sampai  = $this->today($sampai);
        $outAll  = $this->sumOne($this->qDistribusiOut($wilayahId, $sampai, null), 'dd.jumlah_out', 'dd.produk_id', $produkId);
        $terjual = $this->sumOne($this->qTerjual($wilayahId, $sampai), 'lhd.terjual', 'lhd.produk_id', $produkId);

        return ['out_all' => $outAll, 'terjual' => $terjual, 'gerobak' => $outAll - $terjual];
    }

    public function stokGerobak(string $wilayahId, string $produkId, ?string $sampai = null): int
    {
        return $this->gerobakBreakdown($wilayahId, $produkId, $sampai)['gerobak'];
    }

    public function stokTotal(string $wilayahId, string $produkId, ?string $sampai = null): int
    {
        return $this->stokFreezer($wilayahId, $produkId, $sampai)
             + $this->stokGerobak($wilayahId, $produkId, $sampai);
    }

    // ───────────────────────── BATCH (semua produk — untuk rekap) ──────────────

    /**
     * Rincian freezer SEMUA produk sekaligus (3 query, bukan 3/produk).
     * @return array<string,array{masuk:int,out:int,keluar:int,freezer:int}>
     */
    public function freezerBreakdownBatch(string $wilayahId, ?string $sampai = null, $cutoff = false): array
    {
        $sampai = $this->today($sampai);
        if ($cutoff === false) $cutoff = $this->freezerCutoff($wilayahId, $sampai);

        $masuk  = $this->sumGroup($this->qMasuk($wilayahId, $sampai, $cutoff), 'smd.jumlah', 'smd.produk_id');
        $out    = $this->sumGroup($this->qDistribusiOut($wilayahId, $sampai, $cutoff), 'dd.jumlah_out', 'dd.produk_id');
        $keluar = $this->sumGroup($this->qKeluarWilayah($wilayahId, $sampai, $cutoff), 'pwd.jumlah', 'pwd.produk_id');

        $result = [];
        foreach (array_unique([...array_keys($masuk), ...array_keys($out), ...array_keys($keluar)]) as $pid) {
            $m = $masuk[$pid] ?? 0;
            $o = $out[$pid] ?? 0;
            $k = $keluar[$pid] ?? 0;
            $result[$pid] = ['masuk' => $m, 'out' => $o, 'keluar' => $k, 'freezer' => $m - $o - $k];
        }
        return $result;
    }

    /** [produk_id => freezer] untuk semua produk. */
    public function stokFreezerBatch(string $wilayahId, ?string $sampai = null, $cutoff = false): array
    {
        return array_map(fn($r) => $r['freezer'], $this->freezerBreakdownBatch($wilayahId, $sampai, $cutoff));
    }

    /**
     * Rincian gerobak SEMUA produk sekaligus (2 query).
     * @return array<string,array{out_all:int,terjual:int,gerobak:int}>
     */
    public function gerobakBreakdownBatch(string $wilayahId, ?string $sampai = null): array
    {
        $sampai  = $this->today($sampai);
        $outAll  = $this->sumGroup($this->qDistribusiOut($wilayahId, $sampai, null), 'dd.jumlah_out', 'dd.produk_id');
        $terjual = $this->sumGroup($this->qTerjual($wilayahId, $sampai), 'lhd.terjual', 'lhd.produk_id');

        $result = [];
        foreach (array_unique([...array_keys($outAll), ...array_keys($terjual)]) as $pid) {
            $oa = $outAll[$pid] ?? 0;
            $tj = $terjual[$pid] ?? 0;
            $result[$pid] = ['out_all' => $oa, 'terjual' => $tj, 'gerobak' => $oa - $tj];
        }
        return $result;
    }

    /** [produk_id => gerobak] untuk semua produk. */
    public function stokGerobakBatch(string $wilayahId, ?string $sampai = null): array
    {
        return array_map(fn($r) => $r['gerobak'], $this->gerobakBreakdownBatch($wilayahId, $sampai));
    }

    /**
     * Nilai & qty masuk per produk pada window freezer (untuk HPP rata-rata RekapStok).
     * Window IDENTIK dengan qMasuk → konsisten dengan freezer.
     * @return array<string,array{total_nilai:float,total_qty:int}>
     */
    public function nilaiMasukBatch(string $wilayahId, ?string $sampai = null, $cutoff = false): array
    {
        $sampai = $this->today($sampai);
        if ($cutoff === false) $cutoff = $this->freezerCutoff($wilayahId, $sampai);

        return $this->qMasuk($wilayahId, $sampai, $cutoff)
            ->groupBy('smd.produk_id')
            ->selectRaw('smd.produk_id as pid, SUM(smd.jumlah * smd.hpp) as total_nilai, SUM(smd.jumlah) as total_qty')
            ->get()
            ->keyBy('pid')
            ->map(fn($r) => ['total_nilai' => (float) $r->total_nilai, 'total_qty' => (int) $r->total_qty])
            ->all();
    }
}
