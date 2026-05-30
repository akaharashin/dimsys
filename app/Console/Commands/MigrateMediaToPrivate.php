<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Pindahkan media bukti lama dari disk 'public' (yang ter-symlink & bisa
 * diunduh tanpa login) ke disk privat 'media'. Idempotent & aman diulang:
 * media yang sudah di disk 'media' dilewati.
 *
 * Jalankan SEKALI setelah deploy batch security:
 *   php artisan media:migrate-private
 *
 * Setelah dipastikan semua media tampil normal lewat route media.show,
 * folder storage/app/public/{id} lama sudah kosong dan symlink public/storage
 * tidak lagi membocorkan bukti.
 */
class MigrateMediaToPrivate extends Command
{
    protected $signature = 'media:migrate-private {--dry-run : Hanya tampilkan rencana tanpa memindahkan file}';

    protected $description = 'Pindahkan media bukti dari disk public ke disk privat (media)';

    public function handle(): int
    {
        $tujuan = 'media';
        $dry = (bool) $this->option('dry-run');

        $list = Media::where('disk', '!=', $tujuan)->get();

        if ($list->isEmpty()) {
            $this->info('Tidak ada media yang perlu dipindahkan. Semua sudah di disk privat.');
            return self::SUCCESS;
        }

        $this->info(($dry ? '[DRY-RUN] ' : '') . "Menemukan {$list->count()} media untuk dipindahkan ke disk '{$tujuan}'.");

        $ok = 0;
        $gagal = 0;

        foreach ($list as $media) {
            $sumberDisk = $media->disk;
            $relPath = $media->id . '/' . $media->file_name;

            if (!Storage::disk($sumberDisk)->exists($relPath)) {
                $this->warn("  · Lewati media #{$media->id}: file sumber tidak ada ({$sumberDisk}:{$relPath}).");
                $gagal++;
                continue;
            }

            if ($dry) {
                $this->line("  · [DRY] {$sumberDisk}:{$relPath} → {$tujuan}:{$relPath}");
                $ok++;
                continue;
            }

            try {
                // Stream agar video besar tidak membebani memori.
                $stream = Storage::disk($sumberDisk)->readStream($relPath);
                Storage::disk($tujuan)->writeStream($relPath, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }

                $media->disk = $tujuan;
                $media->save();

                // Hapus folder lama di disk sumber.
                Storage::disk($sumberDisk)->deleteDirectory((string) $media->id);

                $this->line("  · OK media #{$media->id} → {$tujuan}:{$relPath}");
                $ok++;
            } catch (\Throwable $e) {
                $this->error("  · GAGAL media #{$media->id}: " . $e->getMessage());
                $gagal++;
            }
        }

        $this->newLine();
        $this->info("Selesai. Berhasil: {$ok}, Gagal/Lewati: {$gagal}.");

        return self::SUCCESS;
    }
}
