<?php

namespace App\Traits;

/**
 * Lapisan otorisasi per-wilayah terpusat.
 *
 * Aturan:
 *  - admin_pusat & owner  → boleh akses SEMUA wilayah (tidak dibatasi).
 *  - koordinator          → HANYA boleh akses data wilayah_id miliknya.
 *
 * Dipakai konsisten di seluruh controller (show/edit/update/destroy,
 * upload/hapus media, export, dan endpoint API) supaya tidak ada celah IDOR.
 */
trait ChecksWilayahAccess
{
    /**
     * Apakah user saat ini berhak atas satu wilayah tertentu?
     * Hanya koordinator yang dibatasi; selain itu selalu true.
     */
    protected function bolehAksesWilayah($wilayahId): bool
    {
        $user = auth()->user();

        if ($user && $user->hasRole('koordinator')) {
            return $wilayahId !== null
                && (string) $wilayahId === (string) $user->wilayah_id;
        }

        return true;
    }

    /**
     * Versi "salah satu cocok" — untuk data yang melibatkan dua wilayah
     * (mis. pindah stok: asal ATAU tujuan). Koordinator berhak jika dia
     * berada di salah satu wilayah tersebut.
     */
    protected function bolehAksesWilayahSalahSatu(array $wilayahIds): bool
    {
        $user = auth()->user();

        if ($user && $user->hasRole('koordinator')) {
            $ids = array_map('strval', array_filter($wilayahIds, fn($id) => $id !== null));
            return in_array((string) $user->wilayah_id, $ids, true);
        }

        return true;
    }

    /**
     * Abort 403 bila user tidak berhak atas wilayah ini (untuk halaman web).
     */
    protected function otorisasiWilayah($wilayahId): void
    {
        abort_unless(
            $this->bolehAksesWilayah($wilayahId),
            403,
            'Anda tidak memiliki akses ke data wilayah ini.'
        );
    }

    /**
     * Abort 403 bila user bukan bagian dari salah satu wilayah (web).
     */
    protected function otorisasiWilayahSalahSatu(array $wilayahIds): void
    {
        abort_unless(
            $this->bolehAksesWilayahSalahSatu($wilayahIds),
            403,
            'Anda tidak memiliki akses ke data wilayah ini.'
        );
    }
}
