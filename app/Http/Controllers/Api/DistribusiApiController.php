<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Distribusi;
use App\Models\LaporanHarian;
use App\Models\Outlet;
use App\Traits\ChecksWilayahAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DistribusiApiController extends Controller
{
    use ChecksWilayahAccess;

    public function getByOutletTanggal(Request $request)
    {
        $outletId = $request->outlet_id;
        $tanggal  = $request->tanggal;

        if (!$outletId || !$tanggal) {
            return response()->json([]);
        }

        // Koordinator hanya boleh akses outlet di wilayahnya sendiri.
        $outlet = Outlet::find($outletId);
        if (!$outlet || !$this->bolehAksesWilayah($outlet->wilayah_id)) {
            return response()->json(['error' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $kemarin = Carbon::parse($tanggal)->subDay()->format('Y-m-d');

        $distribusi = Distribusi::with('details.produk')
            ->where('outlet_id', $outletId)
            ->where('tanggal', $tanggal)
            ->first();

        $laporanKemarin = LaporanHarian::with('details.produk')
            ->where('outlet_id', $outletId)
            ->where('tanggal', $kemarin)
            ->first();

        $produkMap = [];

        // Sisa kemarin → jadi stok awal hari ini
        if ($laporanKemarin) {
            foreach ($laporanKemarin->details as $d) {
                if ($d->sisa <= 0) continue;
                $produkMap[$d->produk_id] = [
                    'produk_id'         => $d->produk_id,
                    'produk_nama'       => $d->produk->nama,
                    'harga_jual'        => $d->produk->harga_mitra,
                    'komisi'            => $d->produk->komisi,
                    'sisa_kemarin'      => (int) $d->sisa,
                    'distribusi_hari'   => 0,
                    'jumlah_out'        => (int) $d->sisa,
                    'dari_sisa_kemarin' => true,
                ];
            }
        }

        // Tambah/merge distribusi hari ini
        if ($distribusi) {
            foreach ($distribusi->details as $d) {
                if (isset($produkMap[$d->produk_id])) {
                    $produkMap[$d->produk_id]['distribusi_hari']   = (int) $d->jumlah_out;
                    $produkMap[$d->produk_id]['jumlah_out']        += (int) $d->jumlah_out;
                    $produkMap[$d->produk_id]['dari_sisa_kemarin'] = false;
                } else {
                    $produkMap[$d->produk_id] = [
                        'produk_id'         => $d->produk_id,
                        'produk_nama'       => $d->produk->nama,
                        'harga_jual'        => $d->produk->harga_mitra,
                        'komisi'            => $d->produk->komisi,
                        'sisa_kemarin'      => 0,
                        'distribusi_hari'   => (int) $d->jumlah_out,
                        'jumlah_out'        => (int) $d->jumlah_out,
                        'dari_sisa_kemarin' => false,
                    ];
                }
            }
        }

        if (empty($produkMap)) {
            return response()->json([]);
        }

        return response()->json(array_values($produkMap));
    }
}
