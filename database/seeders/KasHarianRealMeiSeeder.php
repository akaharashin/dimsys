<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kas;
use App\Models\Rekening;

class KasHarianRealMeiSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = \App\Models\User::where('email', 'admin@dimsys.id')->first()?->id;
        $rekening = Rekening::where('nama', 'Kas Kecil Sukabumi')->first();

        if (!$rekening) {
            $this->command->error('Rekening Kas Kecil tidak ditemukan!');
            return;
        }

        $data = json_decode(file_get_contents(storage_path('app/kas_kecil_mei.json')), true);

        $saldo = 0;
        $sukses = 0;

        foreach ($data as $item) {
            if ($item['tipe'] === 'debit') {
                $saldo += $item['jumlah'];
            } else {
                $saldo -= $item['jumlah'];
            }

            Kas::create([
                'rekening_id' => $rekening->id,
                'outlet_id' => null,
                'tanggal' => $item['tanggal'],
                'tipe' => $item['tipe'],
                'kategori' => $item['kategori'],
                'sub_kategori' => $item['sub_kategori'],
                'keterangan' => $item['sub_kategori'],
                'penerima' => $item['penerima'],
                'jumlah' => $item['jumlah'],
                'saldo' => $saldo,
                'created_by' => $adminId,
            ]);

            $sukses++;
        }

        $this->command->info("✅ Kas Harian: {$sukses} transaksi berhasil!");
        $this->command->info("   Saldo akhir: Rp " . number_format($saldo));
    }
}