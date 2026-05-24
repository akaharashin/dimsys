<?php
namespace App\Exports\Laporan\Sheets;

use App\Models\Distribusi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DistribusiSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize
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

        $query = Distribusi::with(['outlet.wilayah', 'details.produk'])
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
        foreach ($query->get() as $d) {
            foreach ($d->details as $detail) {
                $rows->push([
                    $no++,
                    \Carbon\Carbon::parse($d->tanggal)->format('d/m/Y'),
                    $d->outlet->nama,
                    $d->outlet->wilayah->nama,
                    $detail->produk->nama,
                    $detail->jumlah_out,
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Outlet', 'Wilayah', 'Produk', 'Jumlah OUT'];
    }

    public function title(): string
    {
        return 'DISTRIBUSI';
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF3E0']]]];
    }
}