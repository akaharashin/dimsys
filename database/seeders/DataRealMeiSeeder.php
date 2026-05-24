<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StokMasuk;
use App\Models\StokMasukDetail;
use App\Models\Wilayah;
use App\Models\Supplier;
use App\Models\Produk;
use Carbon\Carbon;

class DataRealMeiSeeder extends Seeder
{
    private function getProdukId(string $nama): ?string
    {
        $map = [
            'MINI' => 'Mini',
            'DCC' => 'DCC',
            'DCU' => 'DCU',
            'SUSHI' => 'Sushi',
            'EKADO' => 'Ekado',
            'NORI' => 'Nori',
            'LUMPIA' => 'Lumpia',
            'MOZARELA' => 'Mozarela',
            'CHILI OIL' => 'Chili Oil',
            'BOX ALUMUNIUM' => 'Box Alumunium',
            'DIMSUM CHILI OIL' => 'Dimsum Chili Oil',
            'DIMSUM LUMER' => 'Dimsum Lumer',
            'MINI 20GR' => 'Mini 20Gr',
            'SAUS MENTAI' => 'Saus Mentai',
            'BOX' => 'Box',
            'DG NAGA' => 'DG Naga',
            'DG LUMER' => 'DG Lumer',
            'DG STIK' => 'DG Stik',
            'CIRENG PEDAS' => 'Cireng Pedas',
            'PANGSIT UDANG' => 'Pangsit Udang',
            'PAKET MENTAI' => 'Paket Mentai',
            'SAUS BAKARAN' => 'Saus Mentai',
        ];
        $key = strtoupper(trim($nama));
        $produkNama = $map[$key] ?? null;
        if (!$produkNama)
            return null;
        return Produk::where('nama', $produkNama)->first()?->id;
    }

    public function run(): void
    {
        $sukabumi = Wilayah::where('nama', 'Sukabumi')->first();
        $jayaFood = Supplier::where('nama', 'Jaya Food')->first();
        $adminId = \App\Models\User::where('email', 'admin@dimsys.id')->first()?->id;

        // ============ STOK AWAL 1 MEI 2026 ============
        $stokAwal = [
            'MINI' => 59600,
            'DCC' => 22650,
            'DCU' => 4100,
            'SUSHI' => 3460,
            'EKADO' => 675,
            'NORI' => 3005,
            'LUMPIA' => 2685,
            'MOZARELA' => 3150,
            'CHILI OIL' => 5100,
            'BOX ALUMUNIUM' => 5334,
            'DIMSUM CHILI OIL' => 3900,
            'DIMSUM LUMER' => 3550,
            'MINI 20GR' => 3100,
            'SAUS MENTAI' => 1124,
            'BOX' => 21291,
            'DG NAGA' => 348,
            'DG LUMER' => 450,
            'DG STIK' => 1064,
            'CIRENG PEDAS' => 2100,
            'SAUS BAKARAN' => 2000,
            'PANGSIT UDANG' => 3350,
        ];

        $sm = StokMasuk::create([
            'wilayah_id' => $sukabumi->id,
            'supplier_id' => $jayaFood->id,
            'tanggal' => '2026-05-01',
            'jenis' => 'awal',
            'keterangan' => 'Stok Awal Mei 2026',
            'created_by' => $adminId,
        ]);

        foreach ($stokAwal as $nama => $jumlah) {
            $pid = $this->getProdukId($nama);
            $produk = $pid ? Produk::find($pid) : null;
            if ($pid && $produk) {
                StokMasukDetail::create([
                    'stok_masuk_id' => $sm->id,
                    'produk_id' => $pid,
                    'jumlah' => $jumlah,
                    'hpp' => $produk->hpp,
                ]);
            }
        }

        // ============ STOK MASUK DARI SUPPLIER ============
        $stokMasukData = [
            '2026-05-02' => [
                'MINI' => 10000,
                'DCC' => 7000,
                'SUSHI' => 2000,
                'EKADO' => 1925,
                'NORI' => 1000,
                'LUMPIA' => 1500,
                'MOZARELA' => 450,
                'DIMSUM LUMER' => 550,
                'DG LUMER' => 500,
            ],
            '2026-05-04' => [
                'CHILI OIL' => 5000,
                'SAUS MENTAI' => 776,
                'MINI' => 13000,
                'DCC' => 12000,
                'DCU' => 3000,
                'SUSHI' => 300,
                'NORI' => 2000,
                'LUMPIA' => 1000,
                'DIMSUM CHILI OIL' => 1000,
                'DG LUMER' => 750,
            ],
            '2026-05-05' => [
                'MINI' => 14700,
                'DCC' => 1500,
                'DCU' => 2000,
                'SUSHI' => 1400,
                'EKADO' => 425,
                'NORI' => 2300,
                'LUMPIA' => 900,
                'MOZARELA' => 950,
                'DIMSUM CHILI OIL' => 600,
                'DIMSUM LUMER' => 750,
                'DG LUMER' => 1000,
                'PANGSIT UDANG' => 1600,
                'SAUS MENTAI' => 776,
            ],
            '2026-05-06' => [
                'MINI' => 15000,
                'DCC' => 2500,
                'SUSHI' => 2000,
                'EKADO' => 775,
                'LUMPIA' => 1500,
                'MOZARELA' => 1150,
                'DIMSUM LUMER' => 1400,
                'SAUS MENTAI' => 1548,
            ],
            '2026-05-07' => [
                'MINI' => 17500,
                'DCC' => 2500,
                'SUSHI' => 2000,
                'EKADO' => 1550,
                'NORI' => 500,
                'LUMPIA' => 1000,
                'MOZARELA' => 1500,
                'DIMSUM LUMER' => 1450,
                'SAUS MENTAI' => 768,
            ],
            '2026-05-08' => [
                'MINI' => 19000,
                'DCC' => 11000,
                'SUSHI' => 1500,
                'EKADO' => 1150,
                'NORI' => 50,
                'LUMPIA' => 1050,
                'MOZARELA' => 1300,
                'DIMSUM CHILI OIL' => 1000,
                'DIMSUM LUMER' => 950,
                'DG LUMER' => 500,
            ],
            '2026-05-09' => [
                'MINI' => 13000,
                'DCC' => 18000,
                'SUSHI' => 1500,
                'EKADO' => 1000,
                'NORI' => 1500,
                'LUMPIA' => 1000,
                'MOZARELA' => 1200,
                'DIMSUM CHILI OIL' => 800,
                'DIMSUM LUMER' => 700,
                'DG LUMER' => 250,
                'SAUS MENTAI' => 772,
            ],
            '2026-05-10' => [
                'MINI' => 13000,
                'DCC' => 2000,
                'SUSHI' => 1950,
                'EKADO' => 1325,
                'NORI' => 1450,
                'LUMPIA' => 1000,
                'MOZARELA' => 1650,
                'DIMSUM CHILI OIL' => 950,
                'DIMSUM LUMER' => 850,
                'SAUS MENTAI' => 768,
            ],
            '2026-05-11' => [
                'MINI' => 8000,
                'DCC' => 5500,
                'SUSHI' => 50,
                'CHILI OIL' => 11020,
                'BOX ALUMUNIUM' => 1000,
                'SAUS MENTAI' => 768,
                'BOX' => 39800,
            ],
            '2026-05-12' => [
                'MINI' => 15000,
                'DCC' => 5000,
                'SUSHI' => 2000,
                'EKADO' => 1150,
                'NORI' => 1500,
                'LUMPIA' => 1000,
                'MOZARELA' => 1000,
                'DIMSUM CHILI OIL' => 900,
                'DIMSUM LUMER' => 1000,
                'SAUS MENTAI' => 2312,
                'DG NAGA' => 600,
                'DG LUMER' => 1250,
            ],
            '2026-05-13' => [
                'MINI' => 19000,
                'DCC' => 7500,
                'SUSHI' => 1550,
                'EKADO' => 850,
                'NORI' => 1000,
                'LUMPIA' => 1200,
                'MOZARELA' => 1150,
                'DIMSUM CHILI OIL' => 1000,
                'DIMSUM LUMER' => 950,
                'SAUS MENTAI' => 1532,
            ],
            '2026-05-14' => [
                'MINI' => 17000,
                'DCC' => 6000,
                'SUSHI' => 1000,
                'EKADO' => 875,
                'NORI' => 1000,
                'LUMPIA' => 1000,
                'MOZARELA' => 1000,
                'DIMSUM CHILI OIL' => 1500,
                'DIMSUM LUMER' => 1500,
                'SAUS MENTAI' => 1544,
            ],
            '2026-05-15' => [
                'MINI' => 14000,
                'DCC' => 6000,
                'SUSHI' => 1000,
                'EKADO' => 650,
                'LUMPIA' => 800,
                'MOZARELA' => 900,
                'CHILI OIL' => 7200,
                'DIMSUM CHILI OIL' => 1000,
                'DIMSUM LUMER' => 750,
                'DG LUMER' => 250,
            ],
            '2026-05-16' => [
                'MINI' => 19000,
                'DCC' => 10000,
                'SUSHI' => 1000,
                'EKADO' => 975,
                'NORI' => 1000,
                'LUMPIA' => 1000,
                'MOZARELA' => 1000,
                'DIMSUM CHILI OIL' => 500,
                'DIMSUM LUMER' => 900,
            ],
            '2026-05-17' => [
                'MINI' => 18000,
                'DCC' => 8000,
                'SUSHI' => 1500,
                'EKADO' => 1100,
                'LUMPIA' => 1000,
                'MOZARELA' => 1000,
                'BOX ALUMUNIUM' => 8800,
                'DIMSUM CHILI OIL' => 1000,
                'DIMSUM LUMER' => 950,
                'BOX' => 5000,
            ],
            '2026-05-18' => [
                'DCC' => 500,
                'DCU' => 2000,
                'SUSHI' => 100,
                'NORI' => 1000,
                'LUMPIA' => 250,
                'DG LUMER' => 250,
            ],
            '2026-05-19' => [
                'MINI' => 22000,
                'DCC' => 7000,
                'SUSHI' => 1400,
                'EKADO' => 975,
                'NORI' => 1500,
                'LUMPIA' => 700,
                'MOZARELA' => 1500,
                'CHILI OIL' => 5500,
                'DIMSUM CHILI OIL' => 1000,
                'DIMSUM LUMER' => 1750,
                'SAUS MENTAI' => 1568,
                'BOX' => 37800,
                'DG LUMER' => 250,
            ],
            '2026-05-20' => [
                'MINI' => 21000,
                'DCC' => 9000,
                'DCU' => 300,
                'SUSHI' => 1700,
                'EKADO' => 1025,
                'NORI' => 1000,
                'LUMPIA' => 1000,
                'MOZARELA' => 1000,
                'DIMSUM CHILI OIL' => 1000,
                'DIMSUM LUMER' => 1900,
                'DG LUMER' => 500,
            ],
            '2026-05-21' => [
                'MINI' => 17000,
                'DCC' => 5000,
                'SUSHI' => 1450,
                'EKADO' => 675,
                'NORI' => 1000,
                'LUMPIA' => 900,
                'MOZARELA' => 1400,
                'DIMSUM LUMER' => 1100,
                'SAUS MENTAI' => 772,
                'DG LUMER' => 250,
                'CIRENG PEDAS' => 60,
            ],
        ];

        foreach ($stokMasukData as $tanggal => $produkList) {
            $sm = StokMasuk::create([
                'wilayah_id' => $sukabumi->id,
                'supplier_id' => $jayaFood->id,
                'tanggal' => $tanggal,
                'jenis' => 'masuk',
                'keterangan' => 'Jaya Food',
                'created_by' => $adminId,
            ]);

            foreach ($produkList as $nama => $jumlah) {
                $pid = $this->getProdukId($nama);
                $produk = $pid ? Produk::find($pid) : null;
                if ($pid && $produk && $jumlah > 0) {
                    StokMasukDetail::create([
                        'stok_masuk_id' => $sm->id,
                        'produk_id' => $pid,
                        'jumlah' => $jumlah,
                        'hpp' => $produk->hpp,
                    ]);
                }
            }
        }

        $this->command->info('✅ Data real Stok Masuk Mei 2026 berhasil diinput!');
    }
}