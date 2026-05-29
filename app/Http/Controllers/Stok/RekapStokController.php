<?php
namespace App\Http\Controllers\Stok;

use App\Http\Controllers\Controller;
use App\Models\Wilayah;
use App\Models\Produk;
use App\Models\StokMasuk;
use App\Models\StokMasukDetail;
use App\Models\DistribusiDetail;
use App\Models\PenjualanWilayahDetail;
use App\Models\LaporanHarianDetail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Stok\RekapStokExport;

class RekapStokController extends Controller
{
    public function index(Request $request)
    {
        $wilayahList = Wilayah::where('aktif', true)->orderBy('nama')->get();

        if (auth()->user()->hasRole('koordinator')) {
            $wilayahId = auth()->user()->wilayah_id;
        } else {
            $wilayahId = $request->input('wilayah_id', auth()->user()->wilayah_id ?? $wilayahList->first()?->id);
        }

        $wilayah = Wilayah::find($wilayahId);
        $produkList = Produk::where('aktif', true)->orderBy('nama')->get();

        $rekap = collect($this->hitungStok($wilayahId, $produkList));

        // Manual pagination
        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;
        $page = $request->input('page', 1);
        $items = $rekap->forPage($page, $perPage);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $rekap->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('stok.rekap.index', compact(
            'wilayahList',
            'wilayahId',
            'wilayah',
            'paginated',
            'rekap'
        ));
    }

    public function export(Request $request)
    {
        if (auth()->user()->hasRole('koordinator')) {
            $wilayahId = auth()->user()->wilayah_id;
        } else {
            $wilayahId = $request->input('wilayah_id');
        }

        $produkList = Produk::where('aktif', true)->orderBy('nama')->get();
        $rekap = $this->hitungStok($wilayahId, $produkList);
        $wilayah = Wilayah::find($wilayahId);

        $filename = 'rekap-stok-' . ($wilayah->nama ?? 'semua') . '-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new RekapStokExport($rekap, $wilayah), $filename);
    }

    private function hitungStok($wilayahId, $produkList)
    {
        // Cutoff freezer: tanggal stok_masuk jenis='awal' terakhir di wilayah ini.
        // Setelah generate stok awal bulan baru, transaksi sebelum cutoff TIDAK ikut
        // dihitung agar tidak double-count (stok_awal sudah merepresentasikan saldo lama).
        // Gerobak tetap ALL-TIME (running balance — sisa belum terjual otomatis carry).
        $cutoff = StokMasuk::where('wilayah_id', $wilayahId)
            ->where('jenis', 'awal')
            ->orderByDesc('tanggal')
            ->value('tanggal');

        return $produkList->map(function ($produk) use ($wilayahId, $cutoff) {

            // Stok Awal + Masuk + Koreksi sejak cutoff (semua jenis)
            $masuk = StokMasukDetail::whereHas('stokMasuk', function ($q) use ($wilayahId, $cutoff) {
                $q->where('wilayah_id', $wilayahId);
                if ($cutoff) $q->whereDate('tanggal', '>=', $cutoff);
            })->where('produk_id', $produk->id)->sum('jumlah');

            // OUT ke gerobak sejak cutoff (untuk freezer)
            $outFreezer = DistribusiDetail::whereHas('distribusi', function ($q) use ($wilayahId, $cutoff) {
                $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $wilayahId));
                if ($cutoff) $q->whereDate('tanggal', '>=', $cutoff);
            })->where('produk_id', $produk->id)->sum('jumlah_out');

            // Keluar ke wilayah lain sejak cutoff
            $keluarWilayah = PenjualanWilayahDetail::whereHas('penjualan', function ($q) use ($wilayahId, $cutoff) {
                $q->where('wilayah_asal_id', $wilayahId)->where('status', 'disetujui');
                if ($cutoff) $q->whereDate('tanggal', '>=', $cutoff);
            })->where('produk_id', $produk->id)->sum('jumlah');

            // Gerobak: ALL-TIME (running balance kumulatif)
            $outAll = DistribusiDetail::whereHas('distribusi', fn($q) =>
                $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $wilayahId))
            )->where('produk_id', $produk->id)->sum('jumlah_out');

            $terjualGerobak = LaporanHarianDetail::whereHas('laporan', fn($q) =>
                $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $wilayahId))
            )->where('produk_id', $produk->id)->sum('terjual');

            // Stok di freezer wilayah sejak cutoff
            $stokFreezer = $masuk - $outFreezer - $keluarWilayah;
            // Sisa di gerobak per wilayah (ALL-TIME)
            $stokGerobak = $outAll - $terjualGerobak;
            // Total fisik perusahaan per wilayah
            $stokTotal   = $stokFreezer + $stokGerobak;

            // Nilai stok (pakai HPP rata-rata dari stok masuk sejak cutoff)
            $totalHpp = StokMasukDetail::whereHas('stokMasuk', function ($q) use ($wilayahId, $cutoff) {
                $q->where('wilayah_id', $wilayahId);
                if ($cutoff) $q->whereDate('tanggal', '>=', $cutoff);
            })->where('produk_id', $produk->id)
                ->selectRaw('SUM(jumlah * hpp) as total_nilai, SUM(jumlah) as total_qty')
                ->first();

            $hppRata = ($totalHpp->total_qty > 0)
                ? $totalHpp->total_nilai / $totalHpp->total_qty
                : $produk->hpp;

            return [
                'produk'         => $produk,
                'masuk'          => $masuk,
                'out_gerobak'    => $outFreezer,
                'keluar_wilayah' => $keluarWilayah,
                'terjual_gerobak'=> $terjualGerobak,
                'stok_akhir'     => $stokFreezer,
                'stok_freezer'   => $stokFreezer,
                'stok_gerobak'   => $stokGerobak,
                'stok_total'     => $stokTotal,
                'hpp_rata'       => round($hppRata),
                'nilai_stok'     => max(0, $stokTotal) * round($hppRata),
                'status'         => $stokTotal <= 0 ? 'habis' : ($stokTotal <= 50 ? 'menipis' : 'aman'),
            ];
        })->filter(fn($r) => $r['masuk'] > 0 || $r['stok_freezer'] != 0 || $r['stok_gerobak'] != 0);
    }
}