<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Distribusi;
use Illuminate\Http\Request;

class DistribusiApiController extends Controller
{
    public function getByOutletTanggal(Request $request)
    {
        $distribusi = Distribusi::with('details.produk')
            ->where('outlet_id', $request->outlet_id)
            ->where('tanggal', $request->tanggal)
            ->first();

        if (!$distribusi)
            return response()->json([]);

        $result = $distribusi->details->map(fn($d) => [
            'produk_id' => $d->produk_id,
            'produk_nama' => $d->produk->nama,
            'jumlah_out' => $d->jumlah_out,
            'harga_jual' => $d->produk->harga_jual,
            'komisi' => $d->produk->komisi,
        ]);

        return response()->json($result);
    }
}