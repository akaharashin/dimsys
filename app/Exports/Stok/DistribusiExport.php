<?php
namespace App\Exports\Stok;

use App\Models\Distribusi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DistribusiExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Distribusi::with(['outlet.wilayah', 'details.produk'])
            ->orderByDesc('tanggal');

        if (!empty($this->filters['wilayah_id'])) {
            $query->whereHas(
                'outlet',
                fn($q) =>
                $q->where('wilayah_id', $this->filters['wilayah_id'])
            );
        }
        if (!empty($this->filters['outlet_id'])) {
            $query->where('outlet_id', $this->filters['outlet_id']);
        }
        if (!empty($this->filters['dari'])) {
            $query->whereDate('tanggal', '>=', $this->filters['dari']);
        }
        if (!empty($this->filters['sampai'])) {
            $query->whereDate('tanggal', '<=', $this->filters['sampai']);
        }

        $rows = collect();
        foreach ($query->get() as $d) {
            foreach ($d->details as $detail) {
                $rows->push([
                    \Carbon\Carbon::parse($d->tanggal)->format('d/m/Y'),
                    $d->outlet->nama,
                    $d->outlet->wilayah->nama,
                    $detail->produk->nama,
                    $detail->jumlah_out,
                    $d->keterangan ?? '-',
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['Tanggal', 'Outlet', 'Wilayah', 'Produk', 'Jumlah OUT (pcs)', 'Keterangan'];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '#,##0',
        ];
    }

    public function title(): string
    {
        return 'Distribusi';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF3E0']],
            ],
        ];
    }
}