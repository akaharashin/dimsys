<?php
namespace App\Exports\Laporan\Sheets;

use App\Models\Distribusi;
use App\Models\Outlet;
use App\Models\Produk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RataRataOutSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $bulan;
    protected $wilayahId;

    public function __construct($bulan, $wilayahId)
    {
        $this->bulan     = $bulan;
        $this->wilayahId = $wilayahId;
    }

    public function collection()
    {
        [$tahun, $bln] = explode('-', $this->bulan);

        $distribusi = Distribusi::with(['outlet.wilayah', 'details.produk'])
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->when($this->wilayahId, fn($q) =>
                $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $this->wilayahId))
            )
            ->get();

        $produkIds  = $distribusi->flatMap(fn($d) => $d->details->pluck('produk_id'))->unique();
        $produkList = Produk::whereIn('id', $produkIds)->orderBy('nama')->get();
        $outletList = $distribusi->pluck('outlet')->unique('id')->sortBy('nama')->values();

        // Build matrix
        $matrix = [];
        foreach ($distribusi as $d) {
            foreach ($d->details as $detail) {
                if (!isset($matrix[$d->outlet_id][$detail->produk_id])) {
                    $matrix[$d->outlet_id][$detail->produk_id] = ['total' => 0, 'hari' => 0];
                }
                $matrix[$d->outlet_id][$detail->produk_id]['total'] += $detail->jumlah_out;
                $matrix[$d->outlet_id][$detail->produk_id]['hari']++;
            }
        }

        $rows = collect();
        $no   = 1;

        foreach ($outletList as $outlet) {
            $outletData  = $matrix[$outlet->id] ?? [];
            $totalOutlet = collect($outletData)->sum('total');

            $row = [$no++, $outlet->nama, $outlet->wilayah->nama];

            foreach ($produkList as $p) {
                $data     = $outletData[$p->id] ?? null;
                $total    = $data['total'] ?? 0;
                $hari     = $data['hari'] ?? 0;
                $rataRata = $hari > 0 ? round($total / $hari) : 0;
                $row[]    = $rataRata > 0 ? $rataRata : 0;
            }

            $row[] = $totalOutlet;
            $rows->push($row);
        }

        return $rows;
    }

    public function headings(): array
    {
        [$tahun, $bln] = explode('-', $this->bulan);

        $distribusi = Distribusi::with('details.produk')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->when($this->wilayahId, fn($q) =>
                $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $this->wilayahId))
            )
            ->get();

        $produkIds  = $distribusi->flatMap(fn($d) => $d->details->pluck('produk_id'))->unique();
        $produkList = Produk::whereIn('id', $produkIds)->orderBy('nama')->pluck('nama')->toArray();

        return array_merge(['No', 'Outlet', 'Wilayah'], $produkList, ['Total']);
    }

    public function columnFormats(): array
    {
        [$tahun, $bln] = explode('-', $this->bulan);

        $count = Distribusi::with('details')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->when($this->wilayahId, fn($q) =>
                $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $this->wilayahId))
            )
            ->get()
            ->flatMap(fn($d) => $d->details->pluck('produk_id'))
            ->unique()
            ->count();

        $formats = [];
        $col = 'D';
        for ($i = 0; $i <= $count; $i++) {
            $formats[$col] = '#,##0';
            $col++;
        }

        return $formats;
    }

    public function title(): string { return 'RATA-RATA OUT'; }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E8F5E9']]]];
    }
}