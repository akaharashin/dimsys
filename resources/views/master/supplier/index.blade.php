@extends('layouts.app')
@section('title', 'Master Supplier')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Master Supplier</h2>
        @if(!auth()->user()->hasRole('owner'))
        <button onclick="document.getElementById('modal-tambah').style.display='flex'"
            class="bg-red-700 hover:bg-red-800 text-white text-sm px-4 py-2 rounded-lg">
            + Tambah Supplier
        </button>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('master.supplier.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1" style="min-width:200px">
                <label class="block text-xs text-gray-500 mb-1">Cari Supplier</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau keterangan..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
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
                        <option value="{{ $n }}" {{ request('per_page', 25) == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg">Filter</button>
                <a href="{{ route('master.supplier.index') }}"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">Reset</a>
                <a href="{{ route('master.supplier.export') }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg"><i class="fa-solid fa-file-excel mr-1"></i> Export</a>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
        <span>Menampilkan {{ $supplier->firstItem() }}-{{ $supplier->lastItem() }} dari {{ $supplier->total() }}
            supplier</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-gray-500 uppercase text-xs" style="position:sticky;top:0;background:#f9fafb;z-index:10;">
                <tr>
                    <th class="px-4 py-3 text-center w-12">No</th>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Keterangan</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($supplier as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">
                            {{ $supplier->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $s->nama }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $s->keterangan ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="px-2 py-1 rounded-full text-xs {{ $s->aktif ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                {{ $s->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 flex gap-2">
                            @if(!auth()->user()->hasRole('owner'))
                                <button onclick="openEdit({{ $s->toJson() }})"
                                    class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 rounded-md text-amber-700 font-medium"><i class="fa-solid fa-pencil text-xs"></i> Edit</button>
                                @if($s->aktif)
                                    <form method="POST" action="{{ route('master.supplier.destroy', $s) }}"
                                        data-confirm="Yakin ingin menonaktifkan supplier {{ $s->nama }}?">
                                        @csrf @method('DELETE')
                                        <button
                                            class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-red-50 hover:bg-red-100 rounded-md text-red-600 font-medium"><i class="fa-solid fa-ban text-xs"></i> Nonaktifkan</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('master.supplier.update', $s) }}">
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
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                            @if(request('search')) Tidak ada supplier dengan kata kunci "{{ request('search') }}"
                            @else Belum ada data supplier. @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($supplier->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">Halaman {{ $supplier->currentPage() }} dari {{ $supplier->lastPage() }}</div>
            <div>{{ $supplier->links() }}</div>
        </div>
    @endif

    @if(!auth()->user()->hasRole('owner'))
    {{-- Modal Tambah --}}
    <div id="modal-tambah"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:9999;">
        <div
            style="background:white;border-radius:12px;padding:24px;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Tambah Supplier</h3>
            <form method="POST" action="{{ route('master.supplier.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Nama Supplier</label>
                    <input type="text" name="nama" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1">Keterangan</label>
                    <input type="text" name="keterangan"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
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

    {{-- Modal Edit --}}
    <div id="modal-edit"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:9999;">
        <div
            style="background:white;border-radius:12px;padding:24px;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Edit Supplier</h3>
            <form id="form-edit" method="POST" action="">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Nama Supplier</label>
                    <input type="text" id="edit-nama" name="nama" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1">Keterangan</label>
                    <input type="text" id="edit-keterangan" name="keterangan"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
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
        function openEdit(data) {
            document.getElementById('edit-nama').value = data.nama;
            document.getElementById('edit-keterangan').value = data.keterangan ?? '';
            document.getElementById('form-edit').action = `/dimsys/public/master/supplier/${data.id}`;
            document.getElementById('modal-edit').style.display = 'flex';
        }
    </script>
    @endif

@endsection