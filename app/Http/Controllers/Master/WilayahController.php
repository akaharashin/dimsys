<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Traits\LogsActivity;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Master\WilayahExport;

class WilayahController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        $query = Wilayah::query();

        if ($request->filled('search')) {
            $query->where('nama', 'like', "%{$request->search}%");
        }
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->filled('status')) {
            $query->where('aktif', $request->status === 'aktif');
        }

        $sort = in_array($request->sort, ['nama', 'tipe']) ? $request->sort : 'nama';
        $dir = $request->dir === 'desc' ? 'desc' : 'asc';
        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;

        $wilayah = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        return view('master.wilayah.index', compact('wilayah'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'tipe' => 'required|in:pusat,cabang',
        ], [
            'nama.required' => 'Nama wilayah wajib diisi.',
            'nama.max' => 'Nama wilayah maksimal 100 karakter.',
            'tipe.required' => 'Tipe wilayah wajib dipilih.',
            'tipe.in' => 'Tipe wilayah harus berupa pusat atau cabang.',
        ]);

        try {
            $wilayah = Wilayah::create($request->only('nama', 'tipe'));
            $this->logActivity('create', 'Wilayah', $wilayah, after: $wilayah->only(['id', 'nama', 'tipe']), label: $wilayah->nama);
            return back()->with('success', 'Wilayah berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan wilayah. Silakan coba lagi.')->withInput();
        }
    }

    public function update(Request $request, Wilayah $wilayah)
    {
        if ($request->input('aktif') == '1' && !$request->has('nama')) {
            $before = $wilayah->only(['id', 'nama', 'tipe', 'aktif']);
            $wilayah->update(['aktif' => true]);
            $this->logActivity('update', 'Wilayah', $wilayah, before: $before, after: $wilayah->fresh()->only(['id', 'nama', 'tipe', 'aktif']), label: 'Aktifkan - ' . $wilayah->nama);
            return back()->with('success', 'Wilayah berhasil diaktifkan.');
        }
        $request->validate([
            'nama' => 'required|string|max:100',
            'tipe' => 'required|in:pusat,cabang',
        ], [
            'nama.required' => 'Nama wilayah wajib diisi.',
            'nama.max' => 'Nama wilayah maksimal 100 karakter.',
            'tipe.required' => 'Tipe wilayah wajib dipilih.',
            'tipe.in' => 'Tipe wilayah harus berupa pusat atau cabang.',
        ]);

        try {
            $before = $wilayah->only(['id', 'nama', 'tipe']);
            $wilayah->update($request->only('nama', 'tipe'));
            $this->logActivity('update', 'Wilayah', $wilayah, before: $before, after: $wilayah->only(['id', 'nama', 'tipe']), label: $wilayah->nama);
            return back()->with('success', 'Wilayah berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui wilayah. Silakan coba lagi.')->withInput();
        }
    }

    public function destroy(Wilayah $wilayah)
    {
        $before = $wilayah->only(['id', 'nama', 'tipe', 'aktif']);
        $wilayah->update(['aktif' => false]);
        $this->logActivity('update', 'Wilayah', $wilayah, before: $before, after: $wilayah->fresh()->only(['id', 'nama', 'tipe', 'aktif']), label: 'Nonaktifkan - ' . $wilayah->nama);
        return back()->with('success', 'Wilayah dinonaktifkan.');
    }

    public function export()
    {
        return Excel::download(new WilayahExport, 'master-wilayah-' . now()->format('Y-m-d') . '.xlsx');
    }
}