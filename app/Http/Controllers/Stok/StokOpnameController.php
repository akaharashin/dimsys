<?php
namespace App\Http\Controllers\Stok;

use App\Http\Controllers\Controller;
use App\Traits\LogsActivity;
use App\Models\StokOpname;
use App\Models\StokOpnameDetail;
use App\Models\Wilayah;
use App\Models\Produk;
use App\Models\StokMasukDetail;
use App\Models\DistribusiDetail;
use App\Models\PenjualanWilayahDetail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Stok\StokOpnameExport;
use App\Models\StokMasuk;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class StokOpnameController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        $wilayahList = Wilayah::where('aktif', true)->orderBy('nama')->get();

        $sort = in_array($request->sort, ['tanggal', 'wilayah_id', 'created_at']) ? $request->sort : 'tanggal';
        $dir  = $request->direction === 'asc' ? 'asc' : 'desc';

        $query = StokOpname::with(['wilayah', 'details'])
            ->orderBy($sort, $dir);
        if ($sort === 'tanggal') {
            $query->orderBy('created_at', $dir);
        }

        if (auth()->user()->hasRole('koordinator')) {
            $query->where('wilayah_id', auth()->user()->wilayah_id);
        }
        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
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
        $stokOpname = $query->paginate($perPage)->withQueryString();

        return view('stok.opname.index', compact('stokOpname', 'wilayahList'));
    }

    public function create()
    {
        $wilayahList = auth()->user()->hasRole('koordinator')
            ? Wilayah::where('id', auth()->user()->wilayah_id)->get()
            : Wilayah::where('aktif', true)->orderBy('nama')->get();

        $produkList = Produk::where('aktif', true)->orderBy('nama')->get();

        return view('stok.opname.create', compact('wilayahList', 'produkList'));
    }

    public function getStokSistem(Request $request)
    {
        $wilayahId = $request->wilayah_id;
        $produkList = Produk::where('aktif', true)->orderBy('nama')->get();

        $stokSistem = $produkList->map(function ($produk) use ($wilayahId) {
            $masuk = StokMasukDetail::whereHas(
                'stokMasuk',
                fn($q) =>
                $q->where('wilayah_id', $wilayahId)
            )->where('produk_id', $produk->id)->sum('jumlah');

            $out = DistribusiDetail::whereHas(
                'distribusi',
                fn($q) =>
                $q->whereHas('outlet', fn($o) => $o->where('wilayah_id', $wilayahId))
            )->where('produk_id', $produk->id)->sum('jumlah_out');

            $keluarWilayah = PenjualanWilayahDetail::whereHas(
                'penjualan',
                fn($q) =>
                $q->where('wilayah_asal_id', $wilayahId)->where('status', 'disetujui')
            )->where('produk_id', $produk->id)->sum('jumlah');

            return [
                'produk_id' => $produk->id,
                'nama' => $produk->nama,
                'hpp' => $produk->hpp,
                'stok_sistem' => $masuk - $out - $keluarWilayah,
            ];
        })->filter(fn($s) => $s['stok_sistem'] != 0);

        return response()->json($stokSistem->values());
    }

    public function store(Request $request)
    {
        $request->validate([
            'wilayah_id' => 'required|exists:wilayah,id',
            'tanggal' => 'required|date|date_equals:today',
            'keterangan' => 'nullable|string|max:255',
            'produk_id' => 'required|array|min:1',
            'stok_fisik' => 'required|array',
        ], [
            'wilayah_id.required' => 'Wilayah wajib dipilih.',
            'wilayah_id.exists' => 'Wilayah yang dipilih tidak valid.',
            'tanggal.required' => 'Tanggal stok opname wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.date_equals' => 'Tanggal transaksi harus hari ini.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
            'produk_id.required' => 'Data produk wajib diisi.',
            'produk_id.min' => 'Minimal satu produk wajib diisi.',
            'stok_fisik.required' => 'Data stok fisik wajib diisi.',
        ]);

        try {
            $stokOpname = StokOpname::create([
                'wilayah_id' => $request->wilayah_id,
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
                'status' => 'final',
                'created_by' => auth()->id(),
            ]);

            foreach ($request->produk_id as $i => $pid) {
                $stokSistem = $request->stok_sistem[$i] ?? 0;
                $stokFisik = $request->stok_fisik[$i] ?? 0;
                $selisih = $stokFisik - $stokSistem;
                $produk = Produk::find($pid);
                $hpp = $produk->hpp;

                StokOpnameDetail::create([
                    'stok_opname_id' => $stokOpname->id,
                    'produk_id' => $pid,
                    'stok_sistem' => $stokSistem,
                    'stok_fisik' => $stokFisik,
                    'selisih' => $selisih,
                    'hpp_snapshot' => $hpp,
                    'nilai_selisih' => $selisih * $hpp,
                ]);
            }

            $this->logActivity(
                'create', 'Stok Opname', $stokOpname,
                after: $stokOpname->only(['id', 'wilayah_id', 'tanggal', 'keterangan', 'status']),
                label: 'Stok Opname ' . optional($stokOpname->wilayah)->nama . ' - ' . $stokOpname->tanggal
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success'    => true,
                    'id'         => $stokOpname->id,
                    'redirect'   => route('stok.opname.show', $stokOpname),
                    'upload_url' => route('stok.opname.foto.upload', $stokOpname->id),
                ]);
            }

            return redirect()->route('stok.opname.index')
                ->with('success', 'Stok Opname berhasil disimpan.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Gagal menyimpan stok opname. Silakan coba lagi.'], 500);
            }
            return back()->with('error', 'Gagal menyimpan stok opname. Silakan coba lagi.')->withInput();
        }
    }

    public function show(StokOpname $stokOpname)
    {
        $stokOpname->load(['wilayah', 'details.produk', 'createdBy']);
        return view('stok.opname.show', compact('stokOpname'));
    }

    public function terapkanKoreksi(StokOpname $stokOpname)
    {
        if ($stokOpname->sudahDikoreksi()) {
            return back()->with('error', 'Koreksi sudah pernah diterapkan untuk STO ini.');
        }

        if ($stokOpname->status !== 'final') {
            return back()->with('error', 'STO harus final sebelum bisa diterapkan.');
        }

        $stokOpname->load('details', 'wilayah');

        $adaSelisih = $stokOpname->details->where('selisih', '!=', 0)->count();
        if ($adaSelisih === 0) {
            return back()->with('error', 'Tidak ada selisih pada STO ini, koreksi tidak diperlukan.');
        }

        try {
            DB::transaction(function () use ($stokOpname) {
                $supplier = Supplier::where('aktif', true)->orderBy('created_at')->first();
                if (!$supplier) {
                    $supplier = Supplier::first()
                        ?? Supplier::create(['nama' => 'Internal', 'aktif' => true]);
                }

                $tanggalLabel = \Carbon\Carbon::parse($stokOpname->tanggal)
                    ->locale('id')->isoFormat('D MMMM Y');

                $koreksi = StokMasuk::create([
                    'wilayah_id'     => $stokOpname->wilayah_id,
                    'supplier_id'    => $supplier->id,
                    'tanggal'        => $stokOpname->tanggal,
                    'jenis'          => 'koreksi',
                    'keterangan'     => 'Koreksi STO ' . $tanggalLabel,
                    'stok_opname_id' => $stokOpname->id,
                    'created_by'     => auth()->id(),
                ]);

                foreach ($stokOpname->details as $d) {
                    if ((int) $d->selisih === 0) continue;

                    StokMasukDetail::create([
                        'stok_masuk_id' => $koreksi->id,
                        'produk_id'     => $d->produk_id,
                        'jumlah'        => $d->selisih,
                        'hpp'           => $d->hpp_snapshot,
                    ]);
                }

                $this->logActivity(
                    'update',
                    'Stok Opname',
                    $stokOpname,
                    after: ['koreksi_diterapkan' => true],
                    label: 'Terapkan Koreksi STO - ' . optional($stokOpname->wilayah)->nama
                );
            });

            return back()->with('success', 'Koreksi stok berhasil diterapkan. Stok freezer sudah diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menerapkan koreksi. Silakan coba lagi.');
        }
    }

    public function destroy(StokOpname $stokOpname)
    {
        $koreksi = StokMasuk::where('stok_opname_id', $stokOpname->id)->first();
        $adaKoreksi = (bool) $koreksi;

        try {
            DB::transaction(function () use ($stokOpname, $koreksi) {
                if ($koreksi) {
                    $koreksi->details()->delete();
                    $koreksi->update(['deleted_by' => auth()->id()]);
                    $koreksi->delete();
                }

                $stokOpname->update(['deleted_by' => auth()->id()]);
                $stokOpname->delete();
            });

            $this->logActivity(
                'delete', 'Stok Opname', $stokOpname,
                before: $stokOpname->only(['id', 'wilayah_id', 'tanggal', 'keterangan', 'status']),
                after: $adaKoreksi ? ['koreksi_dibatalkan' => true] : null,
                label: 'Batalkan STO - ' . optional($stokOpname->wilayah)->nama . ' - ' . $stokOpname->tanggal
            );

            $pesan = $adaKoreksi
                ? 'Stok Opname dibatalkan. Koreksi stok juga dibatalkan.'
                : 'Stok Opname dibatalkan.';

            return redirect()->route('stok.opname.index')->with('success', $pesan);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membatalkan Stok Opname. Silakan coba lagi.');
        }
    }

    public function uploadFoto(Request $request, $id)
    {
        $stokOpname = StokOpname::findOrFail($id);
        $user = auth()->user();

        if (!$user->hasRole('admin_pusat') && !$user->hasRole('koordinator')) {
            return response()->json(['error' => 'Anda tidak berhak mengupload foto.'], 403);
        }

        $request->validate([
            'foto' => 'required|file|mimes:jpeg,jpg,png,webp|max:10240',
            'tipe' => 'required|in:foto_real,berita_acara',
        ], [
            'foto.required' => 'File foto wajib dipilih.',
            'foto.file'     => 'File tidak valid.',
            'foto.mimes'    => 'Format file harus JPG, PNG, atau WebP.',
            'foto.max'      => 'Ukuran file maksimal 10 MB.',
            'tipe.required' => 'Tipe foto wajib dipilih.',
            'tipe.in'       => 'Tipe foto tidak valid.',
        ]);

        $file     = $request->file('foto');
        $tipe     = $request->input('tipe');
        $namaAsli = $file->getClientOriginalName();

        if ($stokOpname->getMedia($tipe)->count() >= 5) {
            return response()->json(['error' => 'Maksimal 5 foto per koleksi.'], 422);
        }

        $namaFile    = 'foto_' . uniqid() . '_' . time() . '.jpg';
        $tmpPath     = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $namaFile;
        $sourceImage = null;
        $mime        = $file->getMimeType();

        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            $sourceImage = imagecreatefromjpeg($file->getPathname());
        } elseif ($mime === 'image/png') {
            $sourceImage = imagecreatefrompng($file->getPathname());
        } elseif ($mime === 'image/webp') {
            $sourceImage = imagecreatefromwebp($file->getPathname());
        } else {
            return response()->json(['error' => 'Format tidak didukung. Gunakan JPG, PNG, atau WebP.'], 422);
        }

        if (!$sourceImage) {
            return response()->json(['error' => 'Gagal membaca file gambar.'], 422);
        }

        $lebar   = imagesx($sourceImage);
        $tinggi  = imagesy($sourceImage);
        $maxSize = 1920;

        if ($lebar > $maxSize || $tinggi > $maxSize) {
            if ($lebar > $tinggi) {
                $lebarBaru  = $maxSize;
                $tinggiBaru = (int) round($tinggi * ($maxSize / $lebar));
            } else {
                $tinggiBaru = $maxSize;
                $lebarBaru  = (int) round($lebar * ($maxSize / $tinggi));
            }
            $resized = imagecreatetruecolor($lebarBaru, $tinggiBaru);
            imagecopyresampled($resized, $sourceImage, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebar, $tinggi);
            imagedestroy($sourceImage);
            $sourceImage = $resized;
        }

        imagejpeg($sourceImage, $tmpPath, 75);
        imagedestroy($sourceImage);

        $media = $stokOpname->addMedia($tmpPath)
            ->usingFileName($namaFile)
            ->usingName($namaAsli)
            ->toMediaCollection($tipe);

        $this->logActivity(
            'upload', 'Stok Opname - Foto', $stokOpname,
            label: 'Upload ' . $tipe . ' - ' . optional($stokOpname->wilayah)->nama . ' - ' . $stokOpname->tanggal
        );

        return response()->json([
            'success'   => true,
            'id'        => $media->id,
            'url'       => asset('storage/' . $media->id . '/' . $media->file_name),
            'ukuran_kb' => (int) ceil($media->size / 1024),
            'nama_asli' => $namaAsli,
        ]);
    }

    public function hapusFoto($fotoId)
    {
        $media = Media::find($fotoId);
        if (!$media) {
            return response()->json(['error' => 'File tidak ditemukan.'], 404);
        }

        $stokOpname = StokOpname::find($media->model_id);
        if (!$stokOpname) {
            return response()->json(['error' => 'Transaksi tidak ditemukan.'], 404);
        }

        $user = auth()->user();
        if (!$user->hasRole('admin_pusat') && !$user->hasRole('koordinator')) {
            return response()->json(['error' => 'Anda tidak berhak menghapus file ini.'], 403);
        }

        $media->delete();

        return response()->json(['success' => true]);
    }

    public function export(Request $request)
    {
        $query = StokOpname::with(['wilayah', 'details.produk'])->orderByDesc('tanggal');
        if ($request->filled('wilayah_id'))
            $query->where('wilayah_id', $request->wilayah_id);
        if ($request->filled('dari'))
            $query->whereDate('tanggal', '>=', $request->dari);
        if ($request->filled('sampai'))
            $query->whereDate('tanggal', '<=', $request->sampai);

        $data = $query->get();
        $filename = 'stok-opname-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new StokOpnameExport($data), $filename);
    }
}