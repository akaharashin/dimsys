@extends('layouts.app')
@section('title', 'Master User')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Master User</h2>
        <button onclick="openTambah()"
            class="bg-red-700 hover:bg-red-800 text-white text-sm px-4 py-2 rounded-lg">
            + Tambah User
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('master.user.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1" style="min-width:200px">
                <label class="block text-xs text-gray-500 mb-1">Cari (nama / username)</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau username..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Role</label>
                <select name="role"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                    style="min-width:140px">
                    <option value="">Semua</option>
                    <option value="owner" {{ request('role') == 'owner' ? 'selected' : '' }}>Owner</option>
                    <option value="admin_pusat" {{ request('role') == 'admin_pusat' ? 'selected' : '' }}>Admin Pusat</option>
                    <option value="koordinator" {{ request('role') == 'koordinator' ? 'selected' : '' }}>Koordinator</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Wilayah</label>
                <select name="wilayah_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                    style="min-width:140px">
                    <option value="">Semua</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ $w->id }}" {{ request('wilayah_id') == $w->id ? 'selected' : '' }}>{{ $w->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    <option value="">Semua</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Per Halaman</label>
                <select name="per_page"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" style="min-width:60px">
                    @foreach([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" {{ request('per_page', 25) == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg">Filter</button>
                <a href="{{ route('master.user.index') }}"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">Reset</a>
                <a href="{{ route('master.user.export', request()->query()) }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg"><i class="fa-solid fa-file-excel mr-1"></i> Export</a>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
        <span>Menampilkan {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} user</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-gray-500 uppercase text-xs" style="position:sticky;top:0;background:#f9fafb;z-index:10;">
                @php
                    function sortUrlUser($col) {
                        $d = request('sort') === $col && request('direction') === 'asc' ? 'desc' : 'asc';
                        return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $d]);
                    }
                    function sortIconUser($col) {
                        if (request('sort') !== $col) return 'fa-sort text-gray-300';
                        return request('direction') === 'asc' ? 'fa-sort-up text-red-700' : 'fa-sort-down text-red-700';
                    }
                @endphp
                <tr>
                    <th class="px-5 py-3 text-center w-12">No</th>
                    <th class="px-5 py-3 text-left">
                        <a href="{{ sortUrlUser('name') }}" class="flex items-center gap-1 hover:text-red-700 transition-colors">
                            Nama <i class="fa-solid {{ sortIconUser('name') }} text-xs"></i>
                        </a>
                    </th>
                    <th class="px-5 py-3 text-left">Kontak</th>
                    <th class="px-12 py-3 text-left">
                        <a href="{{ sortUrlUser('role') }}" class="flex items-center gap-1 hover:text-red-700 transition-colors">
                            Role <i class="fa-solid {{ sortIconUser('role') }} text-xs"></i>
                        </a>
                    </th>
                    <th class="px-5 py-3 text-left">Wilayah</th>
                    <th class="px-5 py-3 text-left">
                        <a href="{{ sortUrlUser('created_at') }}" class="flex items-center gap-1 hover:text-red-700 transition-colors">
                            Dibuat <i class="fa-solid {{ sortIconUser('created_at') }} text-xs"></i>
                        </a>
                    </th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php
                    $adminPusatAktifCount = \App\Models\User::role('admin_pusat')->count();
                @endphp
                @forelse($users as $u)
                    @php
                        $roleClass = [
                            'owner' => 'text-amber-800 border border-amber-300',
                            'admin_pusat' => 'bg-red-100 border border-red-200',
                            'koordinator' => 'bg-gray-100 text-gray-700 border border-gray-200',
                        ][$u->role] ?? 'bg-gray-100 text-gray-600';
                        $roleStyle = $u->role === 'owner' ? 'background-color:#FFF9C4'
                                   : ($u->role === 'admin_pusat' ? 'color:#A51616' : '');
                        $roleLabel = ucfirst(str_replace('_', ' ', $u->role));
                        $nonaktif = $u->trashed();
                        $userEditData = json_encode([
                            'id'         => $u->id,
                            'name'       => $u->name,
                            'username'   => $u->username,
                            'email'      => $u->email ?? '',
                            'no_hp'      => $u->no_hp ?? '',
                            'role'       => $u->role,
                            'wilayah_id' => $u->wilayah_id ?? '',
                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                        $userResetData = json_encode([
                            'id'   => $u->id,
                            'name' => $u->name,
                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $nonaktif ? 'opacity-60' : '' }}">
                        <td class="px-5 py-3 text-center text-gray-400 text-xs">
                            {{ $users->firstItem() + $loop->index }}
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-gray-700">{{ $u->name }}</div>
                            <div class="text-xs text-gray-400">{{ $u->username ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="text-gray-600 text-xs">{{ $u->email ?? '-' }}</div>
                            <div class="text-xs text-gray-400">{{ $u->no_hp ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $roleClass }}" @if($roleStyle) style="{{ $roleStyle }}" @endif>
                                {{ $roleLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $u->wilayah->nama ?? '-' }}</td>
                        <td class="px-5 py-3 text-gray-500 text-xs">
                            {{ $u->created_at?->locale('id')->isoFormat('D MMM Y') }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $nonaktif ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                {{ $nonaktif ? 'Nonaktif' : 'Aktif' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex flex-wrap gap-2">
                                @if(!$nonaktif)
                                    <button onclick="openEdit({{ $userEditData }})"
                                        class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 rounded-md text-amber-700 font-medium">
                                        <i class="fa-solid fa-pencil text-xs"></i> Edit
                                    </button>
                                    <button onclick="openReset({{ $userResetData }})"
                                        class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md text-gray-600 font-medium">
                                        <i class="fa-solid fa-key text-xs"></i> Reset Password
                                    </button>
                                    @if(
                                        !$u->hasRole('owner') &&
                                        !($u->hasRole('admin_pusat') && $adminPusatAktifCount <= 1) &&
                                        $u->id !== auth()->id()
                                    )
                                        <form method="POST" action="{{ route('master.user.destroy', $u) }}"
                                            data-confirm="Yakin ingin menonaktifkan user {{ $u->name }}?">
                                            @csrf @method('DELETE')
                                            <button
                                                class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-red-50 hover:bg-red-100 rounded-md text-red-600 font-medium">
                                                <i class="fa-solid fa-ban text-xs"></i> Nonaktifkan
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <form method="POST" action="{{ route('master.user.restore', $u->id) }}">
                                        @csrf
                                        <button
                                            class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-green-50 hover:bg-green-100 rounded-md text-green-600 font-medium">
                                            <i class="fa-solid fa-check text-xs"></i> Aktifkan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                            @if(request('search')) Tidak ada user dengan kata kunci "{{ request('search') }}".
                            @else Belum ada data user. @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">Halaman {{ $users->currentPage() }} dari {{ $users->lastPage() }}</div>
            <div>{{ $users->links() }}</div>
        </div>
    @endif

    {{-- ─── Modal Tambah ─── --}}
    <div id="modal-tambah"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:9999;">
        <div style="background:white;border-radius:12px;padding:24px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Tambah User</h3>
            <form method="POST" action="{{ route('master.user.store') }}" id="form-tambah">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Nama Lengkap</label>
                        <input type="text" name="name" required maxlength="100"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Username</label>
                        <input type="text" name="username" required maxlength="50" pattern="[A-Za-z0-9_\-\.]+"
                            title="Hanya huruf, angka, underscore, dash, dan titik"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">No HP <span class="text-gray-400 text-xs">(opsional)</span></label>
                        <input type="text" name="no_hp" maxlength="20"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Email <span class="text-gray-400 text-xs">(opsional)</span></label>
                        <input type="email" name="email" maxlength="100"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Password</label>
                        <input type="password" name="password" required minlength="6"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Role</label>
                        <select name="role" id="tambah-role" required onchange="toggleWilayah('tambah')"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                            <option value="koordinator">Koordinator</option>
                            <option value="admin_pusat">Admin Pusat</option>
                            <option value="owner">Owner</option>
                        </select>
                    </div>
                    <div class="md:col-span-2" id="tambah-wilayah-wrap">
                        <label class="block text-sm text-gray-600 mb-1">Wilayah</label>
                        <select name="wilayah_id" id="tambah-wilayah"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                            <option value="">-- Pilih Wilayah --</option>
                            @foreach($wilayahList as $w)
                                <option value="{{ $w->id }}">{{ $w->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" onclick="closeModal('modal-tambah')"
                        class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── Modal Edit ─── --}}
    <div id="modal-edit"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:9999;">
        <div style="background:white;border-radius:12px;padding:24px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 class="text-lg font-semibold text-gray-700 mb-1">Edit User</h3>
            <p class="text-xs text-gray-400 mb-4" id="edit-username-info">Username: -</p>
            <form id="form-edit" method="POST" action="">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Nama Lengkap</label>
                        <input type="text" id="edit-name" name="name" required maxlength="100"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Username</label>
                        <input type="text" id="edit-username" name="username" required maxlength="50" pattern="[A-Za-z0-9_\-\.]+"
                            title="Hanya huruf, angka, underscore, dash, dan titik"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">No HP <span class="text-gray-400 text-xs">(opsional)</span></label>
                        <input type="text" id="edit-no-hp" name="no_hp" maxlength="20"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Email <span class="text-gray-400 text-xs">(opsional)</span></label>
                        <input type="email" id="edit-email" name="email" maxlength="100"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm text-gray-600 mb-1">Password</label>
                        <input type="password" name="password" minlength="6"
                            placeholder="Kosongkan jika tidak ingin mengubah"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Role</label>
                        <select name="role" id="edit-role" required onchange="toggleWilayah('edit')"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                            <option value="koordinator">Koordinator</option>
                            <option value="admin_pusat">Admin Pusat</option>
                            <option value="owner">Owner</option>
                        </select>
                    </div>
                    <div id="edit-wilayah-wrap">
                        <label class="block text-sm text-gray-600 mb-1">Wilayah</label>
                        <select name="wilayah_id" id="edit-wilayah"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                            <option value="">-- Pilih Wilayah --</option>
                            @foreach($wilayahList as $w)
                                <option value="{{ $w->id }}">{{ $w->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-5">
                    <button type="button" onclick="closeModal('modal-edit')"
                        class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ─── Modal Reset Password ─── --}}
    <div id="modal-reset"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:9999;">
        <div style="background:white;border-radius:12px;padding:24px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 class="text-lg font-semibold text-gray-700 mb-1">Reset Password</h3>
            <p class="text-xs text-gray-400 mb-4" id="reset-user-info">— -</p>
            <form id="form-reset" method="POST" action="">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Password Baru</label>
                    <input type="password" name="password" required minlength="6"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required minlength="6"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal('modal-reset')"
                        class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg">Reset Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        var resetBaseUrl = "{{ url('master/user') }}";

        function openTambah() {
            document.getElementById('form-tambah').reset();
            document.getElementById('tambah-role').value = 'koordinator';
            toggleWilayah('tambah');
            document.getElementById('modal-tambah').style.display = 'flex';
        }

        function openEdit(data) {
            document.getElementById('form-edit').action = resetBaseUrl + '/' + data.id;
            document.getElementById('edit-name').value = data.name ?? '';
            document.getElementById('edit-username').value = data.username ?? '';
            document.getElementById('edit-email').value = data.email ?? '';
            document.getElementById('edit-no-hp').value = data.no_hp ?? '';
            document.getElementById('edit-role').value = data.role ?? 'koordinator';
            document.getElementById('edit-wilayah').value = data.wilayah_id ?? '';
            document.getElementById('edit-username-info').textContent = 'Username: ' + (data.username ?? '-');
            toggleWilayah('edit');
            document.getElementById('modal-edit').style.display = 'flex';
        }

        function openReset(data) {
            document.getElementById('form-reset').reset();
            document.getElementById('form-reset').action = resetBaseUrl + '/' + data.id + '/reset-password';
            document.getElementById('reset-user-info').textContent = 'Reset password untuk: ' + data.name;
            document.getElementById('modal-reset').style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function toggleWilayah(prefix) {
            var role = document.getElementById(prefix + '-role').value;
            var wrap = document.getElementById(prefix + '-wilayah-wrap');
            var sel  = document.getElementById(prefix + '-wilayah');
            if (role === 'koordinator') {
                wrap.style.display = '';
                sel.required = true;
            } else {
                wrap.style.display = 'none';
                sel.required = false;
                sel.value = '';
            }
        }

        // Init initial state
        toggleWilayah('tambah');
        toggleWilayah('edit');
    </script>

@endsection
