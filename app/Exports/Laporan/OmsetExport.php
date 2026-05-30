<?php
namespace App\Exports\Laporan;

use App\Models\LaporanHarian;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class OmsetExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnFormatting
{
    protected $bulan;
    protected $wilayahId;

    public function __construct($bulan, $wilayahId = 'semua')
    {
        $this->bulan     = $bulan;
        $this->wilayahId = $wilayahId;
    }

    public function collection()
    {
        [$awalBulan, $akhirBulan] = \App\Support\Periode::range($this->bulan);

        $query = LaporanHarian::with(['outlet.wilayah', 'details'])
            ->whereBetween('tanggal', [$awalBulan, $akhirBulan]);

        if ($this->wilayahId !== 'semua') {
            $query->whereHas('outlet', fn($q) => $q->where('wilayah_id', $this->wilayahId));
        }

        $laporan = $query->orderBy('tanggal')->get();

        $rekapOutlet = $laporan->groupBy('outlet_id')->map(function ($rows) {
            $outlet  = $rows->first()->outlet;
            $omset   = $rows->sum(fn($l) => $l->details->sum('omset'));
            $modal   = $rows->sum(fn($l) => $l->details->sum('modal'));
            $komisi  = $rows->sum(fn($l) => $l->details->sum('komisi'));
            $terjual = $rows->sum(fn($l) => $l->details->sum('terjual'));
            $setor   = $rows->sum('total_setor');
            return [
                $outlet->nama,
                $outlet->wilayah->nama,
                $rows->count(),
                $terjual,
                $omset,
                $modal,
                $komisi,
                $omset - $modal - $komisi,
                $setor,
            ];
        })->values();

        return $rekapOutlet;
    }

    public function headings(): array
    {
        return ['Outlet', 'Wilayah', 'Hari Jualan', 'Terjual (pcs)', 'Omset', 'Modal', 'Komisi', 'Laba', 'Setor'];
    }

    public function columnFormats(): array
    {
        return [
            'C' => '#,##0',
            'D' => '#,##0',
            'E' => '#,##0',
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => '#,##0',
        ];
    }

    public function title(): string
    {
        return 'Rekap Omset';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF3E0']]],
        ];
    }
}