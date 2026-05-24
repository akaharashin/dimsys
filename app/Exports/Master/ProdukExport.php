<?php
namespace App\Exports\Master;

use App\Models\Produk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProdukExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Produk::orderBy('nama')->get()->map(fn($p) => [
            $p->nama,
            $p->hpp,
            $p->harga_mitra,
            $p->harga_jual,
            $p->harga_umum,
            $p->harga_agen,
            $p->komisi,
            $p->aktif ? 'Aktif' : 'Nonaktif',
        ]);
    }

    public function headings(): array
    {
        return ['Nama', 'HPP', 'Harga Mitra', 'Harga Jual', 'Harga Umum', 'Harga Agen', 'Komisi', 'Status'];
    }

    public function title(): string
    {
        return 'Master Produk';
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF3E0']]]];
    }
}