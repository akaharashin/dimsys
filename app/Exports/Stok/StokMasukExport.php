<?php
namespace App\Exports\Stok;

use App\Models\StokMasuk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StokMasukExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = StokMasuk::with(['wilayah', 'supplier', 'details.produk'])
            ->orderByDesc('tanggal');

        if (!empty($this->filters['wilayah_id'])) {
            $query->where('wilayah_id', $this->filters['wilayah_id']);
        }
        if (!empty($this->filters['supplier_id'])) {
            $query->where('supplier_id', $this->filters['supplier_id']);
        }
        if (!empty($this->filters['jenis'])) {
            $query->where('jenis', $this->filters['jenis']);
        }
        if (!empty($this->filters['dari'])) {
            $query->whereDate('tanggal', '>=', $this->filters['dari']);
        }
        if (!empty($this->filters['sampai'])) {
            $query->whereDate('tanggal', '<=', $this->filters['sampai']);
        }

        $rows = collect();
        foreach ($query->get() as $sm) {
            foreach ($sm->details as $d) {
                $rows->push([
                    \Carbon\Carbon::parse($sm->tanggal)->format('d/m/Y'),
                    $sm->jenis === 'awal' ? 'Stok Awal' : 'Stok Masuk',
                    $sm->wilayah->nama,
                    $sm->supplier->nama,
                    $d->produk->nama,
                    $d->jumlah,
                    $d->hpp,
                    $d->jumlah * $d->hpp,
                    $sm->keterangan ?? '-',
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Jenis',
            'Wilayah',
            'Supplier',
            'Produk',
            'Jumlah (pcs)',
            'HPP/pcs',
            'Total HPP',
            'Keterangan',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
        ];
    }

    public function title(): string
    {
        return 'Stok Masuk';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F5E9']],
            ],
        ];
    }
}