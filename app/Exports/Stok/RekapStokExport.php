<?php
namespace App\Exports\Stok;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapStokExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $rekap;
    protected $wilayah;

    public function __construct($rekap, $wilayah)
    {
        $this->rekap   = $rekap;
        $this->wilayah = $wilayah;
    }

    public function collection()
    {
        return $this->rekap->map(fn($r) => [
            $r['produk']->nama,
            $r['masuk'],
            $r['out_gerobak'],
            $r['keluar_wilayah'],
            $r['stok_akhir'],
            $r['hpp_rata'],
            $r['nilai_stok'],
            ucfirst($r['status']),
        ]);
    }

    public function headings(): array
    {
        return [
            'Produk',
            'Total Masuk (pcs)',
            'OUT Gerobak (pcs)',
            'Keluar Wilayah (pcs)',
            'Stok Akhir (pcs)',
            'HPP Rata-rata',
            'Nilai Stok',
            'Status',
        ];
    }

    public function title(): string
    {
        return 'Rekap Stok ' . ($this->wilayah->nama ?? '');
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E3F2FD']],
            ],
        ];
    }
}