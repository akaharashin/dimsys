<?php

namespace App\Http\Controllers;

use App\Models\PenjualanWilayah;
use App\Models\StokOpname;
use App\Traits\ChecksWilayahAccess;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serve media bukti (foto/video STO & Pindah Stok) HANYA untuk user yang berhak.
 *
 * Sebelumnya media disajikan via asset('storage/...') di disk public sehingga
 * bisa diunduh tanpa login lewat URL yang mudah ditebak. Sekarang setiap akses
 * media lewat route ini: wajib login + dicek kepemilikan wilayah-nya.
 */
class MediaController extends Controller
{
    use ChecksWilayahAccess;

    public function show(Media $media): BinaryFileResponse
    {
        $owner = $media->model; // StokOpname / PenjualanWilayah (morphTo)

        if ($owner instanceof StokOpname) {
            $this->otorisasiWilayah($owner->wilayah_id);
        } elseif ($owner instanceof PenjualanWilayah) {
            // Koordinator boleh lihat jika dia di wilayah asal ATAU tujuan.
            $this->otorisasiWilayahSalahSatu([
                $owner->wilayah_asal_id,
                $owner->wilayah_tujuan_id,
            ]);
        } else {
            // Tipe pemilik media tidak dikenal → tolak demi aman.
            abort(403, 'Anda tidak memiliki akses ke file ini.');
        }

        $path = $media->getPath();
        abort_unless(is_file($path), 404, 'File tidak ditemukan.');

        // Tampilkan inline (gambar/video langsung tampil di browser).
        return response()->file($path, [
            'Content-Type' => $media->mime_type,
        ]);
    }
}
