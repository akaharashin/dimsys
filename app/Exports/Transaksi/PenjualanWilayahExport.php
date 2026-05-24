<?php
namespace App\Exports\Transaksi;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenjualanWilayahExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $no = 1;
        return $this->data->map(fn($p) => [
            $no++,
            \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y'),
            $p->wilayahAsal->nama,
            $p->wilayahTujuan->nama,
            $p->total,
            ucfirst(str_replace('_', ' ', $p->status_bayar)),
            $p->keterangan ?? '-',
        ]);
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Dari', 'Ke', 'Total', 'Status Bayar', 'Keterangan'];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '#,##0',
        ];
    }

    public function title(): string
    {
        return 'Penjualan Wilayah';
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF3E0']]]];
    }
}