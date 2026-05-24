<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Rekening;

class RekeningSeeder extends Seeder
{
    public function run(): void
    {
        $sukabumi = \App\Models\Wilayah::where('nama', 'Sukabumi')->first()->id;
        $cianjur = \App\Models\Wilayah::where('nama', 'Cianjur')->first()->id;

        Rekening::create(['wilayah_id' => $sukabumi, 'nama' => 'Kas Kecil Sukabumi', 'tipe' => 'kas_kecil', 'saldo_awal' => 0]);
        Rekening::create(['wilayah_id' => $sukabumi, 'nama' => 'BCA Sukabumi', 'tipe' => 'bank', 'saldo_awal' => 0]);
        Rekening::create(['wilayah_id' => $cianjur, 'nama' => 'Kas Kecil Cianjur', 'tipe' => 'kas_kecil', 'saldo_awal' => 0]);
    }
}