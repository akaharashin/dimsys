<?php
namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Laporan\RekapBulananExport;

class ExportBulananController extends Controller
{
    public function export(Request $request)
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $wilayahId = $request->input('wilayah_id');

        // Kalau "semua" atau kosong, jadikan null
        if (!$wilayahId || $wilayahId === 'semua') {
            $wilayahId = null;
        }

        if (auth()->user()->hasRole('koordinator')) {
            $wilayahId = auth()->user()->wilayah_id;
        }

        $wilayah = $wilayahId ? \App\Models\Wilayah::find($wilayahId) : null;
        $namaBulan = \Carbon\Carbon::parse($bulan . '-01')->locale('id')->isoFormat('MMMM_YYYY');
        $namaWilayah = $wilayah ? $wilayah->nama : 'Semua';

        $filename = "DIMSYS_{$namaWilayah}_{$namaBulan}.xlsx";

        return Excel::download(
            new RekapBulananExport($bulan, $wilayahId),
            $filename
        );
    }
}