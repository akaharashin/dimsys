<?php
namespace App\Exports\Laporan\Sheets;

use App\Models\StokMasuk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StokMasukSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $bulan;
    protected $wilayahId;

    public function __construct($bulan, $wilayahId)
    {
        $this->bulan = $bulan;
        $this->wilayahId = $wilayahId;
    }

    public function collection()
    {
        [$tahun, $bln] = explode('-', $this->bulan);

        $query = StokMasuk::with(['wilayah', 'supplier', 'details.produk'])
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->when($this->wilayahId, fn($q) => $q->where('wilayah_id', $this->wilayahId))
            ->orderBy('tanggal');

        $rows = collect();
        $no = 1;
        foreach ($query->get() as $sm) {
            foreach ($sm->details as $d) {
                $rows->push([
                    $no++,
                    \Carbon\Carbon::parse($sm->tanggal)->format('d/m/Y'),
                    $sm->jenis === 'awal' ? 'Stok Awal' : 'Stok Masuk',
                    $sm->supplier->nama,
                    $d->produk->nama,
                    $d->jumlah,
                    $d->hpp,
                    $d->jumlah * $d->hpp,
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Jenis', 'Supplier', 'Produk', 'Jumlah', 'HPP', 'Total HPP'];
    }

    public function title(): string
    {
        return 'STOK MASUK';
    }

    public function columnFormats(): array
    {
        return [
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F5E9']]]];
    }
}