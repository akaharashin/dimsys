@extends('layouts.app')
@section('title', 'Master Wilayah')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Master Wilayah</h2>
        <button onclick="document.getElementById('modal-tambah').style.display='flex'"
            class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg">
            + Tambah Wilayah
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('master.wilayah.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1" style="min-width:200px">
                <label class="block text-xs text-gray-500 mb-1">Cari Wilayah</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama wilayah..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tipe</label>
                <select name="tipe"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                    <option value="">Semua</option>
                    <option value="pusat" {{ request('tipe') == 'pusat' ? 'selected' : '' }}>Pusat</option>
                    <option value="cabang" {{ request('tipe') == 'cabang' ? 'selected' : '' }}>Cabang</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                    <option value="">Semua</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Per Halaman</label>
                <select name="per_page"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300" style="min-width:60px">
                    @foreach([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" {{ request('per_page', 25) == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg">Filter</button>
                <a href="{{ route('master.wilayah.index') }}"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">Reset</a>
                <a href="{{ route('master.wilayah.export') }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg"><i class="fa-solid fa-file-excel mr-1"></i> Export</a>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
        <span>Menampilkan {{ $wilayah->firstItem() }}-{{ $wilayah->lastItem() }} dari {{ $wilayah->total() }} wilayah</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-gray-500 uppercase text-xs" style="position:sticky;top:0;background:#f9fafb;z-index:10;">
                <tr>
                    <th class="px-5 py-3 text-center w-12">No</th>
                    <th class="px-5 py-3 text-left">Nama</th>
                    <th class="px-5 py-3 text-left">Tipe</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($wilayah as $w)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-center text-gray-400 text-xs">
                            {{ $wilayah->firstItem() + $loop->index }}
                        </td>
                        <td class="px-5 py-3 font-medium text-gray-700">{{ $w->nama }}</td>
                        <td class="px-5 py-3">
                            <span
                                class="px-2 py-1 rounded-full text-xs {{ $w->tipe === 'pusat' ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600' }}">
                                {{ ucfirst($w->tipe) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span
                                class="px-2 py-1 rounded-full text-xs {{ $w->aktif ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                {{ $w->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 flex gap-2">
                            <button onclick="openEdit('{{ $w->id }}','{{ $w->nama }}','{{ $w->tipe }}')"
                                class="text-xs px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600">Edit</button>
                            @if($w->aktif)
                                <form method="POST" action="{{ route('master.wilayah.destroy', $w) }}"
                                    data-confirm="Yakin ingin menonaktifkan wilayah {{ $w->nama }}?">
                                    @csrf @method('DELETE')
                                    <button
                                        class="text-xs px-3 py-1 bg-red-50 hover:bg-red-100 rounded-lg text-red-500">Nonaktifkan</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('master.wilayah.update', $w) }}">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="aktif" value="1">
                                    <button
                                        class="text-xs px-3 py-1 bg-green-50 hover:bg-green-100 rounded-lg text-green-600">Aktifkan</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                            @if(request('search')) Tidak ada wilayah dengan kata kunci "{{ request('search') }}"
                            @else Belum ada data wilayah. @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($wilayah->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">Halaman {{ $wilayah->currentPage() }} dari {{ $wilayah->lastPage() }}</div>
            <div>{{ $wilayah->links() }}</div>
        </div>
    @endif

    {{-- Modal Tambah --}}
    <div id="modal-tambah"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:9999;">
        <div
            style="background:white;border-radius:12px;padding:24px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Tambah Wilayah</h3>
            <form method="POST" action="{{ route('master.wilayah.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Nama Wilayah</label>
                    <input type="text" name="nama" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1">Tipe</label>
                    <select name="tipe"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                        <option value="cabang">Cabang</option>
                        <option value="pusat">Pusat</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-tambah').style.display='none'"
                        class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div id="modal-edit"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:9999;">
        <div
            style="background:white;border-radius:12px;padding:24px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Edit Wilayah</h3>
            <form id="form-edit" method="POST" action="">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Nama Wilayah</label>
                    <input type="text" id="edit-nama" name="nama" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1">Tipe</label>
                    <select id="edit-tipe" name="tipe"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                        <option value="cabang">Cabang</option>
                        <option value="pusat">Pusat</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-edit').style.display='none'"
                        class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEdit(id, nama, tipe) {
            document.getElementById('edit-nama').value = nama;
            document.getElementById('edit-tipe').value = tipe;
            document.getElementById('form-edit').action = `/dimsys/public/master/wilayah/${id}`;
            document.getElementById('modal-edit').style.display = 'flex';
        }
    </script>

@endsection