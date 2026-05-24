<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Master\ProdukExport;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::query();

        if ($request->filled('search')) {
            $query->where('nama', 'like', "%{$request->search}%");
        }
        if ($request->filled('status')) {
            $query->where('aktif', $request->status === 'aktif');
        }

        $sort = in_array($request->sort, ['nama', 'hpp', 'harga_jual', 'harga_agen', 'komisi']) ? $request->sort : 'nama';
        $dir = $request->dir === 'desc' ? 'desc' : 'asc';
        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;

        $produk = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        return view('master.produk.index', compact('produk'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'hpp' => 'required|numeric|min:0',
            'harga_mitra' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'harga_umum' => 'required|numeric|min:0',
            'harga_agen' => 'required|numeric|min:0',
            'komisi' => 'required|numeric|min:0',
        ], [
            'nama.required' => 'Nama produk wajib diisi.',
            'nama.max' => 'Nama produk maksimal 100 karakter.',
            'hpp.required' => 'HPP (Harga Pokok Penjualan) wajib diisi.',
            'hpp.numeric' => 'HPP harus berupa angka.',
            'hpp.min' => 'HPP tidak boleh bernilai negatif.',
            'harga_mitra.required' => 'Harga mitra wajib diisi.',
            'harga_mitra.numeric' => 'Harga mitra harus berupa angka.',
            'harga_mitra.min' => 'Harga mitra tidak boleh bernilai negatif.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'harga_jual.numeric' => 'Harga jual harus berupa angka.',
            'harga_jual.min' => 'Harga jual tidak boleh bernilai negatif.',
            'harga_umum.required' => 'Harga umum wajib diisi.',
            'harga_umum.numeric' => 'Harga umum harus berupa angka.',
            'harga_umum.min' => 'Harga umum tidak boleh bernilai negatif.',
            'harga_agen.required' => 'Harga agen wajib diisi.',
            'harga_agen.numeric' => 'Harga agen harus berupa angka.',
            'harga_agen.min' => 'Harga agen tidak boleh bernilai negatif.',
            'komisi.required' => 'Komisi wajib diisi.',
            'komisi.numeric' => 'Komisi harus berupa angka.',
            'komisi.min' => 'Komisi tidak boleh bernilai negatif.',
        ]);

        try {
            Produk::create($request->only('nama', 'hpp', 'harga_mitra', 'harga_jual', 'harga_umum', 'harga_agen', 'komisi'));
            return back()->with('success', 'Produk berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan produk. Silakan coba lagi.')->withInput();
        }
    }

    public function update(Request $request, Produk $produk)
    {
        if ($request->input('aktif') == '1' && !$request->has('hpp')) {
            $produk->update(['aktif' => true]);
            return back()->with('success', 'Produk berhasil diaktifkan.');
        }
        $request->validate([
            'nama' => 'required|string|max:100',
            'hpp' => 'required|numeric|min:0',
            'harga_mitra' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'harga_umum' => 'required|numeric|min:0',
            'harga_agen' => 'required|numeric|min:0',
            'komisi' => 'required|numeric|min:0',
        ], [
            'nama.required' => 'Nama produk wajib diisi.',
            'nama.max' => 'Nama produk maksimal 100 karakter.',
            'hpp.required' => 'HPP (Harga Pokok Penjualan) wajib diisi.',
            'hpp.numeric' => 'HPP harus berupa angka.',
            'hpp.min' => 'HPP tidak boleh bernilai negatif.',
            'harga_mitra.required' => 'Harga mitra wajib diisi.',
            'harga_mitra.numeric' => 'Harga mitra harus berupa angka.',
            'harga_mitra.min' => 'Harga mitra tidak boleh bernilai negatif.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'harga_jual.numeric' => 'Harga jual harus berupa angka.',
            'harga_jual.min' => 'Harga jual tidak boleh bernilai negatif.',
            'harga_umum.required' => 'Harga umum wajib diisi.',
            'harga_umum.numeric' => 'Harga umum harus berupa angka.',
            'harga_umum.min' => 'Harga umum tidak boleh bernilai negatif.',
            'harga_agen.required' => 'Harga agen wajib diisi.',
            'harga_agen.numeric' => 'Harga agen harus berupa angka.',
            'harga_agen.min' => 'Harga agen tidak boleh bernilai negatif.',
            'komisi.required' => 'Komisi wajib diisi.',
            'komisi.numeric' => 'Komisi harus berupa angka.',
            'komisi.min' => 'Komisi tidak boleh bernilai negatif.',
        ]);

        try {
            $produk->update($request->only('nama', 'hpp', 'harga_mitra', 'harga_jual', 'harga_umum', 'harga_agen', 'komisi'));
            return back()->with('success', 'Produk berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui produk. Silakan coba lagi.')->withInput();
        }
    }

    public function destroy(Produk $produk)
    {
        $produk->update(['aktif' => false]);
        return back()->with('success', 'Produk dinonaktifkan.');
    }

    public function export()
    {
        return Excel::download(new ProdukExport, 'master-produk-' . now()->format('Y-m-d') . '.xlsx');
    }
}