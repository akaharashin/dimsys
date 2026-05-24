<?php
namespace App\Exports\Master;

use App\Models\Wilayah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WilayahExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Wilayah::orderBy('nama')->get()->map(fn($w) => [
            $w->nama,
            ucfirst($w->tipe),
            $w->aktif ? 'Aktif' : 'Nonaktif',
        ]);
    }

    public function headings(): array
    {
        return ['Nama', 'Tipe', 'Status'];
    }
    public function title(): string
    {
        return 'Master Wilayah';
    }
    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E3F2FD']]]];
    }
}