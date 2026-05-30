<?php
namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\Wilayah;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Laporan\OmsetExport;


class OmsetController extends Controller
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

        $query = LaporanHarian::with(['outlet.wilayah', 'details.produk'])
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln);

        if ($wilayahId !== 'semua') {
            $query->whereHas('outlet', fn($q) => $q->where('wilayah_id', $wilayahId));
        }

        $laporan = $query->orderBy('tanggal')->get();

        $rekapOutlet = $laporan->groupBy('outlet_id')->map(function ($rows) {
            $outlet = $rows->first()->outlet;
            $omset = $rows->sum(fn($l) => $l->details->sum('omset'));
            $modal = $rows->sum(fn($l) => $l->details->sum('modal'));
            $komisi = $rows->sum(fn($l) => $l->details->sum('komisi'));
            $terjual = $rows->sum(fn($l) => $l->details->sum('terjual'));
            $setor = $rows->sum('total_setor');
            return [
                'outlet' => $outlet,
                'omset' => $omset,
                'modal' => $modal,
                'komisi' => $komisi,
                'laba' => $omset - $modal - $komisi,
                'terjual' => $terjual,
                'setor' => $setor,
                'hari' => $rows->count(),
            ];
        })->sortByDesc('omset');

        $rekapHarian = $laporan->groupBy('tanggal')->map(function ($rows, $tanggal) {
            return [
                'tanggal' => $tanggal,
                'omset' => $rows->sum(fn($l) => $l->details->sum('omset')),
                'laba' => $rows->sum(
                    fn($l) =>
                    $l->details->sum('omset') - $l->details->sum('modal') - $l->details->sum('komisi')
                ),
            ];
        })->sortBy('tanggal');

        $totalOmset = $rekapOutlet->sum('omset');
        $totalModal = $rekapOutlet->sum('modal');
        $totalKomisi = $rekapOutlet->sum('komisi');
        $totalLaba = $rekapOutlet->sum('laba');
        $totalSetor = $rekapOutlet->sum('setor');

        // Rekap per produk terlaris
        $produkTerlaris = \App\Models\LaporanHarianDetail::with('produk')
            ->whereHas('laporan', function ($q) use ($tahun, $bln, $wilayahId) {
                $q->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bln);
                if ($wilayahId !== 'semua') {
                    $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $wilayahId));
                }
            })
            ->selectRaw('produk_id, SUM(terjual) as total_terjual, SUM(omset) as total_omset')
            ->groupBy('produk_id')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        return view('laporan.omset', compact(
            'bulan',
            'wilayahId',
            'wilayahList',
            'rekapOutlet',
            'rekapHarian',
            'totalOmset',
            'totalModal',
            'totalKomisi',
            'totalLaba',
            'totalSetor',
            'produkTerlaris'
        ));
    }


    public function export(Request $request)
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $wilayahId = $request->input('wilayah_id', 'semua');
        if (auth()->user()->hasRole('koordinator')) {
            $wilayahId = auth()->user()->wilayah_id;
        }
        $filename = 'rekap-omset-' . $bulan . '.xlsx';
        return Excel::download(new OmsetExport($bulan, $wilayahId), $filename);
    }
}