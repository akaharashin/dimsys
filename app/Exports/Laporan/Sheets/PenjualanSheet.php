<?php
namespace App\Exports\Laporan\Sheets;

use App\Models\LaporanHarian;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenjualanSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting
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

        $query = LaporanHarian::with(['outlet.wilayah', 'details.produk'])
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->when(
                $this->wilayahId,
                fn($q) =>
                $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $this->wilayahId))
            )
            ->orderBy('tanggal');

        $rows = collect();
        $no = 1;
        foreach ($query->get() as $l) {
            foreach ($l->details as $d) {
                $rows->push([
                    $no++,
                    \Carbon\Carbon::parse($l->tanggal)->format('d/m/Y'),
                    $l->outlet->nama,
                    $l->outlet->wilayah->nama,
                    $d->produk->nama,
                    $d->terjual,
                    $d->sisa,
                    $d->omset,
                    $d->modal,
                    $d->komisi,
                    $d->omset - $d->modal - $d->komisi,
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Outlet', 'Wilayah', 'Produk', 'Terjual', 'Sisa', 'Omset', 'Modal', 'Komisi', 'Laba'];
    }

    public function title(): string
    {
        return 'PENJUALAN';
    }

    public function columnFormats(): array
    {
        return [
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => '#,##0',
            'J' => '#,##0',
            'K' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F3E5F5']]]];
    }
}