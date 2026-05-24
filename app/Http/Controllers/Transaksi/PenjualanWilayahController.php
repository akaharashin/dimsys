<?php
namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Models\PenjualanWilayah;
use App\Models\PenjualanWilayahDetail;
use App\Models\Wilayah;
use App\Models\Produk;
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

        // Summary
        $allQuery = PenjualanWilayah::query();
        if ($request->filled('wilayah_asal_id'))
            $allQuery->where('wilayah_asal_id', $request->wilayah_asal_id);
        if ($request->filled('status_bayar'))
            $allQuery->where('status_bayar', $request->status_bayar);
        if ($request->filled('dari'))
            $allQuery->whereDate('tanggal', '>=', $request->dari);
        if ($request->filled('sampai'))
            $allQuery->whereDate('tanggal', '<=', $request->sampai);

        $totalNilai = $allQuery->sum('total');
        $totalLunas = (clone $allQuery)->where('status_bayar', 'lunas')->sum('total');
        $totalBelum = (clone $allQuery)->where('status_bayar', 'belum_lunas')->sum('total');

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
        $filename = 'penjualan-wilayah-' . now()->format('Y-m-d') . '.xlsx';
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
        $request->validate([
            'wilayah_asal_id' => 'required|exists:wilayah,id',
            'wilayah_tujuan_id' => 'required|exists:wilayah,id|different:wilayah_asal_id',
            'tanggal' => 'required|date',
            'status_bayar' => 'required|in:lunas,belum_lunas,sebagian',
            'keterangan' => 'nullable|string|max:255',
            'produk_id' => 'required|array|min:1',
            'jumlah.*' => 'nullable|integer|min:0',
        ], [
            'wilayah_asal_id.required' => 'Wilayah asal wajib dipilih.',
            'wilayah_asal_id.exists' => 'Wilayah asal yang dipilih tidak valid.',
            'wilayah_tujuan_id.required' => 'Wilayah tujuan wajib dipilih.',
            'wilayah_tujuan_id.exists' => 'Wilayah tujuan yang dipilih tidak valid.',
            'wilayah_tujuan_id.different' => 'Wilayah tujuan tidak boleh sama dengan wilayah asal.',
            'tanggal.required' => 'Tanggal penjualan wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'status_bayar.required' => 'Status bayar wajib dipilih.',
            'status_bayar.in' => 'Status bayar harus berupa lunas, belum lunas, atau sebagian.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
            'produk_id.required' => 'Minimal satu produk wajib dipilih.',
            'produk_id.min' => 'Minimal satu produk wajib dipilih.',
            'jumlah.*.integer' => 'Jumlah produk harus berupa bilangan bulat.',
            'jumlah.*.min' => 'Jumlah produk tidak boleh bernilai negatif.',
        ]);

        try {
            $total = 0;
            foreach ($request->produk_id as $i => $pid) {
                $jumlah = $request->jumlah[$i] ?? 0;
                if ($jumlah > 0) {
                    $produk = Produk::find($pid);
                    $total += $jumlah * $produk->harga_agen;
                }
            }

            $penjualan = PenjualanWilayah::create([
                'wilayah_asal_id' => $request->wilayah_asal_id,
                'wilayah_tujuan_id' => $request->wilayah_tujuan_id,
                'tanggal' => $request->tanggal,
                'total' => $total,
                'status_bayar' => $request->status_bayar,
                'keterangan' => $request->keterangan,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->produk_id as $i => $pid) {
                $jumlah = $request->jumlah[$i] ?? 0;
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
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencatat penjualan wilayah. Silakan coba lagi.')->withInput();
        }
    }

    public function show(PenjualanWilayah $penjualanWilayah)
    {
        $penjualanWilayah->load(['wilayahAsal', 'wilayahTujuan', 'details.produk']);
        return view('transaksi.penjualan-wilayah.show', compact('penjualanWilayah'));
    }

    public function update(Request $request, PenjualanWilayah $penjualanWilayah)
    {
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
        $penjualanWilayah->update(['deleted_by' => auth()->id()]);
        $penjualanWilayah->delete();
        return redirect()->route('transaksi.penjualan-wilayah.index')
            ->with('success', 'Penjualan wilayah dibatalkan.');
    }
}