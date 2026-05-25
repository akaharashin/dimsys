<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Produk;
use App\Models\StokMasukDetail;
use App\Models\DistribusiDetail;
use App\Models\PenjualanWilayahDetail;
use Illuminate\Http\Request;

class StokApiController extends Controller
{
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

        $produkList = Produk::where('aktif', true)->orderBy('nama')->get();

        $result = $produkList->map(function ($produk) use ($wilayahId) {
            $masuk = StokMasukDetail::whereHas('stokMasuk', fn($q) =>
                $q->where('wilayah_id', $wilayahId)
            )->where('produk_id', $produk->id)->sum('jumlah');

            $sudahOut = DistribusiDetail::whereHas('distribusi', fn($q) =>
                $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $wilayahId))
            )->where('produk_id', $produk->id)->sum('jumlah_out');

            $keluarWilayah = PenjualanWilayahDetail::whereHas('penjualan', fn($q) =>
                $q->where('wilayah_asal_id', $wilayahId)->where('status', 'disetujui')
            )->where('produk_id', $produk->id)->sum('jumlah');

            $stokTersedia = $masuk - $sudahOut - $keluarWilayah;

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