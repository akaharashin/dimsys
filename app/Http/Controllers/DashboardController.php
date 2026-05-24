<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LaporanHarian;
use App\Models\Distribusi;
use App\Models\StokMasuk;
use App\Models\Kas;
use App\Models\Outlet;
use App\Models\Wilayah;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        Carbon::setLocale('id');
        $today = Carbon::today()->toDateString();
        $thisMonth = Carbon::now()->format('Y-m');

        // Laporan hari ini
        $laporanHariIni = LaporanHarian::with('details')
            ->whereDate('tanggal', $today)
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
            ->get();

        $omsetBulanIni = $laporanBulanIni->sum(fn($l) => $l->details->sum('omset'));
        $labaBulanIni = $laporanBulanIni->sum(
            fn($l) =>
            $l->details->sum('omset') - $l->details->sum('modal') - $l->details->sum('komisi')
        );

        // Distribusi hari ini
        $distribusiHariIni = Distribusi::with('details')
            ->whereDate('tanggal', $today)
            ->get();
        $totalOutHariIni = $distribusiHariIni->sum(fn($d) => $d->details->sum('jumlah_out'));

        // Outlet aktif
        $totalOutlet = Outlet::where('aktif', true)->count();

        // Outlet yang sudah lapor hari ini
        $outletSudahLapor = $laporanHariIni->count();

        // Laporan harian terbaru
        $laporanTerbaru = LaporanHarian::with(['outlet', 'details'])
            ->orderByDesc('tanggal')
            ->limit(10)
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
            'laporanTerbaru'
        ));
    }
}