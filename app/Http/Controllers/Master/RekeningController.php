<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Traits\LogsActivity;
use App\Models\Rekening;
use App\Models\Wilayah;
use Illuminate\Http\Request;

class RekeningController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $wilayahList = Wilayah::where('aktif', true)->orderBy('nama')->get();

        $sort = in_array($request->sort, ['nama', 'tipe', 'created_at']) ? $request->sort : 'nama';
        $dir  = $request->direction === 'desc' ? 'desc' : 'asc';

        $query = Rekening::with('wilayah')->orderBy($sort, $dir);

        if ($request->filled('search')) {
            $query->where('nama', 'like', "%{$request->search}%");
        }
        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->filled('status')) {
            $query->where('aktif', $request->status === 'aktif');
        }

        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;
        $rekening = $query->paginate($perPage)->withQueryString();

        return view('master.rekening.index', compact('rekening', 'wilayahList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'       => 'required|string|max:100',
            'tipe'       => 'required|in:kas_kecil,bank',
            'wilayah_id' => 'required|exists:wilayah,id',
            'saldo_awal' => 'nullable|numeric|min:0',
        ], [
            'nama.required'       => 'Nama rekening wajib diisi.',
            'nama.max'            => 'Nama rekening maksimal 100 karakter.',
            'tipe.required'       => 'Tipe rekening wajib dipilih.',
            'tipe.in'             => 'Tipe rekening tidak valid.',
            'wilayah_id.required' => 'Wilayah wajib dipilih.',
            'wilayah_id.exists'   => 'Wilayah yang dipilih tidak valid.',
            'saldo_awal.numeric'  => 'Saldo awal harus berupa angka.',
            'saldo_awal.min'      => 'Saldo awal tidak boleh negatif.',
        ]);

        try {
            $rekening = Rekening::create([
                'nama'       => $request->nama,
                'tipe'       => $request->tipe,
                'wilayah_id' => $request->wilayah_id,
                'saldo_awal' => $request->saldo_awal ?? 0,
                'aktif'      => true,
            ]);

            $this->logActivity(
                'create', 'Rekening', $rekening,
                after: $rekening->only(['id', 'nama', 'tipe', 'wilayah_id', 'saldo_awal']),
                label: 'Rekening ' . $rekening->nama
            );

            return back()->with('success', 'Rekening berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan rekening. Silakan coba lagi.')->withInput();
        }
    }

    public function update(Request $request, Rekening $rekening)
    {
        if ($request->input('aktif') == '1' && !$request->has('nama')) {
            $before = $rekening->only(['id', 'nama', 'tipe', 'aktif']);
            $rekening->update(['aktif' => true]);
            $this->logActivity(
                'update', 'Rekening', $rekening,
                before: $before,
                after: $rekening->fresh()->only(['id', 'nama', 'tipe', 'aktif']),
                label: 'Aktifkan - ' . $rekening->nama
            );
            return back()->with('success', 'Rekening berhasil diaktifkan.');
        }

        $request->validate([
            'nama'       => 'required|string|max:100',
            'tipe'       => 'required|in:kas_kecil,bank',
            'wilayah_id' => 'required|exists:wilayah,id',
            'saldo_awal' => 'nullable|numeric|min:0',
        ], [
            'nama.required'       => 'Nama rekening wajib diisi.',
            'nama.max'            => 'Nama rekening maksimal 100 karakter.',
            'tipe.required'       => 'Tipe rekening wajib dipilih.',
            'tipe.in'             => 'Tipe rekening tidak valid.',
            'wilayah_id.required' => 'Wilayah wajib dipilih.',
            'wilayah_id.exists'   => 'Wilayah yang dipilih tidak valid.',
            'saldo_awal.numeric'  => 'Saldo awal harus berupa angka.',
            'saldo_awal.min'      => 'Saldo awal tidak boleh negatif.',
        ]);

        try {
            $before = $rekening->only(['id', 'nama', 'tipe', 'wilayah_id', 'saldo_awal']);
            $rekening->update([
                'nama'       => $request->nama,
                'tipe'       => $request->tipe,
                'wilayah_id' => $request->wilayah_id,
                'saldo_awal' => $request->saldo_awal ?? 0,
            ]);

            $this->logActivity(
                'update', 'Rekening', $rekening,
                before: $before,
                after: $rekening->fresh()->only(['id', 'nama', 'tipe', 'wilayah_id', 'saldo_awal']),
                label: 'Rekening ' . $rekening->nama
            );

            return back()->with('success', 'Rekening berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui rekening. Silakan coba lagi.')->withInput();
        }
    }

    public function destroy(Rekening $rekening)
    {
        $before = $rekening->only(['id', 'nama', 'tipe', 'aktif']);
        $rekening->update(['aktif' => false]);

        $this->logActivity(
            'update', 'Rekening', $rekening,
            before: $before,
            after: $rekening->fresh()->only(['id', 'nama', 'tipe', 'aktif']),
            label: 'Nonaktifkan - ' . $rekening->nama
        );

        return back()->with('success', 'Rekening berhasil dinonaktifkan.');
    }
}
