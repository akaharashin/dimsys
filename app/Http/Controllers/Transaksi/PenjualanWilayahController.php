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
use App\Models\FotoPindahStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Transaksi\PenjualanWilayahExport;

class PenjualanWilayahController extends Controller
{
    public function index(Request $request)
    {
        $wilayahList = Wilayah::where('aktif', true)->orderBy('nama')->get();

        $query = PenjualanWilayah::with(['wilayahAsal', 'wilayahTujuan', 'details'])
            ->orderByDesc('tanggal')->orderByDesc('created_at');

        // Koordinator hanya lihat pindah stok yang ditujukan ke wilayahnya (untuk approval)
        if (auth()->user()->hasRole('koordinator')) {
            $query->where(function ($q) {
                $q->where('wilayah_tujuan_id', auth()->user()->wilayah_id)
                    ->orWhere('wilayah_asal_id', auth()->user()->wilayah_id);
            });
        }

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
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
            ->orderByDesc('tanggal')->orderByDesc('created_at');

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
        $filename = 'pindah-stok-' . now()->format('Y-m-d') . '.xlsx';
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
            'tanggal' => 'required|date|date_equals:today',
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
            'tanggal.date_equals' => 'Tanggal transaksi harus hari ini.',
            'status_bayar.required' => 'Status bayar wajib dipilih.',
            'status_bayar.in' => 'Status bayar harus berupa lunas, belum lunas, atau sebagian.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
            'jumlah.*.integer' => 'Jumlah produk harus berupa bilangan bulat.',
            'jumlah.*.min' => 'Jumlah produk tidak boleh bernilai negatif.',
        ]);

        $hasAny = collect($request->jumlah ?? [])->filter(fn($j) => (int) $j > 0)->count() > 0;
        if (!$hasAny) {
            return back()->with('error', 'Minimal satu produk harus memiliki jumlah lebih dari 0.')->withInput();
        }

        // Validasi stok tersedia (hanya hitung yang sudah disetujui)
        foreach ($request->jumlah as $pid => $jumlah) {
            $jumlah = (int) $jumlah;
            if ($jumlah <= 0)
                continue;

            $masuk = StokMasukDetail::whereHas(
                'stokMasuk',
                fn($q) =>
                $q->where('wilayah_id', $request->wilayah_asal_id)
            )->where('produk_id', $pid)->sum('jumlah');

            $sudahOut = DistribusiDetail::whereHas(
                'distribusi',
                fn($q) =>
                $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $request->wilayah_asal_id))
            )->where('produk_id', $pid)->sum('jumlah_out');

            $keluarWilayah = PenjualanWilayahDetail::whereHas(
                'penjualan',
                fn($q) =>
                $q->where('wilayah_asal_id', $request->wilayah_asal_id)->where('status', 'disetujui')
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
                $penjualan = PenjualanWilayah::create([
                    'tipe' => 'transfer',
                    'wilayah_asal_id' => $request->wilayah_asal_id,
                    'wilayah_tujuan_id' => $request->wilayah_tujuan_id,
                    'tanggal' => $request->tanggal,
                    'total' => 0,
                    'status_bayar' => null,
                    'keterangan' => $request->keterangan,
                    'status' => 'menunggu',
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

                return redirect()->route('transaksi.penjualan-wilayah.index')
                    ->with('success', 'Pindah stok berhasil dicatat. Menunggu persetujuan koordinator wilayah tujuan.');
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
                    'status' => 'disetujui',
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

    public function approve(PenjualanWilayah $penjualanWilayah)
    {
        if ($penjualanWilayah->tipe !== 'transfer' || $penjualanWilayah->status !== 'menunggu') {
            return back()->with('error', 'Hanya pindah stok berstatus menunggu yang bisa disetujui.');
        }

        // Koordinator hanya bisa approve untuk wilayahnya sebagai tujuan
        if (
            auth()->user()->hasRole('koordinator') &&
            auth()->user()->wilayah_id !== $penjualanWilayah->wilayah_tujuan_id
        ) {
            return back()->with('error', 'Anda tidak berhak menyetujui pindah stok ini.');
        }

        // Validasi ulang stok sebelum approve
        $penjualanWilayah->load('details');
        foreach ($penjualanWilayah->details as $detail) {
            $masuk = StokMasukDetail::whereHas(
                'stokMasuk',
                fn($q) =>
                $q->where('wilayah_id', $penjualanWilayah->wilayah_asal_id)
            )->where('produk_id', $detail->produk_id)->sum('jumlah');

            $sudahOut = DistribusiDetail::whereHas(
                'distribusi',
                fn($q) =>
                $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $penjualanWilayah->wilayah_asal_id))
            )->where('produk_id', $detail->produk_id)->sum('jumlah_out');

            $keluarWilayah = PenjualanWilayahDetail::whereHas(
                'penjualan',
                fn($q) =>
                $q->where('wilayah_asal_id', $penjualanWilayah->wilayah_asal_id)
                    ->where('status', 'disetujui')
            )->where('produk_id', $detail->produk_id)->sum('jumlah');

            $stokTersedia = max(0, $masuk - $sudahOut - $keluarWilayah);
            $produk = \App\Models\Produk::find($detail->produk_id);

            if ($detail->jumlah > $stokTersedia) {
                return back()->with(
                    'error',
                    "Stok {$produk->nama} di wilayah asal tidak mencukupi. Tersedia: {$stokTersedia} pcs, dibutuhkan: {$detail->jumlah} pcs."
                );
            }
        }

        try {
            $wilayahAsal = Wilayah::find($penjualanWilayah->wilayah_asal_id);

            $stokMasuk = StokMasuk::create([
                'wilayah_id' => $penjualanWilayah->wilayah_tujuan_id,
                'supplier_id' => null,
                'tanggal' => $penjualanWilayah->tanggal,
                'jenis' => 'masuk',
                'keterangan' => 'Pindah stok dari ' . $wilayahAsal->nama,
                'created_by' => auth()->id(),
            ]);

            foreach ($penjualanWilayah->details as $detail) {
                StokMasukDetail::create([
                    'stok_masuk_id' => $stokMasuk->id,
                    'produk_id' => $detail->produk_id,
                    'jumlah' => $detail->jumlah,
                    'hpp' => 0,
                ]);
            }

            $penjualanWilayah->update([
                'status' => 'disetujui',
                'transfer_stok_masuk_id' => $stokMasuk->id,
            ]);

            return redirect()->route('transaksi.penjualan-wilayah.index')
                ->with('success', 'Pindah stok disetujui. Stok masuk di wilayah tujuan berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui pindah stok. Silakan coba lagi.');
        }
    }

    public function reject(PenjualanWilayah $penjualanWilayah)
    {
        if ($penjualanWilayah->tipe !== 'transfer' || $penjualanWilayah->status !== 'menunggu') {
            return back()->with('error', 'Hanya pindah stok berstatus menunggu yang bisa ditolak.');
        }

        if (
            auth()->user()->hasRole('koordinator') &&
            auth()->user()->wilayah_id !== $penjualanWilayah->wilayah_tujuan_id
        ) {
            return back()->with('error', 'Anda tidak berhak menolak pindah stok ini.');
        }

        try {
            $penjualanWilayah->update(['status' => 'ditolak']);
            return redirect()->route('transaksi.penjualan-wilayah.index')
                ->with('success', 'Pindah stok ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak pindah stok. Silakan coba lagi.');
        }
    }

    public function show(PenjualanWilayah $penjualanWilayah)
    {
        $penjualanWilayah->load(['wilayahAsal', 'wilayahTujuan', 'details.produk', 'fotos']);
        return view('transaksi.penjualan-wilayah.show', compact('penjualanWilayah'));
    }

    public function uploadFoto(Request $request, $id)
    {
        $pindahStok = PenjualanWilayah::findOrFail($id);
        $user = auth()->user();

        $bolehUpload = $user->hasRole('admin_pusat') ||
            ($user->hasRole('koordinator') &&
                $user->wilayah_id === $pindahStok->wilayah_tujuan_id);

        if (!$bolehUpload) {
            return response()->json(['error' => 'Hanya penerima yang bisa upload bukti.'], 403);
        }

        $tipe = $request->input('tipe');

        if ($tipe === 'video') {
            if ($pindahStok->fotos()->where('tipe', 'video')->count() >= 3) {
                return response()->json(['error' => 'Maksimal 3 video per transaksi.'], 422);
            }
            $request->validate([
                'foto' => 'required|file|mimes:mp4,mov,avi,webm|max:102400',
                'tipe' => 'required|in:foto_real,berita_acara,video',
            ], [
                'foto.required' => 'File video wajib dipilih.',
                'foto.mimes' => 'Format tidak didukung. Gunakan MP4, MOV, AVI, atau WebM.',
                'foto.max' => 'Ukuran video maksimal 100 MB.',
            ]);
        } else {
            if ($pindahStok->fotos()->whereIn('tipe', ['foto_real', 'berita_acara'])->count() >= 10) {
                return response()->json(['error' => 'Maksimal 10 foto per transaksi.'], 422);
            }
            $request->validate([
                'foto' => 'required|file|image|max:10240',
                'tipe' => 'required|in:foto_real,berita_acara,video',
            ], [
                'foto.required' => 'File foto wajib dipilih.',
                'foto.image' => 'File harus berupa gambar (JPG, PNG, WebP).',
                'foto.max' => 'Ukuran foto maksimal 10 MB.',
            ]);
        }

        $file = $request->file('foto');
        $namaAsli = $file->getClientOriginalName();

        if ($tipe === 'video') {
            $namaFile = 'video_' . uniqid() . '_' . time() . '.mp4';
            ['ukuran' => $ukuranKb, 'durasi' => $durasi] = $this->prosesVideo($file, $namaFile);

            $foto = FotoPindahStok::create([
                'penjualan_wilayah_id' => $id,
                'tipe' => 'video',
                'nama_file' => $namaFile,
                'nama_asli' => $namaAsli,
                'ukuran' => $ukuranKb,
                'durasi' => $durasi,
                'created_by' => auth()->id(),
            ]);
        } else {
            $namaFile = 'foto_' . uniqid() . '_' . time() . '.jpg';

            try {
                $manager = new ImageManager(new Driver());
                $img = $manager->read($file);
                $img->scaleDown(1920, 1920);
                $encoded = $img->toJpeg(75)->toString();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Format foto tidak didukung. Gunakan JPG, PNG, atau WebP.'], 422);
            }

            Storage::disk('public')->put('pindah-stok/' . $namaFile, $encoded);
            $ukuranKb = (int) ceil(strlen($encoded) / 1024);

            $foto = FotoPindahStok::create([
                'penjualan_wilayah_id' => $id,
                'tipe' => $tipe,
                'nama_file' => $namaFile,
                'nama_asli' => $namaAsli,
                'ukuran' => $ukuranKb,
                'durasi' => null,
                'created_by' => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'foto_id' => $foto->id,
            'url' => $foto->url,
            'ukuran_kb' => $foto->ukuran,
        ]);
    }

    private function prosesVideo($file, string $namaFile): array
    {
        $videoDir = storage_path('app/public/pindah-stok/videos');
        if (!is_dir($videoDir)) {
            @mkdir($videoDir, 0755, true);
        }

        $outputPath = $videoDir . DIRECTORY_SEPARATOR . $namaFile;
        $durasiDetik = null;
        $compressed = false;

        // Cari FFmpeg
        $ffmpegPath = null;
        if (function_exists('shell_exec')) {
            $checkCmd = PHP_OS_FAMILY === 'Windows' ? 'where ffmpeg 2>NUL' : 'which ffmpeg 2>/dev/null';
            $check = @shell_exec($checkCmd);
            if (!empty(trim($check ?? ''))) {
                $lines = array_filter(array_map('trim', explode("\n", $check)));
                $ffmpegPath = reset($lines);
            }
        }

        if ($ffmpegPath) {
            $inputPath = realpath($file->getRealPath());
            $null = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';

            // Durasi via ffprobe
            $ffprobePath = str_replace(
                ['ffmpeg.exe', 'ffmpeg'],
                ['ffprobe.exe', 'ffprobe'],
                $ffmpegPath
            );
            if (file_exists($ffprobePath)) {
                $dur = @shell_exec(sprintf(
                    '"%s" -v quiet -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 "%s" 2>%s',
                    $ffprobePath,
                    $inputPath,
                    $null
                ));
                if (is_numeric(trim($dur ?? ''))) {
                    $durasiDetik = (int) round((float) $dur);
                }
            }

            // Compress: max 720p, H264, 1000kbps video, 128kbps audio
            $cmd = sprintf(
                '"%s" -i "%s" -vcodec libx264 -vf scale=-2:720 -b:v 1000k -b:a 128k -movflags +faststart -y "%s" 2>%s',
                $ffmpegPath,
                $inputPath,
                $outputPath,
                $null
            );
            @shell_exec($cmd);

            if (file_exists($outputPath) && filesize($outputPath) > 0) {
                $compressed = true;
            }
        }

        if (!$compressed) {
            $file->move($videoDir, $namaFile);
        }

        $ukuranKb = file_exists($outputPath) ? (int) ceil(filesize($outputPath) / 1024) : 0;

        return ['ukuran' => $ukuranKb, 'durasi' => $durasiDetik];
    }

    public function hapusFoto($fotoId)
    {
        $foto = FotoPindahStok::findOrFail($fotoId);
        $pindahStok = $foto->penjualanWilayah;
        $user = auth()->user();

        $bolehHapus = $user->hasRole('admin_pusat') ||
            ($user->hasRole('koordinator') &&
                $user->wilayah_id === $pindahStok->wilayah_tujuan_id);

        if (!$bolehHapus) {
            return response()->json(['error' => 'Anda tidak berhak menghapus file ini.'], 403);
        }

        if ($foto->tipe === 'video') {
            Storage::disk('public')->delete('pindah-stok/videos/' . $foto->nama_file);
        } else {
            Storage::disk('public')->delete('pindah-stok/' . $foto->nama_file);
        }

        $foto->delete();

        return response()->json(['success' => true]);
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
        if ($penjualanWilayah->status === 'disetujui') {
            return back()->with('error', 'Pindah stok yang sudah disetujui tidak dapat dibatalkan.');
        }

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

        $msg = $tipe === 'transfer' ? 'Pindah stok berhasil dibatalkan.' : 'Penjualan wilayah dibatalkan.';
        return redirect()->route('transaksi.penjualan-wilayah.index')->with('success', $msg);
    }
}
