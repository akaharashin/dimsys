@extends('layouts.app')
@section('title', 'Pindah Stok')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Pindah Stok</h2>
        @if(!auth()->user()->hasRole(['owner', 'koordinator']))
        <a href="{{ route('transaksi.penjualan-wilayah.create') }}"
            class="bg-red-700 hover:bg-red-800 text-white text-sm px-4 py-2 rounded-lg">
            + Tambah Transaksi
        </a>
        @endif
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('transaksi.penjualan-wilayah.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ request('dari') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ request('sampai') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tipe</label>
                <select name="tipe"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                    style="min-width:120px">
                    <option value="">Semua</option>
                    <option value="penjualan" {{ request('tipe') == 'penjualan' ? 'selected' : '' }}>Penjualan</option>
                    <option value="transfer" {{ request('tipe') == 'transfer' ? 'selected' : '' }}>Pindah Stok</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status Approval</label>
                <select name="status"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                    style="min-width:120px">
                    <option value="">Semua</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Wilayah Asal</label>
                <select name="wilayah_asal_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                    style="min-width:130px">
                    <option value="">Semua</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ $w->id }}" {{ request('wilayah_asal_id') == $w->id ? 'selected' : '' }}>{{ $w->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Wilayah Tujuan</label>
                <select name="wilayah_tujuan_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                    style="min-width:130px">
                    <option value="">Semua</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ $w->id }}" {{ request('wilayah_tujuan_id') == $w->id ? 'selected' : '' }}>{{ $w->nama }}</option>
                    @endforeach
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
                <a href="{{ route('transaksi.penjualan-wilayah.index') }}"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">Reset</a>
                <a href="{{ route('transaksi.penjualan-wilayah.export', request()->all()) }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg"><i class="fa-solid fa-file-excel mr-1"></i> Export</a>
            </div>
        </form>
    </div>

    {{-- Summary (penjualan only) --}}
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-gray-400">
            <p class="text-xs text-gray-400 uppercase">Total Penjualan</p>
            <p class="text-xl font-bold text-gray-700 mt-1">Rp {{ number_format($totalNilai) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-400">
            <p class="text-xs text-gray-400 uppercase">Sudah Lunas</p>
            <p class="text-xl font-bold text-green-600 mt-1">Rp {{ number_format($totalLunas) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-red-400">
            <p class="text-xs text-gray-400 uppercase">Belum Lunas</p>
            <p class="text-xl font-bold text-red-500 mt-1">Rp {{ number_format($totalBelum) }}</p>
        </div>
    </div>

    {{-- Info --}}
    <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
        <span>Menampilkan {{ $penjualan->firstItem() ?? 0 }}-{{ $penjualan->lastItem() ?? 0 }} dari
            {{ $penjualan->total() }} data</span>
    </div>

    {{-- Tabel --}}
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
                        <a href="{{ sortUrl('tanggal') }}" class="flex items-center gap-1 hover:text-red-700 transition-colors">
                            Tanggal <i class="fa-solid {{ sortIcon('tanggal') }} text-xs"></i>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <a href="{{ sortUrl('tipe') }}" class="flex items-center gap-1 hover:text-red-700 transition-colors">
                            Tipe <i class="fa-solid {{ sortIcon('tipe') }} text-xs"></i>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left">Dari</th>
                    <th class="px-4 py-3 text-left">Ke</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-left">Status Bayar</th>
                    <th class="px-4 py-3 text-left">
                        <a href="{{ sortUrl('status') }}" class="flex items-center gap-1 hover:text-red-700 transition-colors">
                            Status <i class="fa-solid {{ sortIcon('status') }} text-xs"></i>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left">Keterangan</th>
                    <th class="px-4 py-3 text-left min-w-[140px]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($penjualan as $p)
                    @php $isToday = \Carbon\Carbon::parse($p->tanggal)->isToday(); @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $penjualan->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</td>
                        <td class="px-1 py-3">
                            @if($p->tipe === 'transfer')
                                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-600">Pindah Stok</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-700">Penjualan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $p->wilayahAsal->nama }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $p->wilayahTujuan->nama }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-700">
                            @if($p->tipe === 'transfer')
                                <span class="text-gray-400">—</span>
                            @else
                                Rp {{ number_format($p->total) }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($p->tipe === 'transfer')
                                <span class="text-gray-400 text-xs">—</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs
                                    {{ $p->status_bayar === 'lunas' ? 'bg-green-100 text-green-600' :
                                      ($p->status_bayar === 'sebagian' ? 'bg-yellow-100 text-yellow-600' :
                                       'bg-red-100 text-red-500') }}">
                                    {{ ucfirst(str_replace('_', ' ', $p->status_bayar)) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($p->status === 'menunggu')
                                <span class="px-2 py-1 rounded-full text-xs text-amber-700 border border-amber-200" style="background-color:#FFFDE7">Menunggu</span>
                            @elseif($p->status === 'disetujui')
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">Disetujui</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-600">Ditolak</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $p->keterangan ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-1 min-w-[120px]">
                                {{-- Baris 1: Detail selalu tampil --}}
                                <div class="flex gap-1">
                                    <a href="{{ route('transaksi.penjualan-wilayah.show', $p) }}"
                                        class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded text-xs font-medium">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </a>
                                    {{-- Update status bayar: hanya untuk penjualan belum lunas --}}
                                    @if(!auth()->user()->hasRole('owner') && $p->tipe === 'penjualan' && $p->status_bayar !== 'lunas')
                                        <button onclick="openUpdateStatus('{{ $p->id }}','{{ $p->status_bayar }}')"
                                            class="inline-flex items-center gap-1 px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded text-xs font-medium">
                                            <i class="fa-solid fa-pen"></i> Update
                                        </button>
                                    @endif
                                </div>

                                {{-- Baris 2: Konfirmasi + Tolak (transfer menunggu, koordinator/admin) --}}
                                @if($p->tipe === 'transfer' && $p->status === 'menunggu')
                                    @if(auth()->user()->hasRole('admin_pusat') ||
                                        (auth()->user()->hasRole('koordinator') && auth()->user()->wilayah_id === $p->wilayah_tujuan_id))
                                        <div class="flex gap-1">
                                            <a href="{{ route('transaksi.penjualan-wilayah.show', $p) }}"
                                                class="inline-flex items-center gap-1 px-2 py-1 bg-green-50 hover:bg-green-100 text-green-600 rounded text-xs font-medium">
                                                <i class="fa-solid fa-check"></i> Konfirmasi
                                            </a>
                                            <form method="POST" action="{{ route('transaksi.penjualan-wilayah.reject', $p) }}"
                                                data-confirm="Tolak pindah stok ini?">
                                                @csrf
                                                <button class="inline-flex items-center gap-1 px-2 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-medium">
                                                    <i class="fa-solid fa-xmark"></i> Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                @endif

                                {{-- Baris 3: Batal (menunggu + hari ini) --}}
                                @if(!auth()->user()->hasRole('owner') && $p->status === 'menunggu' && $isToday)
                                    <div>
                                        <form method="POST" action="{{ route('transaksi.penjualan-wilayah.destroy', $p) }}"
                                            data-confirm="{{ $p->tipe === 'transfer' ? 'Yakin ingin membatalkan pindah stok ini?' : 'Yakin ingin membatalkan penjualan wilayah ini?' }}">
                                            @csrf @method('DELETE')
                                            <button class="inline-flex items-center gap-1 px-2 py-1 bg-red-50 hover:bg-red-100 text-red-600 rounded text-xs font-medium">
                                                <i class="fa-solid fa-times"></i> Batal
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-400">Belum ada data transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($penjualan->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">Halaman {{ $penjualan->currentPage() }} dari {{ $penjualan->lastPage() }}</div>
            <div>{{ $penjualan->links() }}</div>
        </div>
    @endif

    {{-- Modal Update Status --}}
    <div id="modal-status"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:9999;">
        <div
            style="background:white;border-radius:12px;padding:24px;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Update Status Bayar</h3>
            <form id="form-status" method="POST" action="">
                @csrf @method('PUT')
                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1">Status Bayar</label>
                    <select id="edit-status" name="status_bayar"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        <option value="belum_lunas">Belum Lunas</option>
                        <option value="sebagian">Sebagian</option>
                        <option value="lunas">Lunas</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-status').style.display='none'"
                        class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUpdateStatus(id, status) {
            document.getElementById('edit-status').value = status;
            document.getElementById('form-status').action = `/dimsys/public/transaksi/penjualan-wilayah/${id}`;
            document.getElementById('modal-status').style.display = 'flex';
        }
    </script>

@endsection
