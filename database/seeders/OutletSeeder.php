<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Outlet;

class OutletSeeder extends Seeder
{
    public function run(): void
    {
        $sukabumi = \App\Models\Wilayah::where('nama', 'Sukabumi')->first()->id;
        $cianjur = \App\Models\Wilayah::where('nama', 'Cianjur')->first()->id;
        $bogor = \App\Models\Wilayah::where('nama', 'Bogor')->first()->id;

        $outlets = [
            // Sukabumi - Lokal
            ['nama' => 'Baros 1', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],
            ['nama' => 'Cisaat', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],
            ['nama' => 'Cicurug', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],
            ['nama' => 'Cidahu', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],
            ['nama' => 'Cibadak', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],
            ['nama' => 'Karang Tengah', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],
            ['nama' => 'Sagaranten', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],
            ['nama' => 'Lapang Bojong', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],
            ['nama' => 'Bojong Kokosan', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],
            ['nama' => 'Kaswari', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],
            ['nama' => 'Pelabuhan', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],
            ['nama' => 'Gunung Guruh', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],
            ['nama' => 'Kalapa Nunggal', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],
            ['nama' => 'Pasawahan', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],
            ['nama' => 'Purwakarta', 'wilayah_id' => $sukabumi, 'tipe' => 'mitra'],

            // Cianjur
            ['nama' => 'Duta Cemerlang', 'wilayah_id' => $cianjur, 'tipe' => 'mitra'],
            ['nama' => 'Warung Danas', 'wilayah_id' => $cianjur, 'tipe' => 'mitra'],
            ['nama' => 'PJR', 'wilayah_id' => $cianjur, 'tipe' => 'mitra'],
            ['nama' => 'Ciranjang', 'wilayah_id' => $cianjur, 'tipe' => 'mitra'],
            ['nama' => 'Maleber', 'wilayah_id' => $cianjur, 'tipe' => 'mitra'],
            ['nama' => 'Tungturunan', 'wilayah_id' => $cianjur, 'tipe' => 'mitra'],
            ['nama' => 'Jati', 'wilayah_id' => $cianjur, 'tipe' => 'mitra'],
            ['nama' => 'Sipon', 'wilayah_id' => $cianjur, 'tipe' => 'mitra'],
            ['nama' => 'Kodim', 'wilayah_id' => $cianjur, 'tipe' => 'mitra'],
            ['nama' => 'Salajambe', 'wilayah_id' => $cianjur, 'tipe' => 'mitra'],
            ['nama' => 'Rajamandala', 'wilayah_id' => $cianjur, 'tipe' => 'mitra'],

            // Bogor
            ['nama' => 'Bogor Pusat', 'wilayah_id' => $bogor, 'tipe' => 'mitra'],
            ['nama' => 'Citeureup', 'wilayah_id' => $bogor, 'tipe' => 'mitra'],
            ['nama' => 'Jasinga', 'wilayah_id' => $bogor, 'tipe' => 'mitra'],
            ['nama' => 'Puncak', 'wilayah_id' => $bogor, 'tipe' => 'mitra'],
        ];

        foreach ($outlets as $o) {
            Outlet::create($o);
        }
    }
}