<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Master\OutletExport;

class OutletController extends Controller
{
    public function index(Request $request)
    {
        $wilayah = Wilayah::where('aktif', true)->orderBy('nama')->get();
        $query = Outlet::with('wilayah');

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

        $sort = in_array($request->sort, ['nama', 'tipe']) ? $request->sort : 'nama';
        $dir = $request->dir === 'desc' ? 'desc' : 'asc';
        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;

        $outlet = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        return view('master.outlet.index', compact('outlet', 'wilayah'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'wilayah_id' => 'required|exists:wilayah,id',
            'tipe' => 'required|in:agen,mitra,umum',
        ], [
            'nama.required' => 'Nama outlet wajib diisi.',
            'nama.max' => 'Nama outlet maksimal 100 karakter.',
            'wilayah_id.required' => 'Wilayah wajib dipilih.',
            'wilayah_id.exists' => 'Wilayah yang dipilih tidak valid.',
            'tipe.required' => 'Tipe outlet wajib dipilih.',
            'tipe.in' => 'Tipe outlet harus berupa agen, mitra, atau umum.',
        ]);

        try {
            Outlet::create($request->only('nama', 'wilayah_id', 'tipe'));
            return back()->with('success', 'Outlet berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan outlet. Silakan coba lagi.')->withInput();
        }
    }

    public function update(Request $request, Outlet $outlet)
    {
        if ($request->input('aktif') == '1' && !$request->has('nama')) {
            $outlet->update(['aktif' => true]);
            return back()->with('success', 'Outlet berhasil diaktifkan.');
        }
        $request->validate([
            'nama' => 'required|string|max:100',
            'wilayah_id' => 'required|exists:wilayah,id',
            'tipe' => 'required|in:agen,mitra,umum',
        ], [
            'nama.required' => 'Nama outlet wajib diisi.',
            'nama.max' => 'Nama outlet maksimal 100 karakter.',
            'wilayah_id.required' => 'Wilayah wajib dipilih.',
            'wilayah_id.exists' => 'Wilayah yang dipilih tidak valid.',
            'tipe.required' => 'Tipe outlet wajib dipilih.',
            'tipe.in' => 'Tipe outlet harus berupa agen, mitra, atau umum.',
        ]);

        try {
            $outlet->update($request->only('nama', 'wilayah_id', 'tipe'));
            return back()->with('success', 'Outlet berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui outlet. Silakan coba lagi.')->withInput();
        }
    }

    public function destroy(Outlet $outlet)
    {
        $outlet->update(['aktif' => false]);
        return back()->with('success', 'Outlet dinonaktifkan.');
    }

    public function export()
    {
        return Excel::download(new OutletExport, 'master-outlet-' . now()->format('Y-m-d') . '.xlsx');
    }
}