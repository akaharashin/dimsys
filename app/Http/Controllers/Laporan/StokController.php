<?php
namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\StokMasuk;
use App\Models\Distribusi;
use App\Models\PenjualanWilayah;
use App\Models\Produk;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Laporan\StokExport;


class StokController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', Carbon::now()->format('Y-m'));

        if (auth()->user()->hasRole('koordinator')) {
            $wilayahId = auth()->user()->wilayah_id;
            $wilayahList = Wilayah::where('id', $wilayahId)->get();
        } else {
            $wilayahId = $request->input('wilayah_id', 'semua');
            $wilayahList = Wilayah::where('aktif', true)->orderBy('nama')->get();
        }

        [$tahun, $bln] = explode('-', $bulan);

        $produkList = Produk::where('aktif', true)->orderBy('nama')->get();

        $rekap = $produkList->map(function ($produk) use ($tahun, $bln, $wilayahId) {

            // Stok Awal (jenis = 'awal' di bulan tersebut)
            $stokAwal = \App\Models\StokMasukDetail::whereHas('stokMasuk', function ($q) use ($tahun, $bln, $wilayahId) {
                $q->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bln)
                    ->where('jenis', 'awal');
                if ($wilayahId !== 'semua')
                    $q->where('wilayah_id', $wilayahId);
            })->where('produk_id', $produk->id)->sum('jumlah');

            // Stok Masuk dari supplier (jenis = 'masuk')
            $masuk = \App\Models\StokMasukDetail::whereHas('stokMasuk', function ($q) use ($tahun, $bln, $wilayahId) {
                $q->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bln)
                    ->where('jenis', 'masuk');
                if ($wilayahId !== 'semua')
                    $q->where('wilayah_id', $wilayahId);
            })->where('produk_id', $produk->id)->sum('jumlah');

            // Koreksi STO (jenis = 'koreksi')
            $koreksi = \App\Models\StokMasukDetail::whereHas('stokMasuk', function ($q) use ($tahun, $bln, $wilayahId) {
                $q->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bln)
                    ->where('jenis', 'koreksi');
                if ($wilayahId !== 'semua')
                    $q->where('wilayah_id', $wilayahId);
            })->where('produk_id', $produk->id)->sum('jumlah');

            // OUT ke gerobak
            $out = \App\Models\DistribusiDetail::whereHas('distribusi', function ($q) use ($tahun, $bln, $wilayahId) {
                $q->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bln);
                if ($wilayahId !== 'semua') {
                    $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $wilayahId));
                }
            })->where('produk_id', $produk->id)->sum('jumlah_out');

            // Keluar ke wilayah lain
            $keluarWilayah = \App\Models\PenjualanWilayahDetail::whereHas('penjualan', function ($q) use ($tahun, $bln, $wilayahId) {
                $q->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bln);
                if ($wilayahId !== 'semua')
                    $q->where('wilayah_asal_id', $wilayahId);
            })->where('produk_id', $produk->id)->sum('jumlah');

            $terjual = $out + $keluarWilayah;
            $sisa = ($stokAwal + $masuk + $koreksi) - $terjual;
            $hpp = $produk->hpp;
            $nilaiSisa = max(0, $sisa) * $hpp;

            return [
                'produk' => $produk,
                'stok_awal' => $stokAwal,
                'masuk' => $masuk,
                'koreksi' => $koreksi,
                'terjual' => $terjual,
                'sisa' => $sisa,
                'hpp' => $hpp,
                'nilai_sisa' => $nilaiSisa,
            ];
        })->filter(fn($r) => $r['stok_awal'] > 0 || $r['masuk'] > 0 || $r['koreksi'] != 0 || $r['terjual'] > 0);

        return view('laporan.stok', compact(
            'bulan',
            'wilayahId',
            'wilayahList',
            'rekap'
        ));
    }
    public function export(Request $request)
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $wilayahId = $request->input('wilayah_id', 'semua');
        if (auth()->user()->hasRole('koordinator')) {
            $wilayahId = auth()->user()->wilayah_id;
        }
        return Excel::download(new StokExport($bulan, $wilayahId), 'rekap-stok-' . $bulan . '.xlsx');
    }
}