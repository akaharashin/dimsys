<?php
namespace App\Exports\Stok;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StokOpnameExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $rows = collect();
        $no = 1;
        foreach ($this->data as $so) {
            foreach ($so->details as $d) {
                $rows->push([
                    $no++,
                    \Carbon\Carbon::parse($so->tanggal)->format('d/m/Y'),
                    $so->wilayah->nama,
                    $d->produk->nama,
                    $d->stok_sistem,
                    $d->stok_fisik,
                    $d->selisih,
                    $d->hpp_snapshot,
                    $d->nilai_selisih,
                    ucfirst($so->status),
                    $so->keterangan ?? '-',
                ]);
            }
        }
        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Wilayah',
            'Produk',
            'Stok Sistem',
            'Stok Fisik',
            'Selisih',
            'HPP',
            'Nilai Selisih',
            'Status',
            'Keterangan'
        ];
    }

    public function title(): string
    {
        return 'Stok Opname';
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF3E0']]]];
    }
}