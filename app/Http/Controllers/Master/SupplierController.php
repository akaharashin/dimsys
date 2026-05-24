<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Master\SupplierExport;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $query->where('nama', 'like', "%{$request->search}%")
                ->orWhere('keterangan', 'like', "%{$request->search}%");
        }
        if ($request->filled('status')) {
            $query->where('aktif', $request->status === 'aktif');
        }

        $sort = in_array($request->sort, ['nama']) ? $request->sort : 'nama';
        $dir = $request->dir === 'desc' ? 'desc' : 'asc';
        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;

        $supplier = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        return view('master.supplier.index', compact('supplier'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nama.required' => 'Nama supplier wajib diisi.',
            'nama.max' => 'Nama supplier maksimal 100 karakter.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
        ]);

        try {
            Supplier::create($request->only('nama', 'keterangan'));
            return back()->with('success', 'Supplier berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan supplier. Silakan coba lagi.')->withInput();
        }
    }

    public function update(Request $request, Supplier $supplier)
    {
        if ($request->input('aktif') == '1' && !$request->has('nama')) {
            $supplier->update(['aktif' => true]);
            return back()->with('success', 'Supplier berhasil diaktifkan.');
        }
        $request->validate([
            'nama' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nama.required' => 'Nama supplier wajib diisi.',
            'nama.max' => 'Nama supplier maksimal 100 karakter.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
        ]);

        try {
            $supplier->update($request->only('nama', 'keterangan'));
            return back()->with('success', 'Supplier berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui supplier. Silakan coba lagi.')->withInput();
        }
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->update(['aktif' => false]);
        return back()->with('success', 'Supplier dinonaktifkan.');
    }

    public function export()
    {
        return Excel::download(new SupplierExport, 'master-supplier-' . now()->format('Y-m-d') . '.xlsx');
    }
}