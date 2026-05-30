<?php
namespace App\Http\Controllers\Stok;

use App\Exports\Stok\DistribusiExport;
use App\Http\Controllers\Controller;
use App\Models\Distribusi;
use App\Models\DistribusiDetail;
use App\Models\Outlet;
use App\Models\Produk;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use App\Traits\LogsActivity;
use App\Traits\ChecksWilayahAccess;
use Maatwebsite\Excel\Facades\Excel;

class DistribusiController extends Controller
{
    use LogsActivity, ChecksWilayahAccess;
    public function index(Request $request)
    {
        $wilayahList = Wilayah::where('aktif', true)->orderBy('nama')->get();
        $outletList = Outlet::where('aktif', true)->orderBy('nama')->get();

        $sort = in_array($request->sort, ['tanggal', 'outlet_id', 'created_at']) ? $request->sort : 'tanggal';
        $dir  = $request->direction === 'asc' ? 'asc' : 'desc';

        $query = Distribusi::with(['outlet.wilayah', 'details.produk'])
            ->orderBy($sort, $dir);
        if ($sort === 'tanggal') {
            $query->orderBy('created_at', $dir);
        }

        if (auth()->user()->hasRole('koordinator')) {
            $query->whereHas(
                'outlet',
                fn($q) =>
                $q->where('wilayah_id', auth()->user()->wilayah_id)
            );
        }
        if ($request->filled('wilayah_id')) {
            $query->whereHas(
                'outlet',
                fn($q) =>
                $q->where('wilayah_id', $request->wilayah_id)
            );
        }
        if ($request->filled('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }

        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;
        $distribusi = $query->paginate($perPage)->withQueryString();

        return view('stok.distribusi.index', compact(
            'distribusi',
            'wilayahList',
            'outletList'
        ));
    }
    public function export(Request $request)
    {
        $filters = $request->only(['wilayah_id', 'outlet_id', 'dari', 'sampai']);

        if (auth()->user()->hasRole('koordinator')) {
            $filters['wilayah_id'] = auth()->user()->wilayah_id;
        }

        $filename = 'distribusi-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new DistribusiExport($filters), $filename);
    }


    public function create()
    {
        $produk = Produk::where('aktif', true)->orderBy('nama')->get();

        $outletQuery = Outlet::where('aktif', true)->orderBy('nama');

        if (auth()->user()->hasRole('koordinator')) {
            $outletQuery->where('wilayah_id', auth()->user()->wilayah_id);
        }

        $outlet = $outletQuery->get();
        return view('stok.distribusi.create', compact('produk', 'outlet'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'outlet_id'    => 'required|exists:outlet,id',
            'tanggal'      => 'required|date|date_equals:today',
            'keterangan'   => 'nullable|string|max:255',
            'jumlah_out'   => 'required|array',
            'jumlah_out.*' => 'integer|min:0',
        ], [
            'outlet_id.required'   => 'Outlet wajib dipilih.',
            'outlet_id.exists'     => 'Outlet yang dipilih tidak valid.',
            'tanggal.required'     => 'Tanggal distribusi wajib diisi.',
            'tanggal.date'         => 'Format tanggal tidak valid.',
            'tanggal.date_equals'  => 'Tanggal transaksi harus hari ini.',
            'keterangan.max'       => 'Keterangan maksimal 255 karakter.',
            'jumlah_out.required'  => 'Data produk wajib diisi. Pilih outlet terlebih dahulu.',
            'jumlah_out.array'     => 'Format data produk tidak valid.',
            'jumlah_out.*.integer' => 'Jumlah produk harus berupa bilangan bulat.',
            'jumlah_out.*.min'     => 'Jumlah produk tidak boleh bernilai negatif.',
        ]);

        // Ambil wilayah dari outlet
        $outlet = \App\Models\Outlet::find($request->outlet_id);
        $wilayahId = $outlet->wilayah_id;

        // Validasi stok per produk
        $stokErrors = [];
        $adaProduk = false;
        foreach ($request->jumlah_out as $pid => $jumlah) {
            $jumlah = (int) $jumlah;
            if ($jumlah <= 0) continue;
            $adaProduk = true;

            $produk = \App\Models\Produk::find($pid);
            if (!$produk) {
                $stokErrors[] = "Produk tidak ditemukan (ID: {$pid}).";
                continue;
            }

            $masuk = \App\Models\StokMasukDetail::whereHas(
                'stokMasuk',
                fn($q) => $q->where('wilayah_id', $wilayahId)
            )->where('produk_id', $pid)->sum('jumlah');

            $sudahOut = \App\Models\DistribusiDetail::whereHas(
                'distribusi',
                fn($q) => $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $wilayahId))
            )->where('produk_id', $pid)->sum('jumlah_out');

            $keluarWilayah = \App\Models\PenjualanWilayahDetail::whereHas(
                'penjualan',
                fn($q) => $q->where('wilayah_asal_id', $wilayahId)->where('status', 'disetujui')
            )->where('produk_id', $pid)->sum('jumlah');

            $stokTersedia = $masuk - $sudahOut - $keluarWilayah;

            if ($jumlah > $stokTersedia) {
                $stokErrors[] = "Stok {$produk->nama} tidak cukup. Tersedia: {$stokTersedia} pcs, diminta: {$jumlah} pcs.";
            }
        }

        if (!empty($stokErrors)) {
            return back()->withErrors(['stok' => implode(' | ', $stokErrors)])->withInput();
        }

        if (!$adaProduk) {
            return back()->with('error', 'Minimal satu produk harus memiliki jumlah OUT lebih dari 0.')->withInput();
        }

        try {
            $distribusi = Distribusi::create([
                'outlet_id'  => $request->outlet_id,
                'tanggal'    => $request->tanggal,
                'keterangan' => $request->keterangan,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->jumlah_out as $pid => $jumlah) {
                $jumlah = (int) $jumlah;
                if ($jumlah <= 0) continue;
                DistribusiDetail::create([
                    'distribusi_id' => $distribusi->id,
                    'produk_id'     => $pid,
                    'jumlah_out'    => $jumlah,
                ]);
            }

            $this->logActivity(
                'create', 'Distribusi', $distribusi,
                after: $distribusi->only(['id', 'outlet_id', 'tanggal', 'keterangan']),
                label: 'Distribusi ' . optional($distribusi->outlet)->nama . ' - ' . $distribusi->tanggal
            );

            return redirect()->route('stok.distribusi.index')->with('success', 'Distribusi berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencatat distribusi. Silakan coba lagi.')->withInput();
        }
    }

    public function show(Distribusi $distribusi)
    {
        $distribusi->load(['outlet.wilayah', 'details.produk']);
        $this->otorisasiWilayah(optional($distribusi->outlet)->wilayah_id);
        return view('stok.distribusi.show', compact('distribusi'));
    }

    public function destroy(Distribusi $distribusi)
    {
        $this->otorisasiWilayah(optional($distribusi->outlet)->wilayah_id);

        $this->logActivity(
            'delete', 'Distribusi', $distribusi,
            before: $distribusi->only(['id', 'outlet_id', 'tanggal', 'keterangan']),
            label: 'Distribusi ' . optional($distribusi->outlet)->nama . ' - ' . $distribusi->tanggal
        );
        $distribusi->update(['deleted_by' => auth()->id()]);
        $distribusi->delete();
        return redirect()->route('stok.distribusi.index')->with('success', 'Distribusi berhasil dibatalkan.');
    }
}