<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanHarian;
use App\Models\Distribusi;
use App\Models\StokMasuk;
use App\Models\Kas;
use App\Models\Outlet;
use App\Models\Wilayah;
use App\Models\PenjualanWilayah;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        Carbon::setLocale('id');
        $today = Carbon::today()->toDateString();
        [$awalBulan, $akhirBulan] = \App\Support\Periode::range(Carbon::now()->format('Y-m'));

        $user = auth()->user();
        $wilayahId = $user->hasRole('koordinator') ? $user->wilayah_id : null;
        $scopeOutlet = fn($q) => $q->where('wilayah_id', $wilayahId);

        // B-K4: agregasi di DB (SUM/COUNT), bukan memuat koleksi penuh lalu sum di PHP.
        // Helper: agregat omset/modal/komisi dari laporan_harian_details pada rentang tanggal.
        $aggDetail = function ($from, $to) use ($wilayahId) {
            return \Illuminate\Support\Facades\DB::table('laporan_harian_details as lhd')
                ->join('laporan_harian as lh', 'lh.id', '=', 'lhd.laporan_id')
                ->join('outlet as o', 'o.id', '=', 'lh.outlet_id')
                ->whereNull('lh.deleted_at')
                ->whereBetween('lh.tanggal', [$from, $to])
                ->when($wilayahId, fn($q) => $q->where('o.wilayah_id', $wilayahId))
                ->selectRaw('COALESCE(SUM(lhd.omset),0) o, COALESCE(SUM(lhd.modal),0) m, COALESCE(SUM(lhd.komisi),0) k')
                ->first();
        };

        // ── Hari ini ──
        $hi = $aggDetail($today, $today);
        $omsetHariIni  = (float) $hi->o;
        $modalHariIni  = (float) $hi->m;
        $komisiHariIni = (float) $hi->k;
        $labaHariIni   = $omsetHariIni - $modalHariIni - $komisiHariIni;

        $lapHariIni = LaporanHarian::where('tanggal', $today)
            ->when($wilayahId, fn($q) => $q->whereHas('outlet', $scopeOutlet));
        $setorHariIni     = (float) (clone $lapHariIni)->sum('total_setor');
        $outletSudahLapor = (clone $lapHariIni)->count();

        // ── Bulan ini ──
        $bi = $aggDetail($awalBulan, $akhirBulan);
        $omsetBulanIni = (float) $bi->o;
        $labaBulanIni  = (float) ($bi->o - $bi->m - $bi->k);

        // ── Distribusi OUT hari ini ──
        $totalOutHariIni = (float) \Illuminate\Support\Facades\DB::table('distribusi_details as dd')
            ->join('distribusi as d', 'd.id', '=', 'dd.distribusi_id')
            ->join('outlet as o', 'o.id', '=', 'd.outlet_id')
            ->whereNull('d.deleted_at')
            ->where('d.tanggal', $today)
            ->when($wilayahId, fn($q) => $q->where('o.wilayah_id', $wilayahId))
            ->sum('dd.jumlah_out');

        // ── Outlet aktif (punya distribusi 30 hari terakhir) — sudah count query ──
        $batasOutletAktif = Carbon::now()->subDays(30)->format('Y-m-d');
        $totalOutlet = Outlet::where('aktif', true)
            ->when($wilayahId, fn($q) => $q->where('wilayah_id', $wilayahId))
            ->whereHas('distribusi', function ($q) use ($batasOutletAktif) {
                $q->where('tanggal', '>=', $batasOutletAktif);
            })
            ->count();

        // Laporan harian terbaru (list 10 — tetap eager-load utk view)
        $laporanTerbaru = LaporanHarian::with(['outlet', 'details'])
            ->when($wilayahId, fn($q) => $q->whereHas('outlet', $scopeOutlet))
            ->orderByDesc('tanggal')
            ->limit(10)
            ->get();

        // Pindah stok menunggu persetujuan
        $pindahStokQuery = PenjualanWilayah::where('tipe', 'transfer')->where('status', 'menunggu');
        if ($user->hasRole('koordinator')) {
            $pindahStokQuery->where('wilayah_tujuan_id', $user->wilayah_id);
        }
        $pindahStokMenunggu = $pindahStokQuery->count();

        // A-S5: tren memakai OMSET (details.omset), KONSISTEN dgn kartu omset.
        // B-K4: di-groupBy DI DB (bukan koleksi penuh di PHP).
        $tren7Hari = \Illuminate\Support\Facades\DB::table('laporan_harian_details as lhd')
            ->join('laporan_harian as lh', 'lh.id', '=', 'lhd.laporan_id')
            ->join('outlet as o', 'o.id', '=', 'lh.outlet_id')
            ->whereNull('lh.deleted_at')
            ->whereBetween('lh.tanggal', [$awalBulan, $akhirBulan])
            ->when($wilayahId, fn($q) => $q->where('o.wilayah_id', $wilayahId))
            ->groupBy('lh.tanggal')
            ->orderBy('lh.tanggal')
            ->selectRaw('lh.tanggal as tanggal, COALESCE(SUM(lhd.omset),0) as omset')
            ->get()
            ->map(fn($r) => (object) ['tanggal' => $r->tanggal, 'omset' => (float) $r->omset]);

        return view('dashboard', compact(
            'omsetHariIni',
            'modalHariIni',
            'komisiHariIni',
            'labaHariIni',
            'setorHariIni',
            'omsetBulanIni',
            'labaBulanIni',
            'totalOutHariIni',
            'totalOutlet',
            'outletSudahLapor',
            'laporanTerbaru',
            'pindahStokMenunggu',
            'tren7Hari'
        ));
    }
}