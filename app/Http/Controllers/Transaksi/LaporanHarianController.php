<?php
namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Traits\LogsActivity;
use App\Models\LaporanHarian;
use App\Models\LaporanHarianDetail;
use App\Models\Outlet;
use App\Models\Distribusi;
use App\Models\Produk;
use Illuminate\Http\Request;

class LaporanHarianController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        $wilayahList = \App\Models\Wilayah::where('aktif', true)->orderBy('nama')->get();
        $outletList = \App\Models\Outlet::where('aktif', true)->orderBy('nama')->get();

        $query = LaporanHarian::with(['outlet.wilayah', 'details'])
            ->orderByDesc('tanggal')->orderByDesc('created_at');

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

        // Cek laporan sudah ada belum
        $existing = LaporanHarian::where('outlet_id', $request->outlet_id)
            ->where('tanggal', $request->tanggal)
            ->first();

        if ($existing) {
            return back()->withErrors(['laporan' => 'Laporan untuk outlet dan tanggal ini sudah ada. Silakan pilih outlet atau tanggal yang berbeda.'])->withInput();
        }

        // Ambil distribusi hari itu
        $distribusi = Distribusi::with('details.produk')
            ->where('outlet_id', $request->outlet_id)
            ->where('tanggal', $request->tanggal)
            ->first();
        // Sesudah - BENAR
        if (!$distribusi) {
            return back()->with('error', 'Tidak ada data distribusi untuk outlet dan tanggal ini. Pastikan distribusi sudah diinput terlebih dahulu.')->withInput();
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

        // Hitung omset & komisi dari sisa
        $totalOmset = 0;
        $totalKomisi = 0;
        if ($distribusi) {
            foreach ($distribusi->details as $d) {
                $sisa = $request->input('sisa_' . $d->produk_id, 0);
                $terjual = max(0, $d->jumlah_out - $sisa);
                $totalOmset += $terjual * $d->produk->harga_mitra;
                $totalKomisi += $terjual * $d->produk->komisi;
            }
        }

        $totalSetor = $totalOmset - $totalKomisi - $totalPengeluaran;
        // Cek apakah ada produk yang terjual dari distribusi
        $adaTerjual = false;
        foreach ($distribusi->details as $d) {
            $sisa = $request->input('sisa_' . $d->produk_id, 0);
            $terjual = max(0, $d->jumlah_out - $sisa);
            if ($terjual > 0) {
                $adaTerjual = true;
                break;
            }
        }

        if (!$adaTerjual) {
            return back()->with('error', 'Tidak ada produk yang terjual. Laporan tidak dapat disimpan.')->withInput();
        }
        try {
            $laporan = LaporanHarian::create([
                'outlet_id' => $request->outlet_id,
                'tanggal' => $request->tanggal,
                'total_setor' => max(0, $totalSetor),
                'total_pengeluaran' => $totalPengeluaran,
                'status' => 'final',
                'created_by' => auth()->id(),
            ]);

            // Simpan detail produk
            if ($distribusi) {
                foreach ($distribusi->details as $d) {
                    $sisa = $request->input('sisa_' . $d->produk_id, 0);
                    $terjual = max(0, $d->jumlah_out - $sisa);
                    LaporanHarianDetail::create([
                        'laporan_id' => $laporan->id,
                        'produk_id' => $d->produk_id,
                        'sisa' => $sisa,
                        'terjual' => $terjual,
                        'omset' => $terjual * $d->produk->harga_mitra,
                        'modal' => $terjual * $d->produk->hpp,
                        'komisi' => $terjual * $d->produk->komisi,
                    ]);
                }
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
        return view('transaksi.laporan-harian.show', compact('laporanHarian'));
    }

    public function destroy(LaporanHarian $laporanHarian)
    {
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