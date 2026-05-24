<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Distribusi;
use App\Models\DistribusiDetail;
use App\Models\Outlet;
use App\Models\Produk;
use App\Models\Wilayah;

class DistribusiRealMeiSeeder extends Seeder
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
            'PLASTIK SAOS' => 'Plastik Saos',
            'KRESEK' => 'Kresek',
            'JASMINE' => 'Jasmine',
            'RAWIT' => 'Rawit',
        ];
        $key = strtoupper(trim($nama));
        $produkNama = $map[$key] ?? null;
        if (!$produkNama)
            return null;
        return Produk::where('nama', $produkNama)->first()?->id;
    }

    private function getOutletId(string $nama): ?string
    {
        $map = [
            'BAROS 1' => 'Baros 1',
            'CISAAT' => 'Cisaat',
            'CICURUG' => 'Cicurug',
            'CIBADAK' => 'Cibadak',
            'KALAPA NUNGGAL' => 'Kalapa Nunggal',
            'KARANG TENGAH' => 'Karang Tengah',
            'LAPANG BOJONG' => 'Lapang Bojong',
            'GUNUNG GURUH' => 'Gunung Guruh',
            'SAGARANTEN' => 'Sagaranten',
            'PASAWAHAN' => 'Pasawahan',
            'PELABUHAN' => 'Pelabuhan',
            'PARUNGKUDA II' => 'Purwakarta',
            'SUKARAJA' => 'Kaswari',
            'BOJONG LOPANG' => 'Bojong Kokosan',
            'LIMUS NUNGGAL' => 'Kalapa Nunggal',
            'GEGER BITUNG' => 'Gunung Guruh',
            'GANG PEDA' => 'Baros 1',
            'YADI' => 'Cisaat',
            'JAMPANG' => 'Sagaranten',
            'CIJANGKAR' => 'Cibadak',
        ];
        $key = trim($nama); // trim spasi di awal dan akhir
        $outletNama = $map[$key] ?? null;
        if (!$outletNama)
            return null;
        return Outlet::where('nama', $outletNama)->first()?->id;
    }

    public function run(): void
    {
        $adminId = \App\Models\User::where('email', 'admin@dimsys.id')->first()?->id;

        $data = json_decode(file_get_contents(storage_path('app/distribusi_mei.json')), true);

        $sukses = 0;
        $skip = 0;

        foreach ($data as $tanggal => $outlets) {
            foreach ($outlets as $outletNama => $produkList) {
                $outletId = $this->getOutletId($outletNama);
                if (!$outletId) {
                    $this->command->warn("Outlet tidak ditemukan: {$outletNama}");
                    $skip++;
                    continue;
                }

                $distribusi = Distribusi::create([
                    'outlet_id' => $outletId,
                    'tanggal' => $tanggal,
                    'keterangan' => null,
                    'created_by' => $adminId,
                ]);

                foreach ($produkList as $produkNama => $jumlah) {
                    $pid = $this->getProdukId($produkNama);
                    if (!$pid || $jumlah <= 0)
                        continue;

                    DistribusiDetail::create([
                        'distribusi_id' => $distribusi->id,
                        'produk_id' => $pid,
                        'jumlah_out' => $jumlah,
                    ]);
                }

                $sukses++;
            }
        }

        $this->command->info("✅ Distribusi: {$sukses} berhasil, {$skip} dilewati");
    }
}