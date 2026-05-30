<?php

namespace App\Console\Commands;

use App\Models\PenjualanWilayah;
use App\Models\Produk;
use App\Models\StokMasukDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Backfill HPP untuk stok_masuk hasil PINDAH STOK (transfer) lama yang ter-record
 * dengan hpp = 0. Diisi dari HPP master produk saat ini. Idempotent & aman diulang.
 *
 * Hanya menyentuh stok_masuk yang benar-benar berasal dari transfer (tertaut via
 * penjualan_wilayah.transfer_stok_masuk_id) — TIDAK menyentuh stok masuk biasa
 * maupun koreksi STO.
 *
 *   php artisan stok:backfill-hpp-transfer --dry-run
 *   php artisan stok:backfill-hpp-transfer
 */
class BackfillHppTransfer extends Command
{
    protected $signature = 'stok:backfill-hpp-transfer {--dry-run : Hanya tampilkan rencana tanpa mengubah data}';

    protected $description = 'Isi HPP stok masuk hasil transfer lama (hpp=0) dari HPP master produk';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        // Kumpulan id stok_masuk yang berasal dari transfer (punya transfer_stok_masuk_id).
        $transferStokMasukIds = PenjualanWilayah::whereNotNull('transfer_stok_masuk_id')
            ->pluck('transfer_stok_masuk_id');

        if ($transferStokMasukIds->isEmpty()) {
            $this->info('Tidak ada stok masuk hasil transfer. Tidak ada yang perlu di-backfill.');
            return self::SUCCESS;
        }

        $details = StokMasukDetail::whereIn('stok_masuk_id', $transferStokMasukIds)
            ->where('hpp', 0)
            ->get();

        if ($details->isEmpty()) {
            $this->info('Semua detail transfer sudah punya HPP. Tidak ada yang perlu diubah.');
            return self::SUCCESS;
        }

        $this->info(($dry ? '[DRY-RUN] ' : '') . "Menemukan {$details->count()} detail transfer ber-HPP 0.");

        $hppMaster = Produk::pluck('hpp', 'id'); // id => hpp
        $ubah = 0;
        $lewat = 0;

        foreach ($details as $d) {
            $hpp = (int) ($hppMaster[$d->produk_id] ?? 0);
            if ($hpp <= 0) {
                $this->warn("  · Lewati detail #{$d->id}: HPP master produk juga 0/null.");
                $lewat++;
                continue;
            }

            if ($dry) {
                $this->line("  · [DRY] detail #{$d->id}: hpp 0 → {$hpp}");
                $ubah++;
                continue;
            }

            DB::table('stok_masuk_details')->where('id', $d->id)->update(['hpp' => $hpp]);
            $ubah++;
        }

        $this->newLine();
        $this->info("Selesai. Diubah: {$ubah}, Dilewati: {$lewat}.");

        return self::SUCCESS;
    }
}
