<?php
namespace App\Exports\Laporan\Sheets;

use App\Models\Produk;
use App\Models\StokMasukDetail;
use App\Models\DistribusiDetail;
use App\Models\PenjualanWilayahDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapStokSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $bulan;
    protected $wilayahId;

    public function __construct($bulan, $wilayahId)
    {
        $this->bulan = $bulan;
        $this->wilayahId = $wilayahId;
    }

    public function collection()
    {
        [$tahun, $bln] = explode('-', $this->bulan);
        $produkList = Produk::where('aktif', true)->orderBy('nama')->get();

        return $produkList->map(function ($produk) use ($tahun, $bln) {
            $stokAwal = StokMasukDetail::whereHas(
                'stokMasuk',
                fn($q) =>
                $q->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bln)
                    ->where('jenis', 'awal')
                    ->when($this->wilayahId, fn($q) => $q->where('wilayah_id', $this->wilayahId))
            )->where('produk_id', $produk->id)->sum('jumlah');

            $masuk = StokMasukDetail::whereHas(
                'stokMasuk',
                fn($q) =>
                $q->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bln)
                    ->where('jenis', 'masuk')
                    ->when($this->wilayahId, fn($q) => $q->where('wilayah_id', $this->wilayahId))
            )->where('produk_id', $produk->id)->sum('jumlah');

            $out = DistribusiDetail::whereHas(
                'distribusi',
                fn($q) =>
                $q->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bln)
                    ->when(
                        $this->wilayahId,
                        fn($q) =>
                        $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $this->wilayahId))
                    )
            )->where('produk_id', $produk->id)->sum('jumlah_out');

            $keluarWilayah = PenjualanWilayahDetail::whereHas(
                'penjualan',
                fn($q) =>
                $q->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bln)
                    ->when($this->wilayahId, fn($q) => $q->where('wilayah_asal_id', $this->wilayahId))
            )->where('produk_id', $produk->id)->sum('jumlah');

            $stokAkhir = ($stokAwal + $masuk) - $out - $keluarWilayah;

            return [
                $produk->nama,
                $stokAwal,
                $masuk,
                $out,
                $keluarWilayah,
                $stokAkhir,
                $produk->hpp,
                max(0, $stokAkhir) * $produk->hpp,
            ];
        })->filter(fn($r) => $r[1] > 0 || $r[2] > 0);
    }

    public function headings(): array
    {
        return ['Produk', 'Stok Awal', 'Masuk', 'OUT Gerobak', 'Keluar Wilayah', 'Stok Akhir', 'HPP', 'Nilai Stok'];
    }

    public function title(): string
    {
        return 'REKAP STOK';
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E3F2FD']]]];
    }
}