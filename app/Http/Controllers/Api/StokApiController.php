<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Produk;
use App\Models\StokMasuk;
use App\Models\StokMasukDetail;
use App\Models\DistribusiDetail;
use App\Models\PenjualanWilayahDetail;
use App\Traits\ChecksWilayahAccess;
use Illuminate\Http\Request;

class StokApiController extends Controller
{
    use ChecksWilayahAccess;

    public function getStokTersedia(Request $request)
    {
        if ($request->filled('wilayah_id')) {
            $wilayahId = $request->wilayah_id;
        } elseif ($request->filled('outlet_id')) {
            $outlet = Outlet::find($request->outlet_id);
            if (!$outlet) return response()->json([]);
            $wilayahId = $outlet->wilayah_id;
        } else {
            return response()->json([]);
        }

        // Koordinator hanya boleh melihat stok wilayahnya sendiri
        // (cegah enumerasi wilayah lain via ?wilayah_id / ?outlet_id).
        if (!$this->bolehAksesWilayah($wilayahId)) {
            return response()->json(['error' => 'Anda tidak memiliki akses ke data wilayah ini.'], 403);
        }

        $produkList = Produk::where('aktif', true)->orderBy('nama')->get();

        // Formula freezer dipusatkan di StokService (identik dengan validasi & RekapStok).
        $svc    = new \App\Services\StokService();
        $sampai = \Carbon\Carbon::today()->toDateString();
        $cutoff = $svc->freezerCutoff($wilayahId, $sampai);

        $result = $produkList->map(function ($produk) use ($wilayahId, $svc, $sampai, $cutoff) {
            $stokTersedia = $svc->stokFreezer($wilayahId, $produk->id, $sampai, $cutoff);

            return [
                'produk_id'      => $produk->id,
                'produk_nama'    => $produk->nama,
                'harga_jual'     => $produk->harga_jual,
                'stok_tersedia'  => max(0, $stokTersedia),
            ];
        });

        return response()->json($result);
    }
}