<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Helper rentang tanggal bulan — untuk query SARGABLE (whereBetween),
 * pengganti whereYear()+whereMonth() yang membungkus kolom dalam fungsi
 * sehingga index `tanggal` tidak terpakai.
 *
 * Kolom `tanggal` bertipe DATE, jadi [awal, akhir] inklusif = identik
 * dengan whereYear+whereMonth.
 */
class Periode
{
    /**
     * @param  string $bulan  format 'Y-m' (mis. '2026-05')
     * @return array{0:string,1:string}  [tanggalAwal, tanggalAkhir] (Y-m-d)
     */
    public static function range(string $bulan): array
    {
        $awal = Carbon::parse($bulan . '-01')->startOfMonth();
        return [$awal->toDateString(), $awal->copy()->endOfMonth()->toDateString()];
    }
}
