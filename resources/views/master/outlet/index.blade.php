@extends('layouts.app')
@section('title', 'Master Outlet')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Master Outlet</h2>
        @if(auth()->user()->hasRole('admin_pusat'))
        <button onclick="document.getElementById('modal-tambah').style.display='flex'"
            class="bg-red-700 hover:bg-red-800 text-white text-sm px-4 py-2 rounded-lg">
            + Tambah Outlet
        </button>
        @endif
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('master.outlet.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1" style="min-width:200px">
                <label class="block text-xs text-gray-500 mb-1">Cari Outlet</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama outlet..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
            </div>
            @if(!auth()->user()->hasRole('koordinator'))
            <div>
                <label class="block text-xs text-gray-500 mb-1">Wilayah</label>
                <select name="wilayah_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                    style="min-width:140px">
                    <option value="">Semua</option>
                    @foreach($wilayah as $w)
                        <option value="{{ $w->id }}" {{ request('wilayah_id') == $w->id ? 'selected' : '' }}>{{ $w->nama }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tipe</label>
                <select name="tipe"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    <option value="">Semua</option>
                    <option value="agen" {{ request('tipe') == 'agen' ? 'selected' : '' }}>Agen</option>
                    <option value="mitra" {{ request('tipe') == 'mitra' ? 'selected' : '' }}>Mitra</option>
                    <option value="umum" {{ request('tipe') == 'umum' ? 'selected' : '' }}>Umum</option>
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
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                    style="min-width:60px">
                        @foreach([10, 25, 50, 100] as $n)
                            <option value=" {{ $n }}" {{ request('per_page', 25) == $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg">Filter</button>
                <a href="{{ route('master.outlet.index') }}"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">Reset</a>
                @if(auth()->user()->hasRole('admin_pusat'))
                <a href="{{ route('master.outlet.export') }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg"><i class="fa-solid fa-file-excel mr-1"></i> Export</a>
                @endif
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
        <span>Menampilkan {{ $outlet->firstItem() }}-{{ $outlet->lastItem() }} dari {{ $outlet->total() }} outlet</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-gray-500 uppercase text-xs" style="position:sticky;top:0;background:#f9fafb;z-index:10;">
                @php
                    function sortUrl($col) {
                        $d = request('sort') === $col && request('direction') === 'asc' ? 'desc' : 'asc';
                        return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $d]);
                    }
                    function sortIcon($col) {
                        if (request('sort') !== $col) return 'fa-sort text-gray-300';
                        return request('direction') === 'asc' ? 'fa-sort-up text-red-700' : 'fa-sort-down text-red-700';
                    }
                @endphp
                <tr>
                    <th class="px-4 py-3 text-center w-12">No</th>
                    <th class="px-4 py-3 text-left">
                        <a href="{{ sortUrl('nama') }}" class="flex items-center gap-1 hover:text-red-700 transition-colors">
                            Nama <i class="fa-solid {{ sortIcon('nama') }} text-xs"></i>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <a href="{{ sortUrl('wilayah_id') }}" class="flex items-center gap-1 hover:text-red-700 transition-colors">
                            Wilayah <i class="fa-solid {{ sortIcon('wilayah_id') }} text-xs"></i>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <a href="{{ sortUrl('tipe') }}" class="flex items-center gap-1 hover:text-red-700 transition-colors">
                            Tipe <i class="fa-solid {{ sortIcon('tipe') }} text-xs"></i>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left">Alamat</th>
                    <th class="px-4 py-3 text-left">
                        <a href="{{ sortUrl('aktif') }}" class="flex items-center gap-1 hover:text-red-700 transition-colors">
                            Status <i class="fa-solid {{ sortIcon('aktif') }} text-xs"></i>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($outlet as $o)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-center text-gray-400 text-xs">
                                {{ $outlet->firstItem() + $loop->index }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $o->nama }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $o->wilayah->nama }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs
                                                {{ $o->tipe === 'agen' ? 'bg-purple-100 text-purple-600' :
                    ($o->tipe === 'mitra' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600') }}">
                                    {{ ucfirst($o->tipe) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs" style="max-width:200px">
                                {{ $o->alamat_lengkap ? Str::limit($o->alamat_lengkap, 50) : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-2 py-1 rounded-full text-xs {{ $o->aktif ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                    {{ $o->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 flex gap-2">
                                @php
                                    $userAksi = auth()->user();
                                    $bolehEdit = $userAksi->hasRole('admin_pusat') ||
                                        ($userAksi->hasRole('koordinator') && $o->wilayah_id === $userAksi->wilayah_id);
                                @endphp
                                @if($bolehEdit)
                                    <button onclick="openEdit({{ $o->toJson() }})"
                                        class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 rounded-md text-amber-700 font-medium"><i class="fa-solid fa-pencil text-xs"></i> Edit</button>
                                @endif
                                @if(auth()->user()->hasRole('admin_pusat'))
                                    @if($o->aktif)
                                        <form method="POST" action="{{ route('master.outlet.destroy', $o) }}"
                                            data-confirm="Yakin ingin menonaktifkan outlet {{ $o->nama }}?">
                                            @csrf @method('DELETE')
                                            <button
                                                class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-red-50 hover:bg-red-100 rounded-md text-red-600 font-medium"><i class="fa-solid fa-ban text-xs"></i> Nonaktifkan</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('master.outlet.update', $o) }}">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="aktif" value="1">
                                            <button
                                                class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-green-50 hover:bg-green-100 rounded-md text-green-600 font-medium"><i class="fa-solid fa-check text-xs"></i> Aktifkan</button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                            @if(request('search')) Tidak ada outlet dengan kata kunci "{{ request('search') }}"
                            @else Belum ada data outlet. @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($outlet->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">Halaman {{ $outlet->currentPage() }} dari {{ $outlet->lastPage() }}</div>
            <div>{{ $outlet->links() }}</div>
        </div>
    @endif

    @if(auth()->user()->hasRole('admin_pusat'))
    {{-- Modal Tambah --}}
    <div id="modal-tambah"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:9999;">
        <div
            style="background:white;border-radius:12px;padding:24px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Tambah Outlet</h3>
            <form method="POST" action="{{ route('master.outlet.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Nama Outlet</label>
                    <input type="text" name="nama" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Wilayah</label>
                    <select name="wilayah_id" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        <option value="">-- Pilih Wilayah --</option>
                        @foreach($wilayah as $w)
                            <option value="{{ $w->id }}">{{ $w->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Tipe</label>
                    <select name="tipe"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        <option value="mitra">Mitra</option>
                        <option value="agen">Agen</option>
                        <option value="umum">Umum</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-tambah').style.display='none'"
                        class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Modal Edit --}}
    @if(auth()->user()->hasRole('admin_pusat') || auth()->user()->hasRole('koordinator'))
    <div id="modal-edit"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:9999;">
        <div
            style="background:white;border-radius:12px;padding:24px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Edit Outlet</h3>
            <form id="form-edit" method="POST" action="">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Nama Outlet</label>
                    <input type="text" id="edit-nama" name="nama" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>

                @if(auth()->user()->hasRole('admin_pusat'))
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Wilayah</label>
                    <select id="edit-wilayah_id" name="wilayah_id" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        @foreach($wilayah as $w)
                            <option value="{{ $w->id }}">{{ $w->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Tipe</label>
                    <select id="edit-tipe" name="tipe"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        <option value="mitra">Mitra</option>
                        <option value="agen">Agen</option>
                        <option value="umum">Umum</option>
                    </select>
                </div>
                @else
                {{-- Koordinator: wilayah & tipe read-only --}}
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Wilayah</label>
                    <p id="edit-wilayah-label" class="px-3 py-2 text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-lg"></p>
                </div>
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Tipe</label>
                    <p id="edit-tipe-label" class="px-3 py-2 text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-lg"></p>
                </div>
                @endif

                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Alamat Lengkap</label>
                    <textarea id="edit-alamat_lengkap" name="alamat_lengkap" rows="3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                        placeholder="Isi alamat lengkap outlet..."></textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Latitude</label>
                        <input type="number" id="edit-latitude" name="latitude" step="any"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                            placeholder="-6.12345678">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Longitude</label>
                        <input type="number" id="edit-longitude" name="longitude" step="any"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                            placeholder="106.12345678">
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-edit').style.display='none'"
                        class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        var isAdminPusat = {{ auth()->user()->hasRole('admin_pusat') ? 'true' : 'false' }};

        function openEdit(data) {
            document.getElementById('edit-nama').value = data.nama;
            document.getElementById('edit-alamat_lengkap').value = data.alamat_lengkap || '';
            document.getElementById('edit-latitude').value  = data.latitude  || '';
            document.getElementById('edit-longitude').value = data.longitude || '';

            if (isAdminPusat) {
                document.getElementById('edit-wilayah_id').value = data.wilayah_id;
                document.getElementById('edit-tipe').value = data.tipe;
            } else {
                var wilayahLabel = document.getElementById('edit-wilayah-label');
                var tipeLabel    = document.getElementById('edit-tipe-label');
                if (wilayahLabel) wilayahLabel.textContent = data.wilayah ? data.wilayah.nama : data.wilayah_id;
                if (tipeLabel)    tipeLabel.textContent    = data.tipe.charAt(0).toUpperCase() + data.tipe.slice(1);
            }

            document.getElementById('form-edit').action = `{{ route('master.outlet.index') }}/${data.id}`;
            document.getElementById('modal-edit').style.display = 'flex';
        }
    </script>
    @endif

@endsection
