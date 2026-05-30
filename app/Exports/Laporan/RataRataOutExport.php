<?php
namespace App\Exports\Laporan;

use App\Models\Distribusi;
use App\Models\Produk;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RataRataOutExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnFormatting
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
        [$awalBulan, $akhirBulan] = \App\Support\Periode::range($this->bulan);

        $query = Distribusi::with(['outlet.wilayah', 'details.produk'])
            ->whereBetween('tanggal', [$awalBulan, $akhirBulan]);

        if ($this->wilayahId !== 'semua') {
            $query->whereHas('outlet', fn($q) => $q->where('wilayah_id', $this->wilayahId));
        }

        $distribusi = $query->get();
        $produkIds  = $distribusi->flatMap(fn($d) => $d->details->pluck('produk_id'))->unique();
        $produkList = Produk::whereIn('id', $produkIds)->orderBy('nama')->get();
        $outletList = $distribusi->pluck('outlet')->unique('id')->sortBy('nama')->values();

        // A-R2: 'hari' = jumlah HARI UNIK (distinct tanggal), bukan jumlah record.
        // Identik dengan RataRataOutController agar angka export = angka tampilan.
        $matrix   = [];
        $hariUnik = [];
        foreach ($distribusi as $d) {
            $tgl = \Carbon\Carbon::parse($d->tanggal)->toDateString();
            foreach ($d->details as $detail) {
                if (!isset($matrix[$d->outlet_id][$detail->produk_id])) {
                    $matrix[$d->outlet_id][$detail->produk_id] = ['total' => 0, 'hari' => 0];
                    $hariUnik[$d->outlet_id][$detail->produk_id] = [];
                }
                $matrix[$d->outlet_id][$detail->produk_id]['total'] += $detail->jumlah_out;
                $hariUnik[$d->outlet_id][$detail->produk_id][$tgl] = true;
            }
        }
        foreach ($hariUnik as $outletId => $produkDates) {
            foreach ($produkDates as $produkId => $dates) {
                $matrix[$outletId][$produkId]['hari'] = count($dates);
            }
        }

        $rows = [];
        foreach ($outletList as $outlet) {
            $row = [$outlet->nama, $outlet->wilayah->nama];
            foreach ($produkList as $p) {
                $data  = $matrix[$outlet->id][$p->id] ?? null;
                // Pembulatan ke bilangan bulat, identik dengan tampilan RataRataOut.
                $row[] = $data && $data['hari'] > 0 ? round($data['total'] / $data['hari']) : 0;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function headings(): array
    {
        [$awalBulan, $akhirBulan] = \App\Support\Periode::range($this->bulan);
        $produkIds  = Distribusi::whereBetween('tanggal', [$awalBulan, $akhirBulan])
            ->with('details')->get()->flatMap(fn($d) => $d->details->pluck('produk_id'))->unique();
        $produkList = Produk::whereIn('id', $produkIds)->orderBy('nama')->pluck('nama')->toArray();
        return array_merge(['Outlet', 'Wilayah'], $produkList);
    }

    public function columnFormats(): array
    {
        [$awalBulan, $akhirBulan] = \App\Support\Periode::range($this->bulan);
        $query = Distribusi::with('details')
            ->whereBetween('tanggal', [$awalBulan, $akhirBulan]);
        if ($this->wilayahId !== 'semua') {
            $query->whereHas('outlet', fn($q) => $q->where('wilayah_id', $this->wilayahId));
        }
        $count = $query->get()->flatMap(fn($d) => $d->details->pluck('produk_id'))->unique()->count();

        $formats = [];
        $col = 'C';
        for ($i = 0; $i < $count; $i++) {
            $formats[$col] = '#,##0';
            $col++;
        }
        return $formats;
    }

    public function title(): string { return 'Rata-rata OUT'; }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EDE7F6']]],
        ];
    }
}