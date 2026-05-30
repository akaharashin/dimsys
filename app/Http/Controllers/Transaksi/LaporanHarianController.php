<?php
namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Traits\LogsActivity;
use App\Traits\ChecksWilayahAccess;
use App\Models\LaporanHarian;
use App\Models\LaporanHarianDetail;
use App\Models\Outlet;
use App\Models\Distribusi;
use App\Models\Produk;
use Illuminate\Http\Request;

class LaporanHarianController extends Controller
{
    use LogsActivity, ChecksWilayahAccess;
    public function index(Request $request)
    {
        $wilayahList = \App\Models\Wilayah::where('aktif', true)->orderBy('nama')->get();
        $outletList = \App\Models\Outlet::where('aktif', true)->orderBy('nama')->get();

        $sort = in_array($request->sort, ['tanggal', 'outlet_id', 'total_setor', 'created_at']) ? $request->sort : 'tanggal';
        $dir  = $request->direction === 'asc' ? 'asc' : 'desc';

        $query = LaporanHarian::with(['outlet.wilayah', 'details'])
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
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;
        $laporan = $query->paginate($perPage)->withQueryString();

        return view('transaksi.laporan-harian.index', compact(
            'laporan',
            'wilayahList',
            'outletList'
        ));
    }

    public function create()
    {
        $produk = Produk::where('aktif', true)->orderBy('nama')->get();

        $outletQuery = Outlet::where('aktif', true)->orderBy('nama');

        if (auth()->user()->hasRole('koordinator')) {
            $outletQuery->where('wilayah_id', auth()->user()->wilayah_id);
        }

        $outlet = $outletQuery->get();
        return view('transaksi.laporan-harian.create', compact('produk', 'outlet'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|exists:outlet,id',
            'tanggal' => 'required|date|date_equals:today',
            'pengeluaran_ket.*' => 'nullable|string|max:255',
            'pengeluaran_jml.*' => 'nullable|numeric|min:0',
        ], [
            'outlet_id.required' => 'Outlet wajib dipilih.',
            'outlet_id.exists' => 'Outlet yang dipilih tidak valid.',
            'tanggal.required' => 'Tanggal laporan wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.date_equals' => 'Tanggal transaksi harus hari ini.',
            'pengeluaran_ket.*.max' => 'Keterangan pengeluaran maksimal 255 karakter.',
            'pengeluaran_jml.*.numeric' => 'Jumlah pengeluaran harus berupa angka.',
            'pengeluaran_jml.*.min' => 'Jumlah pengeluaran tidak boleh bernilai negatif.',
        ]);

        // Cek laporan sudah ada belum (whereDate andal, tidak tergantung format jam)
        $existing = LaporanHarian::where('outlet_id', $request->outlet_id)
            ->whereDate('tanggal', $request->tanggal)
            ->exists();

        if ($existing) {
            return back()->withErrors(['laporan' => 'Laporan untuk outlet dan tanggal ini sudah ada. Silakan pilih outlet atau tanggal yang berbeda.'])->withInput();
        }

        // Idempotency guard: cegah double-submit dalam 30 detik terakhir
        // (mis. klik ganda saat koneksi lambat) → laporan duplikat menggandakan omset.
        $duplikat = LaporanHarian::where('outlet_id', $request->outlet_id)
            ->whereDate('tanggal', $request->tanggal)
            ->where('created_by', auth()->id())
            ->where('created_at', '>=', now()->subSeconds(30))
            ->exists();

        if ($duplikat) {
            return back()->withErrors(['laporan' => 'Laporan untuk outlet & tanggal ini baru saja disimpan. Cek daftar laporan sebelum menyimpan ulang.'])->withInput();
        }

        // Bangun stok per produk: sisa kemarin + distribusi hari ini
        $kemarin = \Carbon\Carbon::parse($request->tanggal)->subDay()->format('Y-m-d');

        $distribusi = Distribusi::with('details.produk')
            ->where('outlet_id', $request->outlet_id)
            ->where('tanggal', $request->tanggal)
            ->first();

        $laporanKemarin = LaporanHarian::with('details.produk')
            ->where('outlet_id', $request->outlet_id)
            ->where('tanggal', $kemarin)
            ->first();

        // produkStok[produk_id] = ['produk' => Produk, 'jumlah_out' => int]
        $produkStok = [];

        if ($laporanKemarin) {
            foreach ($laporanKemarin->details as $d) {
                if ($d->sisa <= 0) continue;
                $produkStok[$d->produk_id] = [
                    'produk'     => $d->produk,
                    'jumlah_out' => (int) $d->sisa,
                ];
            }
        }

        if ($distribusi) {
            foreach ($distribusi->details as $d) {
                if (isset($produkStok[$d->produk_id])) {
                    $produkStok[$d->produk_id]['jumlah_out'] += (int) $d->jumlah_out;
                } else {
                    $produkStok[$d->produk_id] = [
                        'produk'     => $d->produk,
                        'jumlah_out' => (int) $d->jumlah_out,
                    ];
                }
            }
        }

        if (empty($produkStok)) {
            return back()->with('error', 'Tidak ada distribusi hari ini maupun sisa stok dari laporan kemarin. Laporan tidak dapat disimpan.')->withInput();
        }

        // Hitung total pengeluaran dari detail
        $totalPengeluaran = 0;
        $pengeluaranItems = [];
        if ($request->pengeluaran_ket) {
            foreach ($request->pengeluaran_ket as $i => $ket) {
                $jml = $request->pengeluaran_jml[$i] ?? 0;
                if (!empty($ket) && $jml > 0) {
                    $totalPengeluaran += $jml;
                    $pengeluaranItems[] = ['keterangan' => $ket, 'jumlah' => $jml];
                }
            }
        }

        // Validasi sisa & hitung omset/komisi
        $totalOmset = 0;
        $totalKomisi = 0;
        $adaTerjual = false;

        foreach ($produkStok as $produkId => $row) {
            $sisa = (int) $request->input('sisa_' . $produkId, 0);
            if ($sisa < 0) {
                return back()->withErrors(['laporan' => 'Sisa ' . $row['produk']->nama . ' tidak boleh negatif.'])->withInput();
            }
            if ($sisa > $row['jumlah_out']) {
                return back()->withErrors(['laporan' => 'Sisa ' . $row['produk']->nama . ' (' . $sisa . ' pcs) tidak boleh lebih dari stok tersedia (' . $row['jumlah_out'] . ' pcs).'])->withInput();
            }
            $terjual = max(0, $row['jumlah_out'] - $sisa);
            if ($terjual > 0) $adaTerjual = true;
            $totalOmset  += $terjual * $row['produk']->harga_mitra;
            $totalKomisi += $terjual * $row['produk']->komisi;
        }

        if (!$adaTerjual) {
            return back()->with('error', 'Tidak ada produk yang terjual. Laporan tidak dapat disimpan.')->withInput();
        }

        // A-S3: jika pengeluaran > (omset - komisi), selisihnya TIDAK hilang.
        // total_setor tetap >= 0 (kas tidak terganggu); kekurangan dicatat di 'talangan'
        // (uang yang harus ditalangi perusahaan) agar bisa direkonsiliasi.
        $totalSetorRaw = $totalOmset - $totalKomisi - $totalPengeluaran;
        $totalSetor    = max(0, $totalSetorRaw);
        $talangan      = max(0, -$totalSetorRaw);

        try {
            $laporan = LaporanHarian::create([
                'outlet_id' => $request->outlet_id,
                'tanggal' => $request->tanggal,
                'total_setor' => $totalSetor,
                'total_pengeluaran' => $totalPengeluaran,
                'talangan' => $talangan,
                'status' => 'final',
                'created_by' => auth()->id(),
            ]);

            foreach ($produkStok as $produkId => $row) {
                $sisa = (int) $request->input('sisa_' . $produkId, 0);
                $terjual = max(0, $row['jumlah_out'] - $sisa);
                LaporanHarianDetail::create([
                    'laporan_id' => $laporan->id,
                    'produk_id'  => $produkId,
                    'sisa'       => $sisa,
                    'terjual'    => $terjual,
                    'omset'      => $terjual * $row['produk']->harga_mitra,
                    'modal'      => $terjual * $row['produk']->hpp,
                    'komisi'     => $terjual * $row['produk']->komisi,
                ]);
            }

            // Simpan detail pengeluaran
            foreach ($pengeluaranItems as $item) {
                \App\Models\LaporanPengeluaran::create([
                    'laporan_id' => $laporan->id,
                    'keterangan' => $item['keterangan'],
                    'jumlah' => $item['jumlah'],
                ]);
            }

            $this->logActivity(
                'create', 'Laporan Harian', $laporan,
                after: $laporan->only(['id', 'outlet_id', 'tanggal', 'total_setor', 'total_pengeluaran', 'status']),
                label: 'Laporan Harian ' . optional($laporan->outlet)->nama . ' - ' . $laporan->tanggal
            );

            return redirect()->route('transaksi.laporan-harian.index')
                ->with('success', 'Laporan harian berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan laporan harian. Silakan coba lagi.')->withInput();
        }
    }
    public function show(LaporanHarian $laporanHarian)
    {
        $laporanHarian->load(['outlet.wilayah', 'details.produk']);
        $this->otorisasiWilayah(optional($laporanHarian->outlet)->wilayah_id);
        return view('transaksi.laporan-harian.show', compact('laporanHarian'));
    }

    public function destroy(LaporanHarian $laporanHarian)
    {
        $this->otorisasiWilayah(optional($laporanHarian->outlet)->wilayah_id);

        $this->logActivity(
            'delete', 'Laporan Harian', $laporanHarian,
            before: $laporanHarian->only(['id', 'outlet_id', 'tanggal', 'total_setor', 'status']),
            label: 'Laporan Harian ' . optional($laporanHarian->outlet)->nama . ' - ' . $laporanHarian->tanggal
        );
        $laporanHarian->update(['deleted_by' => auth()->id()]);
        $laporanHarian->delete();
        return redirect()->route('transaksi.laporan-harian.index')->with('success', 'Laporan berhasil dibatalkan.');
    }

    public function export(Request $request)
    {
        // Filter sama seperti index
        $query = LaporanHarian::with(['outlet.wilayah', 'details'])->orderByDesc('tanggal');

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

        $data = $query->get();
        $filename = 'laporan-harian-' . now()->format('Y-m-d') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\Transaksi\LaporanHarianExport($data),
            $filename
        );
    }
}