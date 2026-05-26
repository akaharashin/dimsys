<?php
namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Traits\LogsActivity;
use App\Models\Kas;
use App\Models\Rekening;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Transaksi\KasExport;

class KasController extends Controller
{
    use LogsActivity;
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

        $query = Kas::with(['outlet', 'rekening'])
            ->where('rekening_id', $selectedRekening)
            ->orderBy('tanggal')
            ->orderBy('created_at');

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('keterangan', 'like', "%{$request->search}%")
                    ->orWhere('sub_kategori', 'like', "%{$request->search}%")
                    ->orWhere('penerima', 'like', "%{$request->search}%");
            });
        }

        // Hitung saldo berjalan (ASC order supaya akumulasi benar)
        $allKas = $query->get();
        $saldo = Rekening::find($selectedRekening)?->saldo_awal ?? 0;

        $kasWithSaldo = $allKas->map(function ($k) use (&$saldo) {
            if ($k->tipe === 'debit') {
                $saldo += $k->jumlah;
            } else {
                $saldo -= $k->jumlah;
            }
            $k->saldo_berjalan = $saldo;
            return $k;
        });

        // Summary dihitung sebelum reverse
        $totalDebit = $kasWithSaldo->where('tipe', 'debit')->sum('jumlah');
        $totalKredit = $kasWithSaldo->where('tipe', 'kredit')->sum('jumlah');
        $saldoAkhir = $kasWithSaldo->last()?->saldo_berjalan ?? 0;

        // Balik urutan untuk tampilkan terbaru di atas
        $kasWithSaldo = $kasWithSaldo->reverse()->values();

        // Paginate manual
        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;
        $page = $request->input('page', 1);
        $items = $kasWithSaldo->forPage($page, $perPage);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $kasWithSaldo->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('transaksi.kas.index', compact(
            'rekeningList',
            'selectedRekening',
            'paginated',
            'kasWithSaldo',
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

    public function destroy(Kas $ka)
    {
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