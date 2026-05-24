<?php
namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\PenjualanWilayah;
use App\Models\PenjualanWilayahDetail;
use App\Models\StokMasuk;
use App\Models\StokMasukDetail;
use App\Models\Wilayah;
use App\Models\Produk;
use App\Models\DistribusiDetail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Transaksi\PenjualanWilayahExport;

class PenjualanWilayahController extends Controller
{
    public function index(Request $request)
    {
        $wilayahList = Wilayah::where('aktif', true)->orderBy('nama')->get();

        $query = PenjualanWilayah::with(['wilayahAsal', 'wilayahTujuan', 'details'])
            ->orderByDesc('tanggal');

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->filled('wilayah_asal_id')) {
            $query->where('wilayah_asal_id', $request->wilayah_asal_id);
        }
        if ($request->filled('wilayah_tujuan_id')) {
            $query->where('wilayah_tujuan_id', $request->wilayah_tujuan_id);
        }
        if ($request->filled('status_bayar')) {
            $query->where('status_bayar', $request->status_bayar);
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }

        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;
        $penjualan = $query->paginate($perPage)->withQueryString();

        // Summary: financial totals only for penjualan type
        $summaryQuery = PenjualanWilayah::where('tipe', 'penjualan');
        if ($request->filled('wilayah_asal_id'))
            $summaryQuery->where('wilayah_asal_id', $request->wilayah_asal_id);
        if ($request->filled('dari'))
            $summaryQuery->whereDate('tanggal', '>=', $request->dari);
        if ($request->filled('sampai'))
            $summaryQuery->whereDate('tanggal', '<=', $request->sampai);

        $totalNilai = $summaryQuery->sum('total');
        $totalLunas = (clone $summaryQuery)->where('status_bayar', 'lunas')->sum('total');
        $totalBelum = (clone $summaryQuery)->where('status_bayar', 'belum_lunas')->sum('total');

        return view('transaksi.penjualan-wilayah.index', compact(
            'penjualan',
            'wilayahList',
            'totalNilai',
            'totalLunas',
            'totalBelum'
        ));
    }

    public function export(Request $request)
    {
        $query = PenjualanWilayah::with(['wilayahAsal', 'wilayahTujuan', 'details.produk'])
            ->orderByDesc('tanggal');

        if ($request->filled('tipe'))
            $query->where('tipe', $request->tipe);
        if ($request->filled('wilayah_asal_id'))
            $query->where('wilayah_asal_id', $request->wilayah_asal_id);
        if ($request->filled('wilayah_tujuan_id'))
            $query->where('wilayah_tujuan_id', $request->wilayah_tujuan_id);
        if ($request->filled('status_bayar'))
            $query->where('status_bayar', $request->status_bayar);
        if ($request->filled('dari'))
            $query->whereDate('tanggal', '>=', $request->dari);
        if ($request->filled('sampai'))
            $query->whereDate('tanggal', '<=', $request->sampai);

        $data = $query->get();
        $filename = 'transfer-penjualan-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new PenjualanWilayahExport($data), $filename);
    }

    public function create()
    {
        $wilayah = Wilayah::where('aktif', true)->orderBy('nama')->get();
        $produk = Produk::where('aktif', true)->orderBy('nama')->get();
        return view('transaksi.penjualan-wilayah.create', compact('wilayah', 'produk'));
    }

    public function store(Request $request)
    {
        $tipe = $request->input('tipe', 'penjualan');

        $rules = [
            'tipe' => 'required|in:transfer,penjualan',
            'wilayah_asal_id' => 'required|exists:wilayah,id',
            'wilayah_tujuan_id' => 'required|exists:wilayah,id|different:wilayah_asal_id',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
            'jumlah' => 'required|array',
            'jumlah.*' => 'nullable|integer|min:0',
        ];

        if ($tipe === 'penjualan') {
            $rules['status_bayar'] = 'required|in:lunas,belum_lunas,sebagian';
        }

        $request->validate($rules, [
            'tipe.required' => 'Tipe transaksi wajib dipilih.',
            'tipe.in' => 'Tipe transaksi tidak valid.',
            'wilayah_asal_id.required' => 'Wilayah asal wajib dipilih.',
            'wilayah_asal_id.exists' => 'Wilayah asal tidak valid.',
            'wilayah_tujuan_id.required' => 'Wilayah tujuan wajib dipilih.',
            'wilayah_tujuan_id.exists' => 'Wilayah tujuan tidak valid.',
            'wilayah_tujuan_id.different' => 'Wilayah tujuan tidak boleh sama dengan wilayah asal.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'status_bayar.required' => 'Status bayar wajib dipilih.',
            'status_bayar.in' => 'Status bayar harus berupa lunas, belum lunas, atau sebagian.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
            'jumlah.*.integer' => 'Jumlah produk harus berupa bilangan bulat.',
            'jumlah.*.min' => 'Jumlah produk tidak boleh bernilai negatif.',
        ]);

        $hasAny = collect($request->jumlah ?? [])->filter(fn($j) => (int)$j > 0)->count() > 0;
        if (!$hasAny) {
            return back()->with('error', 'Minimal satu produk harus memiliki jumlah lebih dari 0.')->withInput();
        }

        // Validasi stok tersedia (berlaku untuk semua tipe)
        foreach ($request->jumlah as $pid => $jumlah) {
            $jumlah = (int) $jumlah;
            if ($jumlah <= 0) continue;

            $masuk = StokMasukDetail::whereHas('stokMasuk', fn($q) =>
                $q->where('wilayah_id', $request->wilayah_asal_id)
            )->where('produk_id', $pid)->sum('jumlah');

            $sudahOut = DistribusiDetail::whereHas('distribusi', fn($q) =>
                $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $request->wilayah_asal_id))
            )->where('produk_id', $pid)->sum('jumlah_out');

            $keluarWilayah = PenjualanWilayahDetail::whereHas('penjualan', fn($q) =>
                $q->where('wilayah_asal_id', $request->wilayah_asal_id)
            )->where('produk_id', $pid)->sum('jumlah');

            $stokTersedia = max(0, $masuk - $sudahOut - $keluarWilayah);
            $produkObj = Produk::find($pid);

            if ($jumlah > $stokTersedia) {
                return back()
                    ->with('error', "Stok {$produkObj->nama} tidak cukup. Tersedia: {$stokTersedia} pcs, diminta: {$jumlah} pcs.")
                    ->withInput();
            }
        }

        try {
            if ($tipe === 'transfer') {
                $wilayahAsal = Wilayah::find($request->wilayah_asal_id);

                $penjualan = PenjualanWilayah::create([
                    'tipe' => 'transfer',
                    'wilayah_asal_id' => $request->wilayah_asal_id,
                    'wilayah_tujuan_id' => $request->wilayah_tujuan_id,
                    'tanggal' => $request->tanggal,
                    'total' => 0,
                    'status_bayar' => null,
                    'keterangan' => $request->keterangan,
                    'created_by' => auth()->id(),
                ]);

                foreach ($request->jumlah as $pid => $jumlah) {
                    $jumlah = (int) $jumlah;
                    if ($jumlah > 0) {
                        PenjualanWilayahDetail::create([
                            'penjualan_id' => $penjualan->id,
                            'produk_id' => $pid,
                            'jumlah' => $jumlah,
                            'harga_agen' => 0,
                            'subtotal' => 0,
                        ]);
                    }
                }

                $stokMasuk = StokMasuk::create([
                    'wilayah_id' => $request->wilayah_tujuan_id,
                    'supplier_id' => null,
                    'tanggal' => $request->tanggal,
                    'jenis' => 'masuk',
                    'keterangan' => 'Transfer dari ' . $wilayahAsal->nama,
                    'created_by' => auth()->id(),
                ]);

                foreach ($request->jumlah as $pid => $jumlah) {
                    $jumlah = (int) $jumlah;
                    if ($jumlah > 0) {
                        StokMasukDetail::create([
                            'stok_masuk_id' => $stokMasuk->id,
                            'produk_id' => $pid,
                            'jumlah' => $jumlah,
                            'hpp' => 0,
                        ]);
                    }
                }

                $penjualan->update(['transfer_stok_masuk_id' => $stokMasuk->id]);

                return redirect()->route('transaksi.penjualan-wilayah.index')
                    ->with('success', 'Transfer stok berhasil dicatat.');
            } else {
                $total = 0;
                foreach ($request->jumlah as $pid => $jumlah) {
                    $jumlah = (int) $jumlah;
                    if ($jumlah > 0) {
                        $produk = Produk::find($pid);
                        $total += $jumlah * $produk->harga_agen;
                    }
                }

                $penjualan = PenjualanWilayah::create([
                    'tipe' => 'penjualan',
                    'wilayah_asal_id' => $request->wilayah_asal_id,
                    'wilayah_tujuan_id' => $request->wilayah_tujuan_id,
                    'tanggal' => $request->tanggal,
                    'total' => $total,
                    'status_bayar' => $request->status_bayar,
                    'keterangan' => $request->keterangan,
                    'created_by' => auth()->id(),
                ]);

                foreach ($request->jumlah as $pid => $jumlah) {
                    $jumlah = (int) $jumlah;
                    if ($jumlah > 0) {
                        $produk = Produk::find($pid);
                        PenjualanWilayahDetail::create([
                            'penjualan_id' => $penjualan->id,
                            'produk_id' => $pid,
                            'jumlah' => $jumlah,
                            'harga_agen' => $produk->harga_agen,
                            'subtotal' => $jumlah * $produk->harga_agen,
                        ]);
                    }
                }

                return redirect()->route('transaksi.penjualan-wilayah.index')
                    ->with('success', 'Penjualan wilayah berhasil dicatat.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan data. Silakan coba lagi.')->withInput();
        }
    }

    public function show(PenjualanWilayah $penjualanWilayah)
    {
        $penjualanWilayah->load(['wilayahAsal', 'wilayahTujuan', 'details.produk']);
        return view('transaksi.penjualan-wilayah.show', compact('penjualanWilayah'));
    }

    public function update(Request $request, PenjualanWilayah $penjualanWilayah)
    {
        if ($penjualanWilayah->tipe === 'transfer') {
            return back()->with('error', 'Transfer tidak memiliki status bayar.');
        }

        $request->validate([
            'status_bayar' => 'required|in:lunas,belum_lunas,sebagian',
        ], [
            'status_bayar.required' => 'Status bayar wajib dipilih.',
            'status_bayar.in' => 'Status bayar harus berupa lunas, belum lunas, atau sebagian.',
        ]);

        try {
            $penjualanWilayah->update(['status_bayar' => $request->status_bayar]);
            return back()->with('success', 'Status bayar berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status bayar. Silakan coba lagi.');
        }
    }

    public function destroy(PenjualanWilayah $penjualanWilayah)
    {
        $tipe = $penjualanWilayah->tipe;

        if ($tipe === 'transfer' && $penjualanWilayah->transfer_stok_masuk_id) {
            $stokMasuk = StokMasuk::find($penjualanWilayah->transfer_stok_masuk_id);
            if ($stokMasuk) {
                $stokMasuk->update(['deleted_by' => auth()->id()]);
                $stokMasuk->delete();
            }
        }

        $penjualanWilayah->update(['deleted_by' => auth()->id()]);
        $penjualanWilayah->delete();

        $msg = $tipe === 'transfer' ? 'Transfer stok dibatalkan.' : 'Penjualan wilayah dibatalkan.';
        return redirect()->route('transaksi.penjualan-wilayah.index')->with('success', $msg);
    }
}
