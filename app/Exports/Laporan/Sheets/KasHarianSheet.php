<?php
namespace App\Exports\Laporan\Sheets;

use App\Models\Kas;
use App\Models\Rekening;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KasHarianSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, ShouldAutoSize, WithColumnFormatting
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

        $rekeningIds = Rekening::when(
            $this->wilayahId,
            fn($q) =>
            $q->where('wilayah_id', $this->wilayahId)
        )->pluck('id');

        $query = Kas::with('rekening')
            ->whereIn('rekening_id', $rekeningIds)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->orderBy('tanggal')
            ->orderBy('created_at');

        $rows = collect();
        $saldo = 0;
        $no = 1;

        foreach ($query->get() as $k) {
            if ($k->tipe === 'debit')
                $saldo += $k->jumlah;
            else
                $saldo -= $k->jumlah;

            $rows->push([
                $no++,
                \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y'),
                $k->rekening->nama,
                $k->kategori,
                $k->sub_kategori ?? '-',
                $k->penerima ?? '-',
                $k->keterangan ?? '-',
                $k->tipe === 'debit' ? $k->jumlah : 0,
                $k->tipe === 'kredit' ? $k->jumlah : 0,
                $saldo,
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Rekening', 'Kategori', 'Sub Kategori', 'Penerima', 'Keterangan', 'Debit', 'Kredit', 'Saldo'];
    }

    public function title(): string
    {
        return 'KAS HARIAN';
    }

    public function columnFormats(): array
    {
        return [
            'H' => '#,##0',
            'I' => '#,##0',
            'J' => '#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E3F2FD']]]];
    }
}