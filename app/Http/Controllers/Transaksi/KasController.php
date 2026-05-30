<?php
namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Traits\LogsActivity;
use App\Traits\ChecksWilayahAccess;
use App\Models\Kas;
use App\Models\Rekening;
use App\Models\Outlet;
use App\Models\LaporanHarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Transaksi\KasExport;

class KasController extends Controller
{
    use LogsActivity, ChecksWilayahAccess;
    public function index(Request $request)
    {
        $rekeningList = Rekening::where('aktif', true)->orderBy('nama')->get();

        // Koordinator hanya lihat rekening wilayahnya
        if (auth()->user()->hasRole('koordinator')) {
            $rekeningList = Rekening::where('aktif', true)
                ->where('wilayah_id', auth()->user()->wilayah_id)
                ->orderBy('nama')->get();
        }

        $selectedRekening = $request->input('rekening_id', $rekeningList->first()?->id);

        // Koordinator tidak boleh memilih rekening di luar wilayahnya (IDOR via ?rekening_id=...)
        if (auth()->user()->hasRole('koordinator') && $selectedRekening) {
            if (!$rekeningList->contains('id', $selectedRekening)) {
                $selectedRekening = $rekeningList->first()?->id;
            }
        }

        // B-K3: query dasar + filter. TIDAK lagi load semua baris ke memori.
        $base = Kas::where('rekening_id', $selectedRekening);

        if ($request->filled('kategori')) {
            $base->where('kategori', $request->kategori);
        }
        if ($request->filled('dari')) {
            $base->where('tanggal', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $base->where('tanggal', '<=', $request->sampai);
        }
        if ($request->filled('search')) {
            $base->where(function ($q) use ($request) {
                $q->where('keterangan', 'like', "%{$request->search}%")
                    ->orWhere('sub_kategori', 'like', "%{$request->search}%")
                    ->orWhere('penerima', 'like', "%{$request->search}%");
            });
        }

        $saldoAwal = (float) (optional(Rekening::find($selectedRekening))->saldo_awal ?? 0);

        // Summary via AGREGAT (1 query masing-masing) — tidak menarik semua baris.
        // (float) menyamai tipe lama (Collection::sum) agar number_format konsisten.
        $totalDebit  = (float) (clone $base)->where('tipe', 'debit')->sum('jumlah');
        $totalKredit = (float) (clone $base)->where('tipe', 'kredit')->sum('jumlah');
        $total       = (clone $base)->count();
        // saldoAkhir = saldo_awal + Σdebit − Σkredit (identik dgn baris terakhir lama).
        $saldoAkhir  = $total > 0 ? ($saldoAwal + $totalDebit - $totalKredit) : 0;

        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;
        $page = (int) $request->input('page', 1);
        if ($page < 1) $page = 1;

        // Saldo berjalan dihitung di DB (window function) — HANYA baris halaman ini
        // yang ditarik ke PHP. Urutan (tanggal, created_at, id) = identik perilaku lama.
        $signed = "CASE WHEN kas.tipe = 'debit' THEN kas.jumlah ELSE -kas.jumlah END";
        $items = (clone $base)
            ->with(['outlet', 'rekening'])
            ->select('kas.*')
            ->selectRaw(
                "(? + SUM($signed) OVER (ORDER BY kas.tanggal, kas.created_at, kas.id "
                . "ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW)) as saldo_berjalan",
                [$saldoAwal]
            )
            ->orderByDesc('tanggal')->orderByDesc('created_at')->orderByDesc('id')
            ->forPage($page, $perPage)
            ->get();

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('transaksi.kas.index', compact(
            'rekeningList',
            'selectedRekening',
            'paginated',
            'totalDebit',
            'totalKredit',
            'saldoAkhir'
        ));
    }

    public function create()
    {
        $rekeningQuery = Rekening::where('aktif', true)->orderBy('nama');
        if (auth()->user()->hasRole('koordinator')) {
            $rekeningQuery->where('wilayah_id', auth()->user()->wilayah_id);
        }
        $rekening = $rekeningQuery->get();

        $outletQuery = Outlet::where('aktif', true)->orderBy('nama');
        if (auth()->user()->hasRole('koordinator')) {
            $outletQuery->where('wilayah_id', auth()->user()->wilayah_id);
        }
        $outlet = $outletQuery->get();

        return view('transaksi.kas.create', compact('rekening', 'outlet'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rekening_id' => 'required|exists:rekening,id',
            'tanggal' => 'required|date|date_equals:today',
            'tipe' => 'required|in:debit,kredit',
            'kategori' => 'required|string|max:100',
            'sub_kategori' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string|max:255',
            'penerima' => 'nullable|string|max:100',
            'jumlah' => 'required|numeric|min:1',
            'outlet_id' => 'nullable|exists:outlet,id',
        ], [
            'rekening_id.required' => 'Rekening wajib dipilih.',
            'rekening_id.exists' => 'Rekening yang dipilih tidak valid.',
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.date_equals' => 'Tanggal transaksi harus hari ini.',
            'tipe.required' => 'Tipe transaksi wajib dipilih.',
            'tipe.in' => 'Tipe transaksi harus berupa debit atau kredit.',
            'kategori.required' => 'Kategori transaksi wajib diisi.',
            'kategori.max' => 'Kategori maksimal 100 karakter.',
            'sub_kategori.max' => 'Sub kategori maksimal 100 karakter.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
            'penerima.max' => 'Nama penerima maksimal 100 karakter.',
            'jumlah.required' => 'Jumlah transaksi wajib diisi.',
            'jumlah.numeric' => 'Jumlah transaksi harus berupa angka.',
            'jumlah.min' => 'Jumlah transaksi minimal Rp 1.',
            'outlet_id.exists' => 'Outlet yang dipilih tidak valid.',
        ]);

        try {
            $kas = Kas::create([
                'rekening_id' => $request->rekening_id,
                'outlet_id' => $request->outlet_id,
                'tanggal' => $request->tanggal,
                'tipe' => $request->tipe,
                'kategori' => $request->kategori,
                'sub_kategori' => $request->sub_kategori,
                'keterangan' => $request->keterangan,
                'penerima' => $request->penerima,
                'jumlah' => $request->jumlah,
                'saldo' => 0,
                'created_by' => auth()->id(),
            ]);

            $this->logActivity(
                'create', 'Kas Harian', $kas,
                after: $kas->only(['id', 'rekening_id', 'tipe', 'kategori', 'jumlah', 'tanggal']),
                label: 'Kas ' . ucfirst($kas->tipe) . ' ' . $kas->kategori . ' - ' . $kas->tanggal
            );

            return redirect()->route('transaksi.kas.index', ['rekening_id' => $request->rekening_id])
                ->with('success', 'Transaksi kas berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencatat transaksi kas. Silakan coba lagi.')->withInput();
        }
    }

    public function getSetoranOutlet(Request $request)
    {
        $outletId = $request->outlet_id;
        $tanggal  = $request->tanggal;

        if (!$outletId || !$tanggal) {
            return response()->json([
                'total_setor'  => 0,
                'pesan'        => 'Outlet dan tanggal wajib diisi.',
                'ada'          => false,
                'sudah_disetor'=> false,
            ]);
        }

        // Koordinator hanya boleh mengakses outlet di wilayahnya sendiri.
        $outletCek = Outlet::find($outletId);
        if (!$outletCek || !$this->bolehAksesWilayah($outletCek->wilayah_id)) {
            return response()->json(['error' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        // Cek apakah sudah pernah dicatat sebagai setoran outlet di kas
        $sudahDisetor = Kas::where('outlet_id', $outletId)
            ->whereDate('tanggal', $tanggal)
            ->where('tipe', 'debit')
            ->where('kategori', 'Pembayaran')
            ->where('keterangan', 'like', 'Setoran %')
            ->exists();

        $laporan = LaporanHarian::where('outlet_id', $outletId)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if (!$laporan) {
            return response()->json([
                'total_setor'  => 0,
                'pesan'        => 'Belum ada laporan harian untuk outlet & tanggal ini.',
                'ada'          => false,
                'sudah_disetor'=> $sudahDisetor,
                'pesan_disetor'=> $sudahDisetor ? 'Setoran outlet ini untuk tanggal tersebut sudah dicatat sebelumnya.' : null,
            ]);
        }

        return response()->json([
            'total_setor'  => (int) $laporan->total_setor,
            'laporan_id'   => $laporan->id,
            'ada'          => true,
            'sudah_disetor'=> $sudahDisetor,
            'pesan_disetor'=> $sudahDisetor ? 'Setoran outlet ini untuk tanggal tersebut sudah dicatat sebelumnya. Batalkan dulu yang lama jika ingin input ulang.' : null,
        ]);
    }

    public function storeSetoran(Request $request)
    {
        $request->validate([
            'rekening_id'             => 'required|exists:rekening,id',
            'outlet_id'               => 'required|exists:outlet,id',
            'tanggal'                 => 'required|date|date_equals:today',
            'total_setor'             => 'required|numeric|min:0',
            'pengeluaran'             => 'nullable|array',
            'pengeluaran.*.keterangan' => 'required_with:pengeluaran|string|max:255',
            'pengeluaran.*.jumlah'    => 'required_with:pengeluaran|numeric|min:1',
        ], [
            'rekening_id.required'   => 'Rekening wajib dipilih.',
            'outlet_id.required'     => 'Outlet wajib dipilih.',
            'tanggal.required'       => 'Tanggal wajib diisi.',
            'tanggal.date_equals'    => 'Tanggal transaksi harus hari ini.',
            'total_setor.required'   => 'Total setor wajib diisi (otomatis dari laporan harian).',
            'pengeluaran.*.keterangan.required_with' => 'Keterangan pengeluaran wajib diisi.',
            'pengeluaran.*.jumlah.required_with'     => 'Jumlah pengeluaran wajib diisi.',
            'pengeluaran.*.jumlah.min'               => 'Jumlah pengeluaran minimal Rp 1.',
        ]);

        // Guard double-setoran: cek sebelum DB::transaction
        $sudahAda = Kas::where('outlet_id', $request->outlet_id)
            ->whereDate('tanggal', $request->tanggal)
            ->where('tipe', 'debit')
            ->where('kategori', 'Pembayaran')
            ->where('keterangan', 'like', 'Setoran %')
            ->exists();

        if ($sudahAda) {
            return back()
                ->with('error', 'Setoran outlet ini untuk tanggal tersebut sudah dicatat. Batalkan dulu yang lama jika ingin input ulang.')
                ->withInput();
        }

        $outlet = Outlet::find($request->outlet_id);
        $tanggalLabel = \Carbon\Carbon::parse($request->tanggal)->locale('id')->isoFormat('D MMMM Y');

        try {
            DB::transaction(function () use ($request, $outlet, $tanggalLabel) {
                // 1. Pemasukan: setoran outlet
                if ($request->total_setor > 0) {
                    Kas::create([
                        'rekening_id'  => $request->rekening_id,
                        'outlet_id'    => $request->outlet_id,
                        'tanggal'      => $request->tanggal,
                        'tipe'         => 'debit',
                        'kategori'     => 'Pembayaran',
                        'sub_kategori' => $outlet?->nama,
                        'keterangan'   => 'Setoran ' . ($outlet?->nama ?? '-') . ' ' . $tanggalLabel,
                        'jumlah'       => $request->total_setor,
                        'saldo'        => 0,
                        'created_by'   => auth()->id(),
                    ]);
                }

                // 2. Pengeluaran operasional agen (per baris)
                foreach ($request->pengeluaran ?? [] as $p) {
                    if (empty($p['keterangan']) || empty($p['jumlah'])) continue;
                    Kas::create([
                        'rekening_id'  => $request->rekening_id,
                        'outlet_id'    => $request->outlet_id,
                        'tanggal'      => $request->tanggal,
                        'tipe'         => 'kredit',
                        'kategori'     => 'Operasional Agen',
                        'sub_kategori' => $outlet?->nama,
                        'keterangan'   => $p['keterangan'],
                        'jumlah'       => $p['jumlah'],
                        'saldo'        => 0,
                        'created_by'   => auth()->id(),
                    ]);
                }
            });

            $this->logActivity(
                'create', 'Kas Harian - Setoran', null,
                after: [
                    'outlet_id'   => $request->outlet_id,
                    'tanggal'     => $request->tanggal,
                    'total_setor' => $request->total_setor,
                ],
                label: 'Kas Setoran ' . ($outlet?->nama ?? '-') . ' - ' . $request->tanggal
            );

            return redirect()->route('transaksi.kas.index', ['rekening_id' => $request->rekening_id])
                ->with('success', 'Setoran outlet & pengeluaran operasional berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencatat setoran. Silakan coba lagi.')->withInput();
        }
    }

    public function destroy(Kas $ka)
    {
        $this->otorisasiWilayah(optional($ka->rekening)->wilayah_id);

        $this->logActivity(
            'delete', 'Kas Harian', $ka,
            before: $ka->only(['id', 'rekening_id', 'tipe', 'kategori', 'jumlah', 'tanggal']),
            label: 'Kas ' . ucfirst($ka->tipe) . ' ' . $ka->kategori . ' - ' . $ka->tanggal
        );
        $ka->update(['deleted_by' => auth()->id()]);
        $ka->delete();
        return redirect()->route('transaksi.kas.index')
            ->with('success', 'Transaksi kas dibatalkan.');
    }

    public function export(Request $request)
    {
        $selectedRekening = $request->rekening_id;
        $rekening = Rekening::find($selectedRekening);

        // Koordinator hanya boleh export kas rekening wilayahnya sendiri.
        $this->otorisasiWilayah(optional($rekening)->wilayah_id);

        $query = Kas::with(['outlet', 'rekening'])
            ->where('rekening_id', $selectedRekening)
            ->orderBy('tanggal')->orderBy('created_at');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }

        $allKas = $query->get();
        $saldo = $rekening?->saldo_awal ?? 0;
        $kasWithSaldo = $allKas->map(function ($k) use (&$saldo) {
            if ($k->tipe === 'debit')
                $saldo += $k->jumlah;
            else
                $saldo -= $k->jumlah;
            $k->saldo_berjalan = $saldo;
            return $k;
        });

        $filename = 'kas-' . ($rekening->nama ?? 'all') . '-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new KasExport($kasWithSaldo, $rekening), $filename);
    }
}