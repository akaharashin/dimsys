<?php
namespace App\Exports\Laporan;

use App\Models\LaporanHarian;
use App\Models\Outlet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KontrolExport implements FromArray, WithHeadings, WithTitle, WithStyles
{
    protected $bulan;
    protected $wilayahId;

    public function __construct($bulan, $wilayahId = 'semua')
    {
        $this->bulan     = $bulan;
        $this->wilayahId = $wilayahId;
    }

    public function array(): array
    {
        [$tahun, $bln] = explode('-', $this->bulan);

        $query = LaporanHarian::with(['outlet.wilayah', 'details'])
            ->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bln);

        if ($this->wilayahId !== 'semua') {
            $query->whereHas('outlet', fn($q) => $q->where('wilayah_id', $this->wilayahId));
        }

        $laporan    = $query->orderBy('tanggal')->get();
        $tanggalList = $laporan->pluck('tanggal')->unique()->sort()->values();
        $outletList  = $laporan->pluck('outlet')->unique('id')->sortBy('nama')->values();

        $matrix = [];
        foreach ($laporan as $l) {
            $matrix[$l->outlet_id][$l->tanggal] = [
                'omset' => $l->details->sum('omset'),
                'setor' => $l->total_setor,
            ];
        }

        $rows = [];

        // Baris omset
        foreach ($outletList as $outlet) {
            $row = [$outlet->nama, $outlet->wilayah->nama, 'Omset'];
            $total = 0;
            foreach ($tanggalList as $tgl) {
                $val   = $matrix[$outlet->id][$tgl]['omset'] ?? 0;
                $total += $val;
                $row[] = $val ?: '';
            }
            $row[]  = $total;
            $rows[] = $row;
        }

        // Baris setor
        foreach ($outletList as $outlet) {
            $row = [$outlet->nama, $outlet->wilayah->nama, 'Setor'];
            $total = 0;
            foreach ($tanggalList as $tgl) {
                $val   = $matrix[$outlet->id][$tgl]['setor'] ?? 0;
                $total += $val;
                $row[] = $val ?: '';
            }
            $row[]  = $total;
            $rows[] = $row;
        }

        return $rows;
    }

    public function headings(): array
    {
        [$tahun, $bln] = explode('-', $this->bulan);

        $tanggalList = LaporanHarian::whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->pluck('tanggal')->unique()->sort()->values()
            ->map(fn($t) => \Carbon\Carbon::parse($t)->format('d'))->toArray();

        return array_merge(['Outlet', 'Wilayah', 'Tipe'], $tanggalList, ['Total']);
    }

    public function title(): string { return 'Kontrol Penjualan'; }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E3F2FD']]],
        ];
    }
}