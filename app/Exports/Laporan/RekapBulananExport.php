<?php
namespace App\Exports\Laporan;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapBulananExport implements WithMultipleSheets
{
    protected $bulan;
    protected $wilayahId;

    public function __construct(string $bulan, ?string $wilayahId)
    {
        $this->bulan = $bulan;
        $this->wilayahId = $wilayahId;
    }

    public function sheets(): array
    {
        return [
            new Sheets\RekapStokSheet($this->bulan, $this->wilayahId),
            new Sheets\StokMasukSheet($this->bulan, $this->wilayahId),
            new Sheets\DistribusiSheet($this->bulan, $this->wilayahId),
            new Sheets\PenjualanSheet($this->bulan, $this->wilayahId),
            new Sheets\KasHarianSheet($this->bulan, $this->wilayahId),
            new Sheets\OmsetSheet($this->bulan, $this->wilayahId),
            new Sheets\RataRataOutSheet($this->bulan, $this->wilayahId),
        ];
    }
}