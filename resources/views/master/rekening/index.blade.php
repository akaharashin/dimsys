@extends('layouts.app')
@section('title', 'Master Rekening')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Master Rekening</h2>
        <button onclick="openTambah()"
            class="bg-red-700 hover:bg-red-800 text-white text-sm px-4 py-2 rounded-lg">
            + Tambah Rekening
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('master.rekening.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1" style="min-width:200px">
                <label class="block text-xs text-gray-500 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama rekening..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
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
                <label class="block text-xs text-gray-500 mb-1">Tipe</label>
                <select name="tipe"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    <option value="">Semua</option>
                    <option value="kas_kecil" {{ request('tipe') == 'kas_kecil' ? 'selected' : '' }}>Kas Kecil</option>
                    <option value="bank" {{ request('tipe') == 'bank' ? 'selected' : '' }}>Bank</option>
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
                <a href="{{ route('master.rekening.index') }}"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">Reset</a>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
        <span>Menampilkan {{ $rekening->firstItem() ?? 0 }}-{{ $rekening->lastItem() ?? 0 }} dari {{ $rekening->total() }} rekening</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-gray-500 uppercase text-xs" style="position:sticky;top:0;background:#f9fafb;z-index:10;">
                @php
                    function sortUrlRek($col) {
                        $d = request('sort') === $col && request('direction') === 'asc' ? 'desc' : 'asc';
                        return request()->fullUrlWithQuery(['sort' => $col, 'direction' => $d]);
                    }
                    function sortIconRek($col) {
                        if (request('sort') !== $col) return 'fa-sort text-gray-300';
                        return request('direction') === 'asc' ? 'fa-sort-up text-red-700' : 'fa-sort-down text-red-700';
                    }
                @endphp
                <tr>
                    <th class="px-5 py-3 text-center w-12">No</th>
                    <th class="px-5 py-3 text-left">
                        <a href="{{ sortUrlRek('nama') }}" class="flex items-center gap-1 hover:text-red-700 transition-colors">
                            Nama <i class="fa-solid {{ sortIconRek('nama') }} text-xs"></i>
                        </a>
                    </th>
                    <th class="px-5 py-3 text-left">
                        <a href="{{ sortUrlRek('tipe') }}" class="flex items-center gap-1 hover:text-red-700 transition-colors">
                            Tipe <i class="fa-solid {{ sortIconRek('tipe') }} text-xs"></i>
                        </a>
                    </th>
                    <th class="px-5 py-3 text-left">Wilayah</th>
                    <th class="px-5 py-3 text-right">Saldo Awal</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rekening as $r)
                    @php
                        $rekData = json_encode([
                            'id'         => $r->id,
                            'nama'       => $r->nama,
                            'tipe'       => $r->tipe,
                            'wilayah_id' => $r->wilayah_id,
                            'saldo_awal' => $r->saldo_awal,
                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 text-center text-gray-400 text-xs">{{ $rekening->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-3 font-medium text-gray-700">{{ $r->nama }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $r->tipe === 'bank' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $r->tipe === 'bank' ? 'Bank' : 'Kas Kecil' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $r->wilayah->nama ?? '-' }}</td>
                        <td class="px-5 py-3 text-right text-gray-700 font-medium">Rp {{ number_format($r->saldo_awal) }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $r->aktif ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                {{ $r->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 flex gap-2">
                            <button onclick="openEdit({{ $rekData }})"
                                class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 rounded-md text-amber-700 font-medium">
                                <i class="fa-solid fa-pencil text-xs"></i> Edit
                            </button>
                            @if($r->aktif)
                                <form method="POST" action="{{ route('master.rekening.destroy', $r) }}"
                                    data-confirm="Yakin ingin menonaktifkan rekening {{ $r->nama }}?">
                                    @csrf @method('DELETE')
                                    <button
                                        class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-red-50 hover:bg-red-100 rounded-md text-red-600 font-medium">
                                        <i class="fa-solid fa-ban text-xs"></i> Nonaktifkan
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('master.rekening.update', $r) }}">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="aktif" value="1">
                                    <button
                                        class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-green-50 hover:bg-green-100 rounded-md text-green-600 font-medium">
                                        <i class="fa-solid fa-check text-xs"></i> Aktifkan
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada data rekening.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($rekening->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">Halaman {{ $rekening->currentPage() }} dari {{ $rekening->lastPage() }}</div>
            <div>{{ $rekening->links() }}</div>
        </div>
    @endif

    {{-- Modal Tambah --}}
    <div id="modal-tambah"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:9999;">
        <div style="background:white;border-radius:12px;padding:24px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Tambah Rekening</h3>
            <form method="POST" action="{{ route('master.rekening.store') }}">
                @csrf
                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Nama Rekening</label>
                        <input type="text" name="nama" required maxlength="100"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Tipe</label>
                            <select name="tipe" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                                <option value="kas_kecil">Kas Kecil</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Wilayah</label>
                            <select name="wilayah_id" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                                <option value="">-- Pilih --</option>
                                @foreach($wilayahList as $w)
                                    <option value="{{ $w->id }}">{{ $w->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Saldo Awal (Rp)</label>
                        <input type="number" name="saldo_awal" value="0" min="0" step="1"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
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

    {{-- Modal Edit --}}
    <div id="modal-edit"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:9999;">
        <div style="background:white;border-radius:12px;padding:24px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Edit Rekening</h3>
            <form id="form-edit" method="POST" action="">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Nama Rekening</label>
                        <input type="text" id="edit-nama" name="nama" required maxlength="100"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Tipe</label>
                            <select id="edit-tipe" name="tipe" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                                <option value="kas_kecil">Kas Kecil</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Wilayah</label>
                            <select id="edit-wilayah" name="wilayah_id" required
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                                <option value="">-- Pilih --</option>
                                @foreach($wilayahList as $w)
                                    <option value="{{ $w->id }}">{{ $w->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Saldo Awal (Rp)</label>
                        <input type="number" id="edit-saldo" name="saldo_awal" min="0" step="1"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
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

    <script>
        var rekBaseUrl = "{{ url('master/rekening') }}";

        function openTambah() {
            document.getElementById('modal-tambah').style.display = 'flex';
        }

        function openEdit(data) {
            document.getElementById('form-edit').action = rekBaseUrl + '/' + data.id;
            document.getElementById('edit-nama').value = data.nama ?? '';
            document.getElementById('edit-tipe').value = data.tipe ?? 'kas_kecil';
            document.getElementById('edit-wilayah').value = data.wilayah_id ?? '';
            document.getElementById('edit-saldo').value = data.saldo_awal ?? 0;
            document.getElementById('modal-edit').style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }
    </script>

@endsection
