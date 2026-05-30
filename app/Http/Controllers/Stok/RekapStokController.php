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
use App\Services\StokService;
use Carbon\Carbon;
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
        // Formula stok dipusatkan di StokService agar IDENTIK dengan validasi
        // distribusi/pindah-stok, StokApi, dan getStokSistem (tidak lagi divergen).
        // Posisi "saat ini" = sampai hari ini; cutoff freezer juga dibatasi <= hari ini
        // supaya stok_awal bulan depan (yang boleh di-generate lebih awal) tidak ikut.
        // B-K1: hitung SEKALI untuk SEMUA produk (batch groupBy), bukan 4-6 query/produk.
        $svc    = new StokService();
        $sampai = Carbon::today()->toDateString();
        $cutoff = $svc->freezerCutoff($wilayahId, $sampai);

        $freezerBatch = $svc->freezerBreakdownBatch($wilayahId, $sampai, $cutoff);
        $gerobakBatch = $svc->gerobakBreakdownBatch($wilayahId, $sampai);
        $nilaiBatch   = $svc->nilaiMasukBatch($wilayahId, $sampai, $cutoff);

        return $produkList->map(function ($produk) use ($freezerBatch, $gerobakBatch, $nilaiBatch) {

            $fz = $freezerBatch[$produk->id] ?? ['masuk' => 0, 'out' => 0, 'keluar' => 0, 'freezer' => 0];
            $gb = $gerobakBatch[$produk->id] ?? ['out_all' => 0, 'terjual' => 0, 'gerobak' => 0];

            $masuk          = $fz['masuk'];
            $outFreezer     = $fz['out'];
            $keluarWilayah  = $fz['keluar'];
            $stokFreezer    = $fz['freezer'];
            $terjualGerobak = $gb['terjual'];
            $stokGerobak    = $gb['gerobak'];
            $stokTotal      = $stokFreezer + $stokGerobak;

            // Nilai stok (HPP rata-rata dari stok masuk pada window yang SAMA: cutoff..sampai)
            $nilai = $nilaiBatch[$produk->id] ?? ['total_nilai' => 0, 'total_qty' => 0];

            $hppRata = ($nilai['total_qty'] > 0)
                ? $nilai['total_nilai'] / $nilai['total_qty']
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