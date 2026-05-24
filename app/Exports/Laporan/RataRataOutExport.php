<?php
namespace App\Exports\Laporan;

use App\Models\Distribusi;
use App\Models\Produk;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RataRataOutExport implements FromArray, WithHeadings, WithTitle, WithStyles
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

        $query = Distribusi::with(['outlet.wilayah', 'details.produk'])
            ->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bln);

        if ($this->wilayahId !== 'semua') {
            $query->whereHas('outlet', fn($q) => $q->where('wilayah_id', $this->wilayahId));
        }

        $distribusi = $query->get();
        $produkIds  = $distribusi->flatMap(fn($d) => $d->details->pluck('produk_id'))->unique();
        $produkList = Produk::whereIn('id', $produkIds)->orderBy('nama')->get();
        $outletList = $distribusi->pluck('outlet')->unique('id')->sortBy('nama')->values();

        $matrix = [];
        foreach ($distribusi as $d) {
            foreach ($d->details as $detail) {
                $matrix[$d->outlet_id][$detail->produk_id]['total'] = ($matrix[$d->outlet_id][$detail->produk_id]['total'] ?? 0) + $detail->jumlah_out;
                $matrix[$d->outlet_id][$detail->produk_id]['hari']  = ($matrix[$d->outlet_id][$detail->produk_id]['hari'] ?? 0) + 1;
            }
        }

        $rows = [];
        foreach ($outletList as $outlet) {
            $row = [$outlet->nama, $outlet->wilayah->nama];
            foreach ($produkList as $p) {
                $data  = $matrix[$outlet->id][$p->id] ?? null;
                $row[] = $data ? round($data['total'] / $data['hari'], 1) : 0;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function headings(): array
    {
        [$tahun, $bln] = explode('-', $this->bulan);
        $produkIds  = Distribusi::whereYear('tanggal', $tahun)->whereMonth('tanggal', $bln)
            ->with('details')->get()->flatMap(fn($d) => $d->details->pluck('produk_id'))->unique();
        $produkList = Produk::whereIn('id', $produkIds)->orderBy('nama')->pluck('nama')->toArray();
        return array_merge(['Outlet', 'Wilayah'], $produkList);
    }

    public function title(): string { return 'Rata-rata OUT'; }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EDE7F6']]],
        ];
    }
}