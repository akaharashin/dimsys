<?php
namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Traits\LogsActivity;
use App\Models\User;
use App\Models\Wilayah;
use App\Exports\Master\UserExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $wilayahList = Wilayah::where('aktif', true)->orderBy('nama')->get();

        $sort = in_array($request->sort, ['name', 'username', 'role', 'created_at']) ? $request->sort : 'created_at';
        $dir  = $request->direction === 'asc' ? 'asc' : 'desc';

        $query = User::with('wilayah')->withTrashed()->orderBy($sort, $dir);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('username', 'like', "%{$s}%");
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }
        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->whereNull('deleted_at');
            } elseif ($request->status === 'nonaktif') {
                $query->whereNotNull('deleted_at');
            }
        }

        $perPage = in_array($request->per_page, [10, 25, 50, 100]) ? $request->per_page : 25;
        $users = $query->paginate($perPage)->withQueryString();

        return view('master.user.index', compact('users', 'wilayahList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'username'   => 'required|string|max:50|alpha_dash|unique:users,username',
            'email'      => 'nullable|email|max:100|unique:users,email',
            'no_hp'      => 'nullable|string|max:20',
            'password'   => 'required|string|min:6',
            'role'       => 'required|in:owner,admin_pusat,koordinator',
            'wilayah_id' => 'required_if:role,koordinator|nullable|exists:wilayah,id',
        ], [
            'name.required'       => 'Nama wajib diisi.',
            'name.max'            => 'Nama maksimal 100 karakter.',
            'username.required'   => 'Username wajib diisi.',
            'username.max'        => 'Username maksimal 50 karakter.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, underscore, dan dash.',
            'username.unique'     => 'Username sudah digunakan.',
            'email.email'         => 'Format email tidak valid.',
            'email.unique'        => 'Email sudah digunakan.',
            'no_hp.max'           => 'No HP maksimal 20 karakter.',
            'password.required'   => 'Password wajib diisi.',
            'password.min'        => 'Password minimal 6 karakter.',
            'role.required'       => 'Role wajib dipilih.',
            'role.in'             => 'Role tidak valid.',
            'wilayah_id.required_if' => 'Wilayah wajib dipilih untuk role koordinator.',
            'wilayah_id.exists'   => 'Wilayah yang dipilih tidak valid.',
        ]);

        try {
            $user = User::create([
                'name'       => $request->name,
                'username'   => $request->username,
                'email'      => $request->email,
                'no_hp'      => $request->no_hp,
                'password'   => Hash::make($request->password),
            ]);

            // role & wilayah_id tidak fillable — set eksplisit (non mass-assign).
            $user->role       = $request->role;
            $user->wilayah_id = $request->role === 'koordinator' ? $request->wilayah_id : null;
            $user->save();

            $user->assignRole($request->role);

            $this->logActivity(
                'create', 'User', $user,
                after: $user->only(['id', 'name', 'username', 'email', 'role', 'wilayah_id']),
                label: 'User ' . $user->name . ' (' . $user->username . ')'
            );

            return redirect()->route('master.user.index')->with('success', 'User berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan user. Silakan coba lagi.')->withInput();
        }
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'username'   => 'required|string|max:50|alpha_dash|unique:users,username,' . $user->id,
            'email'      => 'nullable|email|max:100|unique:users,email,' . $user->id,
            'no_hp'      => 'nullable|string|max:20',
            'password'   => 'nullable|string|min:6',
            'role'       => 'required|in:owner,admin_pusat,koordinator',
            'wilayah_id' => 'required_if:role,koordinator|nullable|exists:wilayah,id',
        ], [
            'name.required'       => 'Nama wajib diisi.',
            'name.max'            => 'Nama maksimal 100 karakter.',
            'username.required'   => 'Username wajib diisi.',
            'username.max'        => 'Username maksimal 50 karakter.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, underscore, dan dash.',
            'username.unique'     => 'Username sudah digunakan.',
            'email.email'         => 'Format email tidak valid.',
            'email.unique'        => 'Email sudah digunakan.',
            'no_hp.max'           => 'No HP maksimal 20 karakter.',
            'password.min'        => 'Password minimal 6 karakter.',
            'role.required'       => 'Role wajib dipilih.',
            'role.in'             => 'Role tidak valid.',
            'wilayah_id.required_if' => 'Wilayah wajib dipilih untuk role koordinator.',
            'wilayah_id.exists'   => 'Wilayah yang dipilih tidak valid.',
        ]);

        try {
            $before = $user->only(['id', 'name', 'username', 'email', 'no_hp', 'role', 'wilayah_id']);

            $data = [
                'name'       => $request->name,
                'username'   => $request->username,
                'email'      => $request->email,
                'no_hp'      => $request->no_hp,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            // role & wilayah_id tidak fillable — set eksplisit (non mass-assign).
            $user->role       = $request->role;
            $user->wilayah_id = $request->role === 'koordinator' ? $request->wilayah_id : null;
            $user->save();

            $user->syncRoles([$request->role]);

            $this->logActivity(
                'update', 'User', $user,
                before: $before,
                after: $user->fresh()->only(['id', 'name', 'username', 'email', 'no_hp', 'role', 'wilayah_id']),
                label: 'User ' . $user->name . ' (' . $user->username . ')'
            );

            return redirect()->route('master.user.index')->with('success', 'User berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui user. Silakan coba lagi.')->withInput();
        }
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required'  => 'Password baru wajib diisi.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        try {
            $user->update(['password' => Hash::make($request->password)]);

            $this->logActivity(
                'update', 'User', $user,
                label: 'Reset Password - ' . $user->name
            );

            return redirect()->route('master.user.index')->with('success', 'Password user berhasil direset.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mereset password. Silakan coba lagi.');
        }
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        if ($user->hasRole('owner')) {
            return back()->with('error', 'Akun Owner tidak dapat dinonaktifkan.');
        }

        if ($user->hasRole('admin_pusat')) {
            $sisaAdmin = User::role('admin_pusat')
                ->where('id', '!=', $user->id)
                ->count();
            if ($sisaAdmin === 0) {
                return back()->with('error', 'Tidak dapat menonaktifkan Admin Pusat terakhir.');
            }
        }

        $this->logActivity(
            'delete', 'User', $user,
            before: $user->only(['id', 'name', 'username', 'email', 'role', 'wilayah_id']),
            label: 'Nonaktifkan - ' . $user->name . ' (' . $user->username . ')'
        );

        $user->delete();

        return back()->with('success', 'User berhasil dinonaktifkan.');
    }

    public function restore(string $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        $this->logActivity(
            'update', 'User', $user,
            label: 'Aktifkan - ' . $user->name . ' (' . $user->username . ')'
        );

        return back()->with('success', 'User berhasil diaktifkan kembali.');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'role', 'wilayah_id', 'status']);
        $filename = 'master-user-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new UserExport($filters), $filename);
    }
}
