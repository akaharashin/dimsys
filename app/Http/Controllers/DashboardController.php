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
        $thisMonth = Carbon::now()->format('Y-m');

        $user = auth()->user();
        $wilayahId = $user->hasRole('koordinator') ? $user->wilayah_id : null;
        $scopeOutlet = fn($q) => $q->where('wilayah_id', $wilayahId);

        // Laporan hari ini
        $laporanHariIni = LaporanHarian::with('details')
            ->whereDate('tanggal', $today)
            ->when($wilayahId, fn($q) => $q->whereHas('outlet', $scopeOutlet))
            ->get();

        $omsetHariIni = $laporanHariIni->sum(fn($l) => $l->details->sum('omset'));
        $modalHariIni = $laporanHariIni->sum(fn($l) => $l->details->sum('modal'));
        $komisiHariIni = $laporanHariIni->sum(fn($l) => $l->details->sum('komisi'));
        $labaHariIni = $omsetHariIni - $modalHariIni - $komisiHariIni;
        $setorHariIni = $laporanHariIni->sum('total_setor');

        // Laporan bulan ini
        $laporanBulanIni = LaporanHarian::with('details')
            ->whereYear('tanggal', Carbon::now()->year)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->when($wilayahId, fn($q) => $q->whereHas('outlet', $scopeOutlet))
            ->get();

        $omsetBulanIni = $laporanBulanIni->sum(fn($l) => $l->details->sum('omset'));
        $labaBulanIni = $laporanBulanIni->sum(
            fn($l) =>
            $l->details->sum('omset') - $l->details->sum('modal') - $l->details->sum('komisi')
        );

        // Distribusi hari ini
        $distribusiHariIni = Distribusi::with('details')
            ->whereDate('tanggal', $today)
            ->when($wilayahId, fn($q) => $q->whereHas('outlet', $scopeOutlet))
            ->get();
        $totalOutHariIni = $distribusiHariIni->sum(fn($d) => $d->details->sum('jumlah_out'));

        // Outlet aktif yang punya distribusi dalam 30 hari terakhir (lebih realistis)
        $batasOutletAktif = Carbon::now()->subDays(30)->format('Y-m-d');
        $totalOutlet = Outlet::where('aktif', true)
            ->when($wilayahId, fn($q) => $q->where('wilayah_id', $wilayahId))
            ->whereHas('distribusi', function ($q) use ($batasOutletAktif) {
                $q->where('tanggal', '>=', $batasOutletAktif);
            })
            ->count();

        // Outlet yang sudah lapor hari ini
        $outletSudahLapor = $laporanHariIni->count();

        // Laporan harian terbaru
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

        $tren7Hari = LaporanHarian::selectRaw('tanggal, SUM(total_setor) as omset')
            ->whereYear('tanggal', Carbon::now()->year)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->when($wilayahId, fn($q) => $q->whereHas('outlet', $scopeOutlet))
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

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