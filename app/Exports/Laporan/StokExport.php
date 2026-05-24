<?php
namespace App\Exports\Laporan;

use App\Models\Produk;
use App\Models\StokMasukDetail;
use App\Models\DistribusiDetail;
use App\Models\PenjualanWilayahDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StokExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnFormatting
{
    protected $bulan;
    protected $wilayahId;

    public function __construct($bulan, $wilayahId = 'semua')
    {
        $this->bulan     = $bulan;
        $this->wilayahId = $wilayahId;
    }

    public function collection()
    {
        [$tahun, $bln] = explode('-', $this->bulan);

        $produkList = Produk::where('aktif', true)->orderBy('nama')->get();

        return $produkList->map(function ($produk) use ($tahun, $bln) {
            $masuk = StokMasukDetail::whereHas('stokMasuk', function ($q) use ($tahun, $bln) {
                $q->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bln);
            })->where('produk_id', $produk->id)->sum('jumlah');

            $out = DistribusiDetail::whereHas('distribusi', function ($q) use ($tahun, $bln) {
                $q->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bln);
            })->where('produk_id', $produk->id)->sum('jumlah_out');

            $keluarWilayah = PenjualanWilayahDetail::whereHas('penjualan', function ($q) use ($tahun, $bln) {
                $q->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bln);
            })->where('produk_id', $produk->id)->sum('jumlah');

            $totalKeluar = $out + $keluarWilayah;

            return [$produk->nama, $masuk, $out, $keluarWilayah, $totalKeluar, $masuk - $totalKeluar];
        })->filter(fn($r) => $r[1] > 0 || $r[4] > 0)->values();
    }

    public function headings(): array
    {
        return ['Produk', 'Stok Masuk', 'OUT Gerobak', 'Keluar Wilayah', 'Total Keluar', 'Sisa Estimasi'];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '#,##0',
            'C' => '#,##0',
            'D' => '#,##0',
            'E' => '#,##0',
            'F' => '#,##0',
        ];
    }

    public function title(): string { return 'Rekap Stok'; }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F5E9']]],
        ];
    }
}