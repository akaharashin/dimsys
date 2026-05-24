@extends('layouts.app')
@section('title', 'Penjualan Wilayah')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Penjualan Wilayah</h2>
        <a href="{{ route('transaksi.penjualan-wilayah.create') }}"
            class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg">
            + Tambah Penjualan
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('transaksi.penjualan-wilayah.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ request('dari') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ request('sampai') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Wilayah Asal</label>
                <select name="wilayah_asal_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                    style="min-width:130px">
                    <option value="">Semua</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ $w->id }}" {{ request('wilayah_asal_id') == $w->id ? 'selected' : '' }}>{{ $w->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Wilayah Tujuan</label>
                <select name="wilayah_tujuan_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                    style="min-width:130px">
                    <option value="">Semua</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ $w->id }}" {{ request('wilayah_tujuan_id') == $w->id ? 'selected' : '' }}>{{ $w->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status Bayar</label>
                <select name="status_bayar"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                    <option value="">Semua</option>
                    <option value="lunas" {{ request('status_bayar') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="belum_lunas" {{ request('status_bayar') == 'belum_lunas' ? 'selected' : '' }}>Belum Lunas
                    </option>
                    <option value="sebagian" {{ request('status_bayar') == 'sebagian' ? 'selected' : '' }}>Sebagian</option>
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
                <a href="{{ route('transaksi.penjualan-wilayah.index') }}"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">Reset</a>
                <a href="{{ route('transaksi.penjualan-wilayah.export', request()->all()) }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg"><i class="fa-solid fa-file-excel mr-1"></i> Export</a>
            </div>
        </form>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-gray-400">
            <p class="text-xs text-gray-400 uppercase">Total Nilai</p>
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
                <tr>
                    <th class="px-4 py-3 text-center w-12">No</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Dari</th>
                    <th class="px-4 py-3 text-left">Ke</th>
                    <th class="px-4 py-3 text-right">Total</th>
                    <th class="px-4 py-3 text-left">Status Bayar</th>
                    <th class="px-4 py-3 text-left">Keterangan</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($penjualan as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $penjualan->firstItem() + $loop->index }}
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $p->wilayahAsal->nama }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $p->wilayahTujuan->nama }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-700">Rp {{ number_format($p->total) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs
                                    {{ $p->status_bayar === 'lunas' ? 'bg-green-100 text-green-600' :
                    ($p->status_bayar === 'sebagian' ? 'bg-yellow-100 text-yellow-600' :
                        'bg-red-100 text-red-500') }}">
                                    {{ ucfirst(str_replace('_', ' ', $p->status_bayar)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $p->keterangan ?? '-' }}</td>
                            <td class="px-4 py-3 flex gap-2">
                                <a href="{{ route('transaksi.penjualan-wilayah.show', $p) }}"
                                    class="text-xs px-3 py-1 bg-blue-50 hover:bg-blue-100 rounded-lg text-blue-600">Detail</a>
                                @if($p->status_bayar !== 'lunas')
                                    <button onclick="openUpdateStatus('{{ $p->id }}','{{ $p->status_bayar }}')"
                                        class="text-xs px-3 py-1 bg-yellow-50 hover:bg-yellow-100 rounded-lg text-yellow-600">Update</button>
                                @endif
                                <form method="POST" action="{{ route('transaksi.penjualan-wilayah.destroy', $p) }}"
                                    data-confirm="Yakin ingin membatalkan penjualan wilayah ini?">
                                    @csrf @method('DELETE')
                                    <button
                                        class="text-xs px-3 py-1 bg-red-50 hover:bg-red-100 rounded-lg text-red-500">Batalkan</button>
                                </form>
                            </td>
                        </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400">Belum ada data penjualan wilayah.</td>
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
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                        <option value="belum_lunas">Belum Lunas</option>
                        <option value="sebagian">Sebagian</option>
                        <option value="lunas">Lunas</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-status').style.display='none'"
                        class="px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg">Update</button>
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