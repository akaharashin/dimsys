<?php
namespace App\Exports\Transaksi;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KasExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $data;
    protected $rekening;

    public function __construct($data, $rekening)
    {
        $this->data = $data;
        $this->rekening = $rekening;
    }

    public function collection()
    {
        $no = 1;
        return $this->data->map(fn($k) => [
            $no++,
            \Carbon\Carbon::parse($k->tanggal)->format('d/m/Y'),
            ucfirst($k->kategori),
            $k->sub_kategori ?? '-',
            $k->outlet?->nama ?? '-',
            $k->keterangan ?? '-',
            $k->penerima ?? '-',
            $k->tipe === 'debit' ? $k->jumlah : 0,
            $k->tipe === 'kredit' ? $k->jumlah : 0,
            $k->saldo_berjalan,
        ]);
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Kategori',
            'Sub Kategori',
            'Outlet',
            'Keterangan',
            'Penerima',
            'Debit',
            'Kredit',
            'Saldo'
        ];
    }

    public function title(): string
    {
        return 'Kas ' . ($this->rekening->nama ?? '');
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E3F2FD']]],
        ];
    }
}