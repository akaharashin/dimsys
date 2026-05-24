<?php
namespace App\Http\Controllers\Stok;

use App\Exports\Stok\StokMasukExport;
use App\Http\Controllers\Controller;
use App\Models\StokMasuk;
use App\Models\StokMasukDetail;
use App\Models\Wilayah;
use App\Models\Supplier;
use App\Models\Produk;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StokMasukController extends Controller
{
    public function index(Request $request)
    {
        $wilayahList = Wilayah::where('aktif', true)->orderBy('nama')->get();
        $supplierList = Supplier::where('aktif', true)->orderBy('nama')->get();

        $query = StokMasuk::with(['wilayah', 'supplier', 'details.produk'])
            ->orderByDesc('tanggal');

        if (auth()->user()->hasRole('koordinator')) {
            $query->where('wilayah_id', auth()->user()->wilayah_id);
        }
        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }

        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;
        $stokMasuk = $query->paginate($perPage)->withQueryString();

        return view('stok.masuk.index', compact(
            'stokMasuk',
            'wilayahList',
            'supplierList'
        ));
    }

    public function export(Request $request)
    {
        $filters = $request->only(['wilayah_id', 'supplier_id', 'jenis', 'dari', 'sampai']);

        // Koordinator hanya bisa export wilayahnya sendiri
        if (auth()->user()->hasRole('koordinator')) {
            $filters['wilayah_id'] = auth()->user()->wilayah_id;
        }

        $filename = 'stok-masuk-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new StokMasukExport($filters), $filename);
    }
    public function create()
    {
        $wilayah = auth()->user()->hasRole('koordinator')
            ? \App\Models\Wilayah::where('id', auth()->user()->wilayah_id)->get()
            : \App\Models\Wilayah::where('aktif', true)->orderBy('nama')->get();

        $supplier = \App\Models\Supplier::where('aktif', true)->orderBy('nama')->get();
        $produk = \App\Models\Produk::where('aktif', true)->orderBy('nama')->get();

        return view('stok.masuk.create', compact('wilayah', 'supplier', 'produk'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'wilayah_id' => 'required|exists:wilayah,id',
            'supplier_id' => 'required|exists:supplier,id',
            'tanggal' => 'required|date',
            'jenis' => 'required|in:awal,masuk',
            'keterangan' => 'nullable|string|max:255',
            'produk_id' => 'required|array|min:1',
            'jumlah.*' => 'nullable|integer|min:0',
        ], [
            'wilayah_id.required' => 'Wilayah wajib dipilih.',
            'wilayah_id.exists' => 'Wilayah yang dipilih tidak valid.',
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists' => 'Supplier yang dipilih tidak valid.',
            'tanggal.required' => 'Tanggal stok masuk wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'jenis.required' => 'Jenis stok wajib dipilih.',
            'jenis.in' => 'Jenis stok harus berupa stok awal atau stok masuk.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
            'produk_id.required' => 'Minimal satu produk wajib dipilih.',
            'produk_id.min' => 'Minimal satu produk wajib dipilih.',
            'jumlah.*.integer' => 'Jumlah produk harus berupa bilangan bulat.',
            'jumlah.*.min' => 'Jumlah produk tidak boleh bernilai negatif.',
        ]);

        // Cek apakah ada minimal 1 produk dengan jumlah > 0
        $adaProduk = false;
        foreach ($request->produk_id as $i => $pid) {
            if (($request->jumlah[$i] ?? 0) > 0) {
                $adaProduk = true;
                break;
            }
        }

        if (!$adaProduk) {
            return back()->with('error', 'Minimal satu produk harus memiliki jumlah lebih dari 0.')->withInput();
        }

        try {
            $stokMasuk = StokMasuk::create([
                'wilayah_id' => $request->wilayah_id,
                'supplier_id' => $request->supplier_id,
                'tanggal' => $request->tanggal,
                'jenis' => $request->jenis,
                'keterangan' => $request->keterangan,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->produk_id as $i => $pid) {
                $jumlah = $request->jumlah[$i] ?? 0;
                if ($jumlah > 0) {
                    $produk = \App\Models\Produk::find($pid);
                    StokMasukDetail::create([
                        'stok_masuk_id' => $stokMasuk->id,
                        'produk_id' => $pid,
                        'jumlah' => $jumlah,
                        'hpp' => $produk->hpp,
                    ]);
                }
            }

            return redirect()->route('stok.masuk.index')
                ->with('success', 'Stok masuk berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencatat stok masuk. Silakan coba lagi.')->withInput();
        }
    }
    public function show(StokMasuk $masuk)
    {
        $masuk->load(['wilayah', 'supplier', 'details.produk']);
        return view('stok.masuk.show', compact('masuk'));
    }

    public function destroy(StokMasuk $masuk)
    {
        $masuk->update(['deleted_by' => auth()->id()]);
        $masuk->delete();
        return redirect()->route('stok.masuk.index')->with('success', 'Stok masuk berhasil dibatalkan.');
    }
}