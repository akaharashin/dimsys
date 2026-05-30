<?php

namespace App\Services;

use App\Models\StokMasuk;
use App\Models\StokMasukDetail;
use App\Models\DistribusiDetail;
use App\Models\PenjualanWilayahDetail;
use App\Models\LaporanHarianDetail;
use Carbon\Carbon;

/**
 * Sumber kebenaran TUNGGAL untuk perhitungan stok wilayah.
 *
 * Dipakai oleh: validasi Distribusi & Pindah Stok, RekapStok (Stok Freezer),
 * StokApi (form distribusi), dan getStokSistem (STO). Tujuannya agar rumus
 * stok tidak pernah lagi divergen antara "yang divalidasi" dan "yang ditampilkan".
 *
 * KONSEP:
 *  - Stok Freezer  = stok yang masih bisa DIDISTRIBUSIKAN dari freezer wilayah.
 *      cutoff = tanggal stok_masuk jenis='awal' TERAKHIR di wilayah yang <= $sampai.
 *      freezer = Σ(masuk semua jenis sejak cutoff s/d $sampai)
 *              − Σ(distribusi keluar ke gerobak sejak cutoff s/d $sampai)
 *              − Σ(transfer keluar ke wilayah lain [disetujui] sejak cutoff s/d $sampai)
 *      Cutoff mencegah double-count: stok_awal bulan baru sudah merepresentasikan
 *      saldo akhir bulan lama, jadi transaksi sebelum cutoff tidak dihitung lagi.
 *      Batas `<= $sampai` mencegah stok_awal bulan DEPAN (yang boleh di-generate
 *      lebih awal) ikut terhitung di posisi hari ini.
 *
 *  - Stok Gerobak  = sisa barang yang sudah turun ke gerobak tapi belum terjual.
 *      running balance ALL-TIME (kumulatif) s/d $sampai:
 *      gerobak = Σ(distribusi out s/d $sampai) − Σ(terjual laporan harian s/d $sampai)
 *
 *  - Total Perusahaan = Freezer + Gerobak.
 */
class StokService
{
    /** Tanggal cutoff freezer (stok_awal terakhir <= $sampai). Null bila belum ada. */
    public function freezerCutoff(string $wilayahId, ?string $sampai = null): ?string
    {
        $sampai = $sampai ?: Carbon::today()->toDateString();

        return StokMasuk::where('wilayah_id', $wilayahId)
            ->where('jenis', 'awal')
            ->whereDate('tanggal', '<=', $sampai)
            ->orderByDesc('tanggal')
            ->value('tanggal');
    }

    /**
     * Rincian freezer satu produk: ['masuk','out','keluar','freezer'].
     * $cutoff bisa diisi (sudah dihitung di luar loop) agar tidak query berulang;
     * biarkan `false` untuk dihitung otomatis.
     */
    public function freezerBreakdown(string $wilayahId, string $produkId, ?string $sampai = null, $cutoff = false): array
    {
        $sampai = $sampai ?: Carbon::today()->toDateString();
        if ($cutoff === false) {
            $cutoff = $this->freezerCutoff($wilayahId, $sampai);
        }

        $masuk = StokMasukDetail::whereHas('stokMasuk', function ($q) use ($wilayahId, $sampai, $cutoff) {
            $q->where('wilayah_id', $wilayahId)->whereDate('tanggal', '<=', $sampai);
            if ($cutoff) $q->whereDate('tanggal', '>=', $cutoff);
        })->where('produk_id', $produkId)->sum('jumlah');

        $out = DistribusiDetail::whereHas('distribusi', function ($q) use ($wilayahId, $sampai, $cutoff) {
            $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $wilayahId))
              ->whereDate('tanggal', '<=', $sampai);
            if ($cutoff) $q->whereDate('tanggal', '>=', $cutoff);
        })->where('produk_id', $produkId)->sum('jumlah_out');

        $keluar = PenjualanWilayahDetail::whereHas('penjualan', function ($q) use ($wilayahId, $sampai, $cutoff) {
            $q->where('wilayah_asal_id', $wilayahId)->where('status', 'disetujui')
              ->whereDate('tanggal', '<=', $sampai);
            if ($cutoff) $q->whereDate('tanggal', '>=', $cutoff);
        })->where('produk_id', $produkId)->sum('jumlah');

        return [
            'masuk'   => (int) $masuk,
            'out'     => (int) $out,
            'keluar'  => (int) $keluar,
            'freezer' => (int) ($masuk - $out - $keluar),
        ];
    }

    /** Stok freezer (yang bisa didistribusikan) satu produk. */
    public function stokFreezer(string $wilayahId, string $produkId, ?string $sampai = null, $cutoff = false): int
    {
        return $this->freezerBreakdown($wilayahId, $produkId, $sampai, $cutoff)['freezer'];
    }

    /** Rincian gerobak satu produk: ['out_all','terjual','gerobak']. */
    public function gerobakBreakdown(string $wilayahId, string $produkId, ?string $sampai = null): array
    {
        $sampai = $sampai ?: Carbon::today()->toDateString();

        $outAll = DistribusiDetail::whereHas('distribusi', fn($q) =>
            $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $wilayahId))
              ->whereDate('tanggal', '<=', $sampai)
        )->where('produk_id', $produkId)->sum('jumlah_out');

        $terjual = LaporanHarianDetail::whereHas('laporan', fn($q) =>
            $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $wilayahId))
              ->whereDate('tanggal', '<=', $sampai)
        )->where('produk_id', $produkId)->sum('terjual');

        return [
            'out_all' => (int) $outAll,
            'terjual' => (int) $terjual,
            'gerobak' => (int) ($outAll - $terjual),
        ];
    }

    /** Sisa stok gerobak satu produk. */
    public function stokGerobak(string $wilayahId, string $produkId, ?string $sampai = null): int
    {
        return $this->gerobakBreakdown($wilayahId, $produkId, $sampai)['gerobak'];
    }

    /** Total fisik perusahaan (freezer + gerobak) satu produk. */
    public function stokTotal(string $wilayahId, string $produkId, ?string $sampai = null): int
    {
        return $this->stokFreezer($wilayahId, $produkId, $sampai)
             + $this->stokGerobak($wilayahId, $produkId, $sampai);
    }
}
