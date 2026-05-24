<?php
namespace App\Exports\Transaksi;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanHarianExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $no = 1;
        return $this->data->map(fn($l) => [
            $no++,
            \Carbon\Carbon::parse($l->tanggal)->format('d/m/Y'),
            $l->outlet->nama,
            $l->outlet->wilayah->nama,
            $l->details->sum('terjual'),
            $l->details->sum('omset'),
            $l->details->sum('modal'),
            $l->details->sum('komisi'),
            $l->details->sum('omset') - $l->details->sum('modal') - $l->details->sum('komisi'),
            $l->total_setor,
            $l->total_pengeluaran,
            ucfirst($l->status),
        ]);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Outlet',
            'Wilayah',
            'Terjual (pcs)',
            'Omset',
            'Modal',
            'Komisi',
            'Laba',
            'Total Setor',
            'Total Pengeluaran',
            'Status'
        ];
    }

    public function title(): string
    {
        return 'Laporan Harian';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F5E9']]],
        ];
    }
}