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

        [$awalBulan, $akhirBulan] = \App\Support\Periode::range($bulan);

        $produkList = Produk::where('aktif', true)->orderBy('nama')->get();

        // B-K1: batch — hitung SEKALI untuk SEMUA produk (groupBy), bukan 5 query/produk.
        // Formula Laporan Stok = mutasi FREEZER per-bulan (split jenis awal/masuk/koreksi),
        // BERBEDA dari StokService (cutoff). keluarWilayah SENGAJA tanpa filter status
        // (mengikuti perilaku lama). Wilayah hanya difilter bila bukan 'semua'.
        $useWilayah = $wilayahId !== 'semua';

        $masukRows = \Illuminate\Support\Facades\DB::table('stok_masuk_details as smd')
            ->join('stok_masuk as sm', 'sm.id', '=', 'smd.stok_masuk_id')
            ->whereNull('sm.deleted_at')
            ->whereBetween('sm.tanggal', [$awalBulan, $akhirBulan])
            ->when($useWilayah, fn($q) => $q->where('sm.wilayah_id', $wilayahId))
            ->groupBy('smd.produk_id', 'sm.jenis')
            ->selectRaw('smd.produk_id as pid, sm.jenis as jenis, SUM(smd.jumlah) as total')
            ->get();
        $masukMap = []; // [pid][jenis] => total
        foreach ($masukRows as $r) {
            $masukMap[$r->pid][$r->jenis] = (int) $r->total;
        }

        $outMap = \Illuminate\Support\Facades\DB::table('distribusi_details as dd')
            ->join('distribusi as d', 'd.id', '=', 'dd.distribusi_id')
            ->join('outlet as o', 'o.id', '=', 'd.outlet_id')
            ->whereNull('d.deleted_at')
            ->whereBetween('d.tanggal', [$awalBulan, $akhirBulan])
            ->when($useWilayah, fn($q) => $q->where('o.wilayah_id', $wilayahId))
            ->groupBy('dd.produk_id')
            ->selectRaw('dd.produk_id as pid, SUM(dd.jumlah_out) as total')
            ->pluck('total', 'pid');

        $keluarMap = \Illuminate\Support\Facades\DB::table('penjualan_wilayah_details as pwd')
            ->join('penjualan_wilayah as pw', 'pw.id', '=', 'pwd.penjualan_id')
            ->whereNull('pw.deleted_at')
            ->whereBetween('pw.tanggal', [$awalBulan, $akhirBulan])
            ->when($useWilayah, fn($q) => $q->where('pw.wilayah_asal_id', $wilayahId))
            ->groupBy('pwd.produk_id')
            ->selectRaw('pwd.produk_id as pid, SUM(pwd.jumlah) as total')
            ->pluck('total', 'pid');

        $rekap = $produkList->map(function ($produk) use ($masukMap, $outMap, $keluarMap) {

            $stokAwal      = $masukMap[$produk->id]['awal'] ?? 0;
            $masuk         = $masukMap[$produk->id]['masuk'] ?? 0;
            $koreksi       = $masukMap[$produk->id]['koreksi'] ?? 0;
            $out           = (int) ($outMap[$produk->id] ?? 0);
            $keluarWilayah = (int) ($keluarMap[$produk->id] ?? 0);

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