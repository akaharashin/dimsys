@extends('layouts.app')
@section('title', 'Master Produk')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Master Produk</h2>
        <button onclick="document.getElementById('modal-tambah').style.display='flex'"
            class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg">
            + Tambah Produk
        </button>
    </div>

    {{-- Filter & Search --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('master.produk.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1" style="min-width:200px">
                <label class="block text-xs text-gray-500 mb-1">Cari Produk</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama produk..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
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
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                    style="min-width:60px">
                    @foreach([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" {{ request('per_page', 25) == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="sort" value="{{ request('sort', 'nama') }}">
            <input type="hidden" name="dir" value="{{ request('dir', 'asc') }}">
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg">Filter</button>
                <a href="{{ route('master.produk.index') }}"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">Reset</a>
                <a href="{{ route('master.produk.export') }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg"><i class="fa-solid fa-file-excel mr-1"></i> Export</a>
            </div>
        </form>
    </div>

    {{-- Info hasil --}}
    <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
        <span>Menampilkan {{ $produk->firstItem() }}-{{ $produk->lastItem() }} dari {{ $produk->total() }} produk</span>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-gray-500 uppercase text-xs" style="position:sticky;top:0;background:#f9fafb;z-index:10;">
                <tr>
                    @php
                        function sortLink($label, $col, $current, $dir)
                        {
                            $newDir = ($current === $col && $dir === 'asc') ? 'desc' : 'asc';
                            $icon = $current === $col ? ($dir === 'asc' ? '↑' : '↓') : '↕';
                            $query = array_merge(request()->all(), ['sort' => $col, 'dir' => $newDir]);
                            return '<a href="?' . http_build_query($query) . '" class="flex items-center gap-1 hover:text-orange-500">' . $label . ' <span>' . $icon . '</span></a>';
                        }
                    @endphp
                    <th class="px-4 py-3 text-center w-12">No</th>
                    <th class="px-4 py-3 text-left">
                        {!! sortLink('Nama', 'nama', request('sort', 'nama'), request('dir', 'asc')) !!}
                    </th>
                    <th class="px-4 py-3 text-right">
                        {!! sortLink('HPP', 'hpp', request('sort', 'nama'), request('dir', 'asc')) !!}
                    </th>
                    <th class="px-4 py-3 text-right">Mitra</th>
                    <th class="px-4 py-3 text-right">
                        {!! sortLink('Jual', 'harga_jual', request('sort', 'nama'), request('dir', 'asc')) !!}
                    </th>
                    <th class="px-4 py-3 text-right">Umum</th>
                    <th class="px-4 py-3 text-right">
                        {!! sortLink('Agen', 'harga_agen', request('sort', 'nama'), request('dir', 'asc')) !!}
                    </th>
                    <th class="px-4 py-3 text-right">
                        {!! sortLink('Komisi', 'komisi', request('sort', 'nama'), request('dir', 'asc')) !!}
                    </th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($produk as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">
                            {{ $produk->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $p->nama }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($p->hpp) }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($p->harga_mitra) }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($p->harga_jual) }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($p->harga_umum) }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($p->harga_agen) }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($p->komisi) }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="px-2 py-1 rounded-full text-xs {{ $p->aktif ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                {{ $p->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 flex gap-2">
                            <button onclick="openEdit({{ $p->toJson() }})"
                                class="text-xs px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600">Edit</button>
                            @if($p->aktif)
                                <form method="POST" action="{{ route('master.produk.destroy', $p) }}"
                                    data-confirm="Yakin ingin menonaktifkan produk {{ $p->nama }}?">
                                    @csrf @method('DELETE')
                                    <button
                                        class="text-xs px-3 py-1 bg-red-50 hover:bg-red-100 rounded-lg text-red-500">Nonaktifkan</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('master.produk.update', $p) }}">
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
                        <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                            @if(request('search'))
                                Tidak ada produk dengan kata kunci "{{ request('search') }}"
                            @else
                                Belum ada data produk.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($produk->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Halaman {{ $produk->currentPage() }} dari {{ $produk->lastPage() }}
            </div>
            <div>{{ $produk->links() }}</div>
        </div>
    @endif

    {{-- Modal Tambah --}}
    <div id="modal-tambah"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:9999;">
        <div
            style="background:white;border-radius:12px;padding:24px;width:100%;max-width:520px;box-shadow:0 20px 60px rgba(0,0,0,0.2);max-height:90vh;overflow-y:auto;">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Tambah Produk</h3>
            <form method="POST" action="{{ route('master.produk.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Nama Produk</label>
                    <input type="text" name="nama" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    @foreach(['hpp' => 'HPP', 'harga_mitra' => 'Harga Mitra', 'harga_jual' => 'Harga Jual', 'harga_umum' => 'Harga Umum', 'harga_agen' => 'Harga Agen', 'komisi' => 'Komisi'] as $field => $label)
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">{{ $label }}</label>
                            <input type="number" name="{{ $field }}" value="0" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-end gap-2 mt-4">
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
            style="background:white;border-radius:12px;padding:24px;width:100%;max-width:520px;box-shadow:0 20px 60px rgba(0,0,0,0.2);max-height:90vh;overflow-y:auto;">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Edit Produk</h3>
            <form id="form-edit" method="POST" action="">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="block text-sm text-gray-600 mb-1">Nama Produk</label>
                    <input type="text" id="edit-nama" name="nama" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    @foreach(['hpp' => 'HPP', 'harga_mitra' => 'Harga Mitra', 'harga_jual' => 'Harga Jual', 'harga_umum' => 'Harga Umum', 'harga_agen' => 'Harga Agen', 'komisi' => 'Komisi'] as $field => $label)
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">{{ $label }}</label>
                            <input type="number" id="edit-{{ $field }}" name="{{ $field }}" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="document.getElementById('modal-edit').style.display='none'"
                        class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEdit(data) {
            document.getElementById('edit-nama').value = data.nama;
            ['hpp', 'harga_mitra', 'harga_jual', 'harga_umum', 'harga_agen', 'komisi'].forEach(f => {
                document.getElementById('edit-' + f).value = data[f];
            });
            document.getElementById('form-edit').action = `/dimsys/public/master/produk/${data.id}`;
            document.getElementById('modal-edit').style.display = 'flex';
        }
    </script>

@endsection