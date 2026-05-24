<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LaporanHarian;
use App\Models\LaporanHarianDetail;
use App\Models\LaporanPengeluaran;
use App\Models\Outlet;
use App\Models\Produk;

class LaporanHarianRealMeiSeeder extends Seeder
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
            'PLASTIK SAOS' => 'Plastik Saos',
            'JASMINE' => 'Jasmine',
            'RAWIT' => 'Rawit',
        ];
        $produkNama = $map[$nama] ?? null;
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
        $outletNama = $map[trim($nama)] ?? null;
        if (!$outletNama)
            return null;
        return Outlet::where('nama', $outletNama)->first()?->id;
    }

    public function run(): void
    {
        $adminId = \App\Models\User::where('email', 'admin@dimsys.id')->first()?->id;
        $data = json_decode(file_get_contents(storage_path('app/laporan_harian_mei.json')), true);

        $sukses = 0;
        $skip = 0;

        foreach ($data as $tanggal => $outlets) {
            foreach ($outlets as $outletNama => $produkList) {
                $outletId = $this->getOutletId($outletNama);
                if (!$outletId) {
                    $this->command->warn("Outlet skip: {$outletNama}");
                    $skip++;
                    continue;
                }

                // Hitung total
                $totalOmset = collect($produkList)->sum('omset');
                $totalKomisi = collect($produkList)->sum('total_komisi');
                $totalSetor = $totalOmset - $totalKomisi;

                $laporan = LaporanHarian::create([
                    'outlet_id' => $outletId,
                    'tanggal' => $tanggal,
                    'total_setor' => max(0, $totalSetor),
                    'total_pengeluaran' => 0,
                    'status' => 'final',
                    'created_by' => $adminId,
                ]);

                foreach ($produkList as $item) {
                    $pid = $this->getProdukId($item['produk']);
                    if (!$pid || $item['out'] <= 0)
                        continue;

                    LaporanHarianDetail::create([
                        'laporan_id' => $laporan->id,
                        'produk_id' => $pid,
                        'sisa' => $item['sisa'],
                        'terjual' => $item['terjual'],
                        'omset' => $item['omset'],
                        'modal' => $item['modal'],
                        'komisi' => $item['total_komisi'],
                    ]);
                }

                $sukses++;
            }
        }

        $this->command->info("✅ Laporan Harian: {$sukses} berhasil, {$skip} dilewati");
    }
}