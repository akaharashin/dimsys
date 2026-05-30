<?php
namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\LaporanHarian;
use App\Models\Outlet;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Laporan\KontrolExport;

class KontrolController extends Controller
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

        $query = LaporanHarian::with(['outlet.wilayah', 'details'])
            ->whereBetween('tanggal', [$awalBulan, $akhirBulan]);

        if ($wilayahId !== 'semua') {
            $query->whereHas('outlet', fn($q) => $q->where('wilayah_id', $wilayahId));
        }

        $laporan = $query->orderBy('tanggal')->get();

        $tanggalList = $laporan->pluck('tanggal')->unique()->sort()->values();
        $outletList = $laporan->pluck('outlet')->unique('id')->sortBy('nama')->values();

        $matrix = [];
        foreach ($laporan as $l) {
            $matrix[$l->outlet_id][$l->tanggal] = [
                'omset' => $l->details->sum('omset'),
                'terjual' => $l->details->sum('terjual'),
                'laba' => $l->details->sum('omset') - $l->details->sum('modal') - $l->details->sum('komisi'),
                'setor' => $l->total_setor,
            ];
        }

        $rekap = $outletList->map(function ($outlet) use ($matrix) {
            $data = $matrix[$outlet->id] ?? [];
            return [
                'outlet' => $outlet,
                'total_hari' => count($data),
                'total_terjual' => collect($data)->sum('terjual'),
                'total_omset' => collect($data)->sum('omset'),
                'total_laba' => collect($data)->sum('laba'),
                'total_setor' => collect($data)->sum('setor'),
            ];
        });

        return view('laporan.kontrol', compact(
            'bulan',
            'wilayahId',
            'wilayahList',
            'tanggalList',
            'outletList',
            'matrix',
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
        return Excel::download(new KontrolExport($bulan, $wilayahId), 'kontrol-penjualan-' . $bulan . '.xlsx');
    }
}