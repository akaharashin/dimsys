<?php
namespace App\Http\Controllers\Stok;

use App\Exports\Stok\StokMasukExport;
use App\Http\Controllers\Controller;
use App\Models\StokMasuk;
use App\Models\StokMasukDetail;
use App\Models\DistribusiDetail;
use App\Models\PenjualanWilayahDetail;
use App\Models\Wilayah;
use App\Models\Supplier;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\LogsActivity;
use Maatwebsite\Excel\Facades\Excel;

class StokMasukController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        $wilayahList = Wilayah::where('aktif', true)->orderBy('nama')->get();
        $supplierList = Supplier::where('aktif', true)->orderBy('nama')->get();

        $query = StokMasuk::with(['wilayah', 'supplier', 'details.produk'])
            ->orderByDesc('tanggal')->orderByDesc('created_at');

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

        if (auth()->user()->hasRole('koordinator')) {
            $filters['wilayah_id'] = auth()->user()->wilayah_id;
        }

        $filename = 'stok-masuk-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new StokMasukExport($filters), $filename);
    }

    public function create()
    {
        $wilayah = auth()->user()->hasRole('koordinator')
            ? Wilayah::where('id', auth()->user()->wilayah_id)->get()
            : Wilayah::where('aktif', true)->orderBy('nama')->get();

        $supplier = Supplier::where('aktif', true)->orderBy('nama')->get();
        $produk = Produk::where('aktif', true)->orderBy('nama')->get();

        return view('stok.masuk.create', compact('wilayah', 'supplier', 'produk'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'wilayah_id' => 'required|exists:wilayah,id',
            'supplier_id' => 'required|exists:supplier,id',
            'tanggal' => 'required|date|date_equals:today',
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
            'tanggal.date_equals' => 'Tanggal transaksi harus hari ini.',
            'jenis.required' => 'Jenis stok wajib dipilih.',
            'jenis.in' => 'Jenis stok harus berupa stok awal atau stok masuk.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
            'produk_id.required' => 'Minimal satu produk wajib dipilih.',
            'produk_id.min' => 'Minimal satu produk wajib dipilih.',
            'jumlah.*.integer' => 'Jumlah produk harus berupa bilangan bulat.',
            'jumlah.*.min' => 'Jumlah produk tidak boleh bernilai negatif.',
        ]);

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
                    $produk = Produk::find($pid);
                    StokMasukDetail::create([
                        'stok_masuk_id' => $stokMasuk->id,
                        'produk_id' => $pid,
                        'jumlah' => $jumlah,
                        'hpp' => $produk->hpp,
                    ]);
                }
            }

            $this->logActivity(
                'create', 'Stok Masuk', $stokMasuk,
                after: $stokMasuk->only(['id', 'wilayah_id', 'supplier_id', 'tanggal', 'jenis', 'keterangan']),
                label: 'Stok Masuk ' . optional($stokMasuk->wilayah)->nama . ' - ' . $stokMasuk->tanggal
            );

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
        $this->logActivity(
            'delete', 'Stok Masuk', $masuk,
            before: $masuk->only(['id', 'wilayah_id', 'supplier_id', 'tanggal', 'jenis', 'keterangan']),
            label: 'Stok Masuk ' . optional($masuk->wilayah)->nama . ' - ' . $masuk->tanggal
        );
        $masuk->update(['deleted_by' => auth()->id()]);
        $masuk->delete();
        return redirect()->route('stok.masuk.index')->with('success', 'Stok masuk berhasil dibatalkan.');
    }

    // ─── Generate Stok Awal ───────────────────────────────────────────────────

    public function generateAwalForm(Request $request)
    {
        if (auth()->user()->hasRole('koordinator')) {
            $wilayahList = Wilayah::where('id', auth()->user()->wilayah_id)->get();
        } else {
            $wilayahList = Wilayah::where('aktif', true)->orderBy('nama')->get();
        }

        $defaultBulan = now()->subMonth()->format('Y-m');
        return view('stok.generate-awal', compact('wilayahList', 'defaultBulan'));
    }

    public function generateAwalPreview(Request $request)
    {
        $wilayahId = $request->wilayah_id;
        $bulan     = $request->bulan;

        if (!$wilayahId || !$bulan || !preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            return response()->json(['error' => 'Parameter tidak lengkap.'], 422);
        }

        if (auth()->user()->hasRole('koordinator')) {
            $wilayahId = auth()->user()->wilayah_id;
        }

        $wilayah = Wilayah::find($wilayahId);
        if (!$wilayah) {
            return response()->json(['error' => 'Wilayah tidak ditemukan.'], 404);
        }

        [$tahun, $bln] = explode('-', $bulan);
        $nextMonth = Carbon::parse($bulan . '-01')->addMonth();

        $alreadyExists = StokMasuk::where('jenis', 'awal')
            ->where('wilayah_id', $wilayahId)
            ->whereYear('tanggal', $nextMonth->year)
            ->whereMonth('tanggal', $nextMonth->month)
            ->exists();

        $produkList = Produk::where('aktif', true)->orderBy('nama')->get();
        $data = $this->hitungStokAkhirBulan($wilayahId, $produkList, (int) $tahun, (int) $bln);

        $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return response()->json([
            'wilayah_id'          => $wilayahId,
            'bulan'               => $bulan,
            'bulan_label'         => $bulanIndo[(int) $bln] . ' ' . $tahun,
            'bulan_tujuan_label'  => $bulanIndo[$nextMonth->month] . ' ' . $nextMonth->year,
            'tanggal_tujuan'      => $nextMonth->format('Y-m-01'),
            'already_exists'      => $alreadyExists,
            'wilayah_nama'        => $wilayah->nama,
            'data'                => $data,
        ]);
    }

    public function generateAwal(Request $request)
    {
        $request->validate([
            'wilayah_id' => 'required|exists:wilayah,id',
            'bulan'      => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ], [
            'wilayah_id.required' => 'Wilayah wajib dipilih.',
            'bulan.required'      => 'Bulan wajib dipilih.',
            'bulan.regex'         => 'Format bulan tidak valid.',
        ]);

        $wilayahId = $request->wilayah_id;
        if (auth()->user()->hasRole('koordinator')) {
            $wilayahId = auth()->user()->wilayah_id;
        }

        $bulan = $request->bulan;
        [$tahun, $bln] = explode('-', $bulan);
        $nextMonth = Carbon::parse($bulan . '-01')->addMonth();

        $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $nextBulanLabel = $bulanIndo[$nextMonth->month] . ' ' . $nextMonth->year;

        // Cek apakah stok awal bulan tujuan sudah ada
        $alreadyExists = StokMasuk::where('jenis', 'awal')
            ->where('wilayah_id', $wilayahId)
            ->whereYear('tanggal', $nextMonth->year)
            ->whereMonth('tanggal', $nextMonth->month)
            ->exists();

        if ($alreadyExists) {
            return back()->with('error', "Stok awal {$nextBulanLabel} untuk wilayah ini sudah ada.");
        }

        $produkList = Produk::where('aktif', true)->orderBy('nama')->get();
        $stokData = $this->hitungStokAkhirBulan($wilayahId, $produkList, (int) $tahun, (int) $bln);

        if (empty($stokData)) {
            return back()->with('error', "Tidak ada produk dengan stok akhir > 0 pada {$bulanIndo[(int)$bln]} {$tahun}.");
        }

        try {
            $supplier = Supplier::where('aktif', true)->orderBy('created_at')->first();

            if (!$supplier) {
                $supplier = Supplier::create([
                    'nama'   => 'Internal',
                    'aktif'  => true,
                ]);
            }

            $wilayah = Wilayah::find($wilayahId);

            $stokMasuk = StokMasuk::create([
                'wilayah_id'  => $wilayahId,
                'supplier_id' => $supplier->id,
                'tanggal'     => $nextMonth->format('Y-m-01'),
                'jenis'       => 'awal',
                'keterangan'  => 'Stok Awal ' . $nextBulanLabel . ' - Auto Generate',
                'created_by'  => auth()->id(),
            ]);

            foreach ($stokData as $row) {
                StokMasukDetail::create([
                    'stok_masuk_id' => $stokMasuk->id,
                    'produk_id'     => $row['produk_id'],
                    'jumlah'        => $row['stok_akhir'],
                    'hpp'           => $row['hpp'],
                ]);
            }

            $jumlahProduk = count($stokData);

            return redirect()->route('stok.masuk.index', [
                'dari'       => $nextMonth->format('Y-m-01'),
                'sampai'     => $nextMonth->copy()->endOfMonth()->format('Y-m-d'),
                'jenis'      => 'awal',
                'wilayah_id' => $wilayahId,
            ])->with('success', "Stok awal {$nextBulanLabel} berhasil di-generate ({$jumlahProduk} produk).");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate stok awal. Silakan coba lagi.');
        }
    }

    private function hitungStokAkhirBulan(string $wilayahId, $produkList, int $tahun, int $bulan): array
    {
        return $produkList->map(function ($produk) use ($wilayahId, $tahun, $bulan) {

            $stokAwal = StokMasukDetail::whereHas('stokMasuk', function ($q) use ($tahun, $bulan, $wilayahId) {
                $q->whereYear('tanggal', $tahun)
                  ->whereMonth('tanggal', $bulan)
                  ->where('jenis', 'awal')
                  ->where('wilayah_id', $wilayahId);
            })->where('produk_id', $produk->id)->sum('jumlah');

            $masuk = StokMasukDetail::whereHas('stokMasuk', function ($q) use ($tahun, $bulan, $wilayahId) {
                $q->whereYear('tanggal', $tahun)
                  ->whereMonth('tanggal', $bulan)
                  ->where('jenis', 'masuk')
                  ->where('wilayah_id', $wilayahId);
            })->where('produk_id', $produk->id)->sum('jumlah');

            $out = DistribusiDetail::whereHas('distribusi', function ($q) use ($tahun, $bulan, $wilayahId) {
                $q->whereYear('tanggal', $tahun)
                  ->whereMonth('tanggal', $bulan)
                  ->whereHas('outlet', fn($o) => $o->where('wilayah_id', $wilayahId));
            })->where('produk_id', $produk->id)->sum('jumlah_out');

            $keluarWilayah = PenjualanWilayahDetail::whereHas('penjualan', function ($q) use ($tahun, $bulan, $wilayahId) {
                $q->whereYear('tanggal', $tahun)
                  ->whereMonth('tanggal', $bulan)
                  ->where('wilayah_asal_id', $wilayahId)
                  ->where('status', 'disetujui');
            })->where('produk_id', $produk->id)->sum('jumlah');

            $stokAkhir = ($stokAwal + $masuk) - $out - $keluarWilayah;

            return [
                'produk_id'   => $produk->id,
                'produk_nama' => $produk->nama,
                'stok_awal'   => (int) $stokAwal,
                'masuk'       => (int) $masuk,
                'out'         => (int) ($out + $keluarWilayah),
                'stok_akhir'  => (int) $stokAkhir,
                'hpp'         => $produk->hpp,
            ];
        })->filter(fn($r) => $r['stok_akhir'] > 0)->values()->toArray();
    }
}
