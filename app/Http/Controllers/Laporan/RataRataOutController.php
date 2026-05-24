<?php
namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Distribusi;
use App\Models\Outlet;
use App\Models\Produk;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Laporan\RataRataOutExport;

class RataRataOutController extends Controller
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

        $query = Distribusi::with(['outlet.wilayah', 'details.produk'])
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln);

        if ($wilayahId !== 'semua') {
            $query->whereHas('outlet', fn($q) => $q->where('wilayah_id', $wilayahId));
        }

        $distribusi = $query->get();
        $produkIds = $distribusi->flatMap(fn($d) => $d->details->pluck('produk_id'))->unique();
        $produkList = Produk::whereIn('id', $produkIds)->orderBy('nama')->get();
        $outletList = $distribusi->pluck('outlet')->unique('id')->sortBy('nama')->values();

        $matrix = [];
        foreach ($distribusi as $d) {
            foreach ($d->details as $detail) {
                if (!isset($matrix[$d->outlet_id][$detail->produk_id])) {
                    $matrix[$d->outlet_id][$detail->produk_id] = ['total' => 0, 'hari' => 0];
                }
                $matrix[$d->outlet_id][$detail->produk_id]['total'] += $detail->jumlah_out;
                $matrix[$d->outlet_id][$detail->produk_id]['hari']++;
            }
        }

        return view('laporan.rata-rata-out', compact(
            'bulan',
            'wilayahId',
            'wilayahList',
            'outletList',
            'produkList',
            'matrix'
        ));
    }

    public function export(Request $request)
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $wilayahId = $request->input('wilayah_id', 'semua');
        return Excel::download(new RataRataOutExport($bulan, $wilayahId), 'rata-rata-out-' . $bulan . '.xlsx');
    }
}