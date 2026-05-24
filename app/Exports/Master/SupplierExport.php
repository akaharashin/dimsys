<?php
namespace App\Exports\Master;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupplierExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Supplier::orderBy('nama')->get()->map(fn($s) => [
            $s->nama,
            $s->keterangan ?? '-',
            $s->aktif ? 'Aktif' : 'Nonaktif',
        ]);
    }

    public function headings(): array
    {
        return ['Nama', 'Keterangan', 'Status'];
    }
    public function title(): string
    {
        return 'Master Supplier';
    }
    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EDE7F6']]]];
    }
}