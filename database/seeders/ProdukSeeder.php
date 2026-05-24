<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Produk;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        $produk = [
            // Produk Utama Dimsum
            ['nama' => 'Mini', 'hpp' => 800, 'harga_mitra' => 900, 'harga_jual' => 1250, 'harga_umum' => 1030, 'harga_agen' => 850, 'komisi' => 50],
            ['nama' => 'Mini 20Gr', 'hpp' => 1150, 'harga_mitra' => 1250, 'harga_jual' => 2000, 'harga_umum' => 1250, 'harga_agen' => 1300, 'komisi' => 1000],
            ['nama' => 'DCC', 'hpp' => 1600, 'harga_mitra' => 2250, 'harga_jual' => 3750, 'harga_umum' => 2250, 'harga_agen' => 1810, 'komisi' => 500],
            ['nama' => 'DCU', 'hpp' => 1650, 'harga_mitra' => 2250, 'harga_jual' => 3750, 'harga_umum' => 2250, 'harga_agen' => 1810, 'komisi' => 500],
            ['nama' => 'DCA', 'hpp' => 1550, 'harga_mitra' => 2250, 'harga_jual' => 3750, 'harga_umum' => 2250, 'harga_agen' => 1760, 'komisi' => 500],
            ['nama' => 'DCB', 'hpp' => 1550, 'harga_mitra' => 2250, 'harga_jual' => 3750, 'harga_umum' => 2250, 'harga_agen' => 1760, 'komisi' => 500],
            ['nama' => 'Sushi', 'hpp' => 1900, 'harga_mitra' => 2350, 'harga_jual' => 3750, 'harga_umum' => 2350, 'harga_agen' => 2150, 'komisi' => 500],
            ['nama' => 'Ekado', 'hpp' => 2400, 'harga_mitra' => 2750, 'harga_jual' => 3750, 'harga_umum' => 2750, 'harga_agen' => 2575, 'komisi' => 500],
            ['nama' => 'Nori', 'hpp' => 1900, 'harga_mitra' => 2350, 'harga_jual' => 3750, 'harga_umum' => 2350, 'harga_agen' => 2150, 'komisi' => 500],
            ['nama' => 'Lumpia', 'hpp' => 2100, 'harga_mitra' => 2450, 'harga_jual' => 3750, 'harga_umum' => 2450, 'harga_agen' => 2250, 'komisi' => 500],
            ['nama' => 'Mozarela', 'hpp' => 2000, 'harga_mitra' => 2350, 'harga_jual' => 3750, 'harga_umum' => 2350, 'harga_agen' => 2150, 'komisi' => 500],
            ['nama' => 'Jasmine', 'hpp' => 2100, 'harga_mitra' => 2450, 'harga_jual' => 3750, 'harga_umum' => 2450, 'harga_agen' => 2250, 'komisi' => 500],
            ['nama' => 'Rawit', 'hpp' => 1900, 'harga_mitra' => 2350, 'harga_jual' => 3750, 'harga_umum' => 2350, 'harga_agen' => 2150, 'komisi' => 0],
            ['nama' => 'Pangsit Udang', 'hpp' => 1750, 'harga_mitra' => 2250, 'harga_jual' => 3750, 'harga_umum' => 2250, 'harga_agen' => 1910, 'komisi' => 500],
            ['nama' => 'Dimsum Lumer', 'hpp' => 2150, 'harga_mitra' => 2550, 'harga_jual' => 3750, 'harga_umum' => 2550, 'harga_agen' => 2350, 'komisi' => 500],
            ['nama' => 'Dimsum Chili Oil', 'hpp' => 1800, 'harga_mitra' => 2250, 'harga_jual' => 3750, 'harga_umum' => 2250, 'harga_agen' => 1910, 'komisi' => 500],
            ['nama' => 'Chili Oil', 'hpp' => 750, 'harga_mitra' => 900, 'harga_jual' => 1000, 'harga_umum' => 900, 'harga_agen' => 850, 'komisi' => 0],
            ['nama' => 'Ceker', 'hpp' => 7800, 'harga_mitra' => 9300, 'harga_jual' => 12000, 'harga_umum' => 9300, 'harga_agen' => 8800, 'komisi' => 1000],

            // Paket
            ['nama' => 'Saus Mentai', 'hpp' => 1400, 'harga_mitra' => 2200, 'harga_jual' => 5000, 'harga_umum' => 1800, 'harga_agen' => 0, 'komisi' => 700],
            ['nama' => 'Paket Mentai', 'hpp' => 1900, 'harga_mitra' => 3600, 'harga_jual' => 5000, 'harga_umum' => 3600, 'harga_agen' => 3400, 'komisi' => 700],
            ['nama' => 'Paket Tar Tar', 'hpp' => 1900, 'harga_mitra' => 3000, 'harga_jual' => 5000, 'harga_umum' => 3000, 'harga_agen' => 2500, 'komisi' => 1000],

            // DG Series
            ['nama' => 'DG Lumer', 'hpp' => 2400, 'harga_mitra' => 2800, 'harga_jual' => 5000, 'harga_umum' => 0, 'harga_agen' => 2600, 'komisi' => 500],
            ['nama' => 'DG Stik', 'hpp' => 7000, 'harga_mitra' => 10150, 'harga_jual' => 15000, 'harga_umum' => 0, 'harga_agen' => 8400, 'komisi' => 1750],
            ['nama' => 'DG Naga', 'hpp' => 2200, 'harga_mitra' => 2600, 'harga_jual' => 5000, 'harga_umum' => 0, 'harga_agen' => 2400, 'komisi' => 500],
            ['nama' => 'Cireng Pedas', 'hpp' => 5700, 'harga_mitra' => 1300, 'harga_jual' => 2500, 'harga_umum' => 0, 'harga_agen' => 1100, 'komisi' => 250],

            // Packaging & Operasional
            ['nama' => 'Box', 'hpp' => 285, 'harga_mitra' => 400, 'harga_jual' => 400, 'harga_umum' => 0, 'harga_agen' => 300, 'komisi' => 0],
            ['nama' => 'Box Alumunium', 'hpp' => 1350, 'harga_mitra' => 2000, 'harga_jual' => 0, 'harga_umum' => 0, 'harga_agen' => 0, 'komisi' => 0],
            ['nama' => 'Mika K', 'hpp' => 12000, 'harga_mitra' => 12000, 'harga_jual' => 12000, 'harga_umum' => 12000, 'harga_agen' => 12000, 'komisi' => 0],
            ['nama' => 'Mika B', 'hpp' => 16000, 'harga_mitra' => 16000, 'harga_jual' => 16000, 'harga_umum' => 16000, 'harga_agen' => 16000, 'komisi' => 0],
            ['nama' => 'Kresek', 'hpp' => 4000, 'harga_mitra' => 4000, 'harga_jual' => 4000, 'harga_umum' => 4000, 'harga_agen' => 4000, 'komisi' => 0],
            ['nama' => 'Plastik Saos', 'hpp' => 8000, 'harga_mitra' => 8000, 'harga_jual' => 8000, 'harga_umum' => 8000, 'harga_agen' => 8000, 'komisi' => 0],
            ['nama' => 'Tusuk Gigi', 'hpp' => 3000, 'harga_mitra' => 3000, 'harga_jual' => 3000, 'harga_umum' => 3000, 'harga_agen' => 3000, 'komisi' => 0],
            ['nama' => 'Klip', 'hpp' => 3000, 'harga_mitra' => 3000, 'harga_jual' => 3000, 'harga_umum' => 3000, 'harga_agen' => 3000, 'komisi' => 0],
        ];

        foreach ($produk as $p) {
            Produk::create($p);
        }
    }
}