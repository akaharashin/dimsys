<?php
namespace App\Exports\Laporan\Sheets;

use App\Models\LaporanHarian;
use App\Models\Outlet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OmsetSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting
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
        [$awalBulan, $akhirBulan] = \App\Support\Periode::range($this->bulan);

        $outletQuery = Outlet::where('aktif', true)
            ->when($this->wilayahId, fn($q) => $q->where('wilayah_id', $this->wilayahId))
            ->orderBy('nama');

        $rows = collect();
        $no = 1;

        foreach ($outletQuery->get() as $outlet) {
            $laporan = LaporanHarian::with('details')
                ->where('outlet_id', $outlet->id)
                ->whereBetween('tanggal', [$awalBulan, $akhirBulan])
                ->get();

            if ($laporan->isEmpty())
                continue;

            $totalOmset = $laporan->sum(fn($l) => $l->details->sum('omset'));
            $totalModal = $laporan->sum(fn($l) => $l->details->sum('modal'));
            $totalKomisi = $laporan->sum(fn($l) => $l->details->sum('komisi'));
            $totalLaba = $totalOmset - $totalModal - $totalKomisi;
            $totalSetor = $laporan->sum('total_setor');
            $totalHari = $laporan->count();
            $totalTerjual = $laporan->sum(fn($l) => $l->details->sum('terjual'));

            $rows->push([
                $no++,
                $outlet->nama,
                $outlet->wilayah->nama,
                $totalHari,
                $totalTerjual,
                $totalOmset,
                $totalModal,
                $totalKomisi,
                $totalLaba,
                $totalSetor,
                $totalHari > 0 ? round($totalOmset / $totalHari) : 0,
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'Outlet', 'Wilayah', 'Hari', 'Terjual', 'Omset', 'Modal', 'Komisi', 'Laba', 'Setor', 'Rata-rata/Hari'];
    }

    public function title(): string
    {
        return 'REKAP OMSET';
    }

    public function columnFormats(): array
    {
        return [
            'E' => '#,##0',
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
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF9C4']]]];
    }
}