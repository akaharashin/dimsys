<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Wilayah;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $wilayah = [
            ['nama' => 'Sukabumi', 'tipe' => 'pusat'],
            ['nama' => 'Cianjur', 'tipe' => 'cabang'],
            ['nama' => 'Bogor', 'tipe' => 'cabang'],
            ['nama' => 'Bandung', 'tipe' => 'cabang'],
            ['nama' => 'Garut', 'tipe' => 'cabang'],
            ['nama' => 'Tasikmalaya', 'tipe' => 'cabang'],
            ['nama' => 'Karawang', 'tipe' => 'cabang'],
            ['nama' => 'Bekasi', 'tipe' => 'cabang'],
            ['nama' => 'Depok', 'tipe' => 'cabang'],
            ['nama' => 'Cirebon', 'tipe' => 'cabang'],
        ];

        foreach ($wilayah as $w) {
            Wilayah::create($w);
        }
    }
}