<?php
namespace App\Exports\Master;

use App\Models\Outlet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OutletExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Outlet::with('wilayah')->orderBy('nama')->get()->map(fn($o) => [
            $o->nama,
            $o->wilayah->nama,
            ucfirst($o->tipe),
            $o->aktif ? 'Aktif' : 'Nonaktif',
        ]);
    }

    public function headings(): array
    {
        return ['Nama', 'Wilayah', 'Tipe', 'Status'];
    }
    public function title(): string
    {
        return 'Master Outlet';
    }
    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F5E9']]]];
    }
}