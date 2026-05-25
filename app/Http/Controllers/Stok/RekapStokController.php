<?php
namespace App\Http\Controllers\Stok;

use App\Http\Controllers\Controller;
use App\Models\Wilayah;
use App\Models\Produk;
use App\Models\StokMasukDetail;
use App\Models\DistribusiDetail;
use App\Models\PenjualanWilayahDetail;
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
        return $produkList->map(function ($produk) use ($wilayahId) {

            // Stok Awal + Masuk dari supplier
            $masuk = StokMasukDetail::whereHas(
                'stokMasuk',
                fn($q) =>
                $q->where('wilayah_id', $wilayahId)
            )->where('produk_id', $produk->id)->sum('jumlah');

            // OUT ke gerobak lokal (distribusi)
            $out = DistribusiDetail::whereHas(
                'distribusi',
                fn($q) =>
                $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $wilayahId))
            )->where('produk_id', $produk->id)->sum('jumlah_out');

            // Keluar ke wilayah lain (hanya yang sudah disetujui)
            $keluarWilayah = PenjualanWilayahDetail::whereHas(
                'penjualan',
                fn($q) =>
                $q->where('wilayah_asal_id', $wilayahId)->where('status', 'disetujui')
            )->where('produk_id', $produk->id)->sum('jumlah');

            $stokAkhir = $masuk - $out - $keluarWilayah;

            // Nilai stok (pakai HPP rata-rata dari stok masuk)
            $totalHpp = StokMasukDetail::whereHas(
                'stokMasuk',
                fn($q) =>
                $q->where('wilayah_id', $wilayahId)
            )->where('produk_id', $produk->id)
                ->selectRaw('SUM(jumlah * hpp) as total_nilai, SUM(jumlah) as total_qty')
                ->first();

            $hppRata = ($totalHpp->total_qty > 0)
                ? $totalHpp->total_nilai / $totalHpp->total_qty
                : $produk->hpp;

            return [
                'produk' => $produk,
                'masuk' => $masuk,
                'out_gerobak' => $out,
                'keluar_wilayah' => $keluarWilayah,
                'stok_akhir' => $stokAkhir,
                'hpp_rata' => round($hppRata),
                'nilai_stok' => max(0, $stokAkhir) * round($hppRata),
                'status' => $stokAkhir <= 0 ? 'habis' : ($stokAkhir <= 50 ? 'menipis' : 'aman'),
            ];
        })->filter(fn($r) => $r['masuk'] > 0 || $r['stok_akhir'] != 0);
    }
}