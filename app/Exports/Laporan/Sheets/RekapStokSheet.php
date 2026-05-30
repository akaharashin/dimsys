<?php
namespace App\Exports\Laporan\Sheets;

use App\Models\Produk;
use App\Models\StokMasukDetail;
use App\Models\DistribusiDetail;
use App\Models\PenjualanWilayahDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class RekapStokSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting
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
        // B-K2: batch groupBy (bukan 4 query/produk). Formula DIPERTAHANKAN persis:
        // stokAkhir = (awal + masuk) - out - keluarWilayah  (TANPA koreksi),
        // keluarWilayah TANPA filter status. Wilayah difilter hanya bila di-set.
        [$awalBulan, $akhirBulan] = \App\Support\Periode::range($this->bulan);
        $produkList = Produk::where('aktif', true)->orderBy('nama')->get();
        $useWil = (bool) $this->wilayahId;

        $masukRows = \Illuminate\Support\Facades\DB::table('stok_masuk_details as smd')
            ->join('stok_masuk as sm', 'sm.id', '=', 'smd.stok_masuk_id')
            ->whereNull('sm.deleted_at')
            ->whereBetween('sm.tanggal', [$awalBulan, $akhirBulan])
            ->when($useWil, fn($q) => $q->where('sm.wilayah_id', $this->wilayahId))
            ->groupBy('smd.produk_id', 'sm.jenis')
            ->selectRaw('smd.produk_id as pid, sm.jenis as jenis, SUM(smd.jumlah) as total')
            ->get();
        $masukMap = [];
        foreach ($masukRows as $r) {
            $masukMap[$r->pid][$r->jenis] = (int) $r->total;
        }

        $outMap = \Illuminate\Support\Facades\DB::table('distribusi_details as dd')
            ->join('distribusi as d', 'd.id', '=', 'dd.distribusi_id')
            ->join('outlet as o', 'o.id', '=', 'd.outlet_id')
            ->whereNull('d.deleted_at')
            ->whereBetween('d.tanggal', [$awalBulan, $akhirBulan])
            ->when($useWil, fn($q) => $q->where('o.wilayah_id', $this->wilayahId))
            ->groupBy('dd.produk_id')
            ->selectRaw('dd.produk_id as pid, SUM(dd.jumlah_out) as total')
            ->pluck('total', 'pid');

        $keluarMap = \Illuminate\Support\Facades\DB::table('penjualan_wilayah_details as pwd')
            ->join('penjualan_wilayah as pw', 'pw.id', '=', 'pwd.penjualan_id')
            ->whereNull('pw.deleted_at')
            ->whereBetween('pw.tanggal', [$awalBulan, $akhirBulan])
            ->when($useWil, fn($q) => $q->where('pw.wilayah_asal_id', $this->wilayahId))
            ->groupBy('pwd.produk_id')
            ->selectRaw('pwd.produk_id as pid, SUM(pwd.jumlah) as total')
            ->pluck('total', 'pid');

        return $produkList->map(function ($produk) use ($masukMap, $outMap, $keluarMap) {
            $stokAwal      = $masukMap[$produk->id]['awal'] ?? 0;
            $masuk         = $masukMap[$produk->id]['masuk'] ?? 0;
            $out           = (int) ($outMap[$produk->id] ?? 0);
            $keluarWilayah = (int) ($keluarMap[$produk->id] ?? 0);

            $stokAkhir = ($stokAwal + $masuk) - $out - $keluarWilayah;

            return [
                $produk->nama,
                $stokAwal,
                $masuk,
                $out,
                $keluarWilayah,
                $stokAkhir,
                $produk->hpp,
                max(0, $stokAkhir) * $produk->hpp,
            ];
        })->filter(fn($r) => $r[1] > 0 || $r[2] > 0);
    }

    public function headings(): array
    {
        return ['Produk', 'Stok Awal', 'Masuk', 'OUT Gerobak', 'Keluar Wilayah', 'Stok Akhir', 'HPP', 'Nilai Stok'];
    }

    public function title(): string
    {
        return 'REKAP STOK';
    }

    public function columnFormats(): array
    {
        return [
            'B' => '#,##0',
            'C' => '#,##0',
            'D' => '#,##0',
            'E' => '#,##0',
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E3F2FD']]]];
    }
}