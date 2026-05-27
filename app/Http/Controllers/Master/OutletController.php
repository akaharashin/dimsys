<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Traits\LogsActivity;
use App\Models\Outlet;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Master\OutletExport;

class OutletController extends Controller
{
    use LogsActivity;
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('koordinator')) {
            $wilayah = Wilayah::where('id', $user->wilayah_id)->get();
        } else {
            $wilayah = Wilayah::where('aktif', true)->orderBy('nama')->get();
        }

        $query = Outlet::with('wilayah');

        if ($user->hasRole('koordinator')) {
            $query->where('wilayah_id', $user->wilayah_id);
        }

        if ($request->filled('search')) {
            $query->where('nama', 'like', "%{$request->search}%");
        }
        if ($request->filled('wilayah_id') && !$user->hasRole('koordinator')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->filled('status')) {
            $query->where('aktif', $request->status === 'aktif');
        }

        $sort   = in_array($request->sort, ['nama', 'tipe', 'wilayah_id', 'aktif']) ? $request->sort : 'nama';
        $dir    = $request->direction === 'desc' ? 'desc' : 'asc';
        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;

        $outlet = $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();

        return view('master.outlet.index', compact('outlet', 'wilayah'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'       => 'required|string|max:100',
            'wilayah_id' => 'required|exists:wilayah,id',
            'tipe'       => 'required|in:agen,mitra,umum',
        ], [
            'nama.required'       => 'Nama outlet wajib diisi.',
            'nama.max'            => 'Nama outlet maksimal 100 karakter.',
            'wilayah_id.required' => 'Wilayah wajib dipilih.',
            'wilayah_id.exists'   => 'Wilayah yang dipilih tidak valid.',
            'tipe.required'       => 'Tipe outlet wajib dipilih.',
            'tipe.in'             => 'Tipe outlet harus berupa agen, mitra, atau umum.',
        ]);

        try {
            $outlet = Outlet::create($request->only('nama', 'wilayah_id', 'tipe'));
            $this->logActivity('create', 'Outlet', $outlet, after: $outlet->only(['id', 'nama', 'wilayah_id', 'tipe']), label: $outlet->nama);
            return back()->with('success', 'Outlet berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan outlet. Silakan coba lagi.')->withInput();
        }
    }

    public function update(Request $request, Outlet $outlet)
    {
        $user = auth()->user();

        // Aktifkan kembali outlet (tombol Aktifkan) — hanya admin_pusat
        if ($request->input('aktif') == '1' && !$request->has('nama')) {
            if (!$user->hasRole('admin_pusat')) {
                return back()->with('error', 'Hanya admin pusat yang dapat mengaktifkan outlet.');
            }
            $before = $outlet->only(['id', 'nama', 'wilayah_id', 'tipe', 'aktif']);
            $outlet->update(['aktif' => true]);
            $this->logActivity('update', 'Outlet', $outlet, before: $before, after: $outlet->fresh()->only(['id', 'nama', 'wilayah_id', 'tipe', 'aktif']), label: 'Aktifkan - ' . $outlet->nama);
            return back()->with('success', 'Outlet berhasil diaktifkan.');
        }

        if ($user->hasRole('koordinator')) {
            if ($outlet->wilayah_id !== $user->wilayah_id) {
                return back()->with('error', 'Anda tidak berhak mengubah outlet ini.');
            }

            $request->validate([
                'nama'           => 'required|string|max:100',
                'alamat_lengkap' => 'nullable|string',
                'latitude'       => 'nullable|numeric|between:-90,90',
                'longitude'      => 'nullable|numeric|between:-180,180',
            ], [
                'nama.required'     => 'Nama outlet wajib diisi.',
                'nama.max'          => 'Nama outlet maksimal 100 karakter.',
                'latitude.numeric'  => 'Latitude harus berupa angka.',
                'latitude.between'  => 'Latitude harus antara -90 dan 90.',
                'longitude.numeric' => 'Longitude harus berupa angka.',
                'longitude.between' => 'Longitude harus antara -180 dan 180.',
            ]);

            try {
                $before = $outlet->only(['id', 'nama', 'alamat_lengkap', 'latitude', 'longitude']);
                $outlet->update($request->only('nama', 'alamat_lengkap', 'latitude', 'longitude'));
                $this->logActivity('update', 'Outlet', $outlet, before: $before, after: $outlet->only(['id', 'nama', 'alamat_lengkap', 'latitude', 'longitude']), label: $outlet->nama);
                return back()->with('success', 'Outlet berhasil diperbarui.');
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal memperbarui outlet. Silakan coba lagi.')->withInput();
            }
        }

        // admin_pusat: edit semua field
        $request->validate([
            'nama'           => 'required|string|max:100',
            'wilayah_id'     => 'required|exists:wilayah,id',
            'tipe'           => 'required|in:agen,mitra,umum',
            'alamat_lengkap' => 'nullable|string',
            'latitude'       => 'nullable|numeric|between:-90,90',
            'longitude'      => 'nullable|numeric|between:-180,180',
        ], [
            'nama.required'       => 'Nama outlet wajib diisi.',
            'nama.max'            => 'Nama outlet maksimal 100 karakter.',
            'wilayah_id.required' => 'Wilayah wajib dipilih.',
            'wilayah_id.exists'   => 'Wilayah yang dipilih tidak valid.',
            'tipe.required'       => 'Tipe outlet wajib dipilih.',
            'tipe.in'             => 'Tipe outlet harus berupa agen, mitra, atau umum.',
            'latitude.numeric'    => 'Latitude harus berupa angka.',
            'latitude.between'    => 'Latitude harus antara -90 dan 90.',
            'longitude.numeric'   => 'Longitude harus berupa angka.',
            'longitude.between'   => 'Longitude harus antara -180 dan 180.',
        ]);

        try {
            $before = $outlet->only(['id', 'nama', 'wilayah_id', 'tipe', 'alamat_lengkap', 'latitude', 'longitude']);
            $outlet->update($request->only('nama', 'wilayah_id', 'tipe', 'alamat_lengkap', 'latitude', 'longitude'));
            $this->logActivity('update', 'Outlet', $outlet, before: $before, after: $outlet->only(['id', 'nama', 'wilayah_id', 'tipe']), label: $outlet->nama);
            return back()->with('success', 'Outlet berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui outlet. Silakan coba lagi.')->withInput();
        }
    }

    public function destroy(Outlet $outlet)
    {
        $before = $outlet->only(['id', 'nama', 'wilayah_id', 'tipe', 'aktif']);
        $outlet->update(['aktif' => false]);
        $this->logActivity('update', 'Outlet', $outlet, before: $before, after: $outlet->fresh()->only(['id', 'nama', 'wilayah_id', 'tipe', 'aktif']), label: 'Nonaktifkan - ' . $outlet->nama);
        return back()->with('success', 'Outlet dinonaktifkan.');
    }

    public function export()
    {
        return Excel::download(new OutletExport, 'master-outlet-' . now()->format('Y-m-d') . '.xlsx');
    }
}
