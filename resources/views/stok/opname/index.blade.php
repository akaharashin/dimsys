@extends('layouts.app')
@section('title', 'Stok Opname')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Stok Opname</h2>
        <a href="{{ route('stok.opname.create') }}"
            class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg">
            + Tambah STO
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('stok.opname.index') }}" class="flex flex-wrap items-end gap-3">
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
            @if(!auth()->user()->hasRole('koordinator'))
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Wilayah</label>
                    <select name="wilayah_id"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                        style="min-width:140px">
                        <option value="">Semua</option>
                        @foreach($wilayahList as $w)
                            <option value="{{ $w->id }}" {{ request('wilayah_id') == $w->id ? 'selected' : '' }}>{{ $w->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300" style="min-width:100px">
                    <option value="">Semua</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="final" {{ request('status') == 'final' ? 'selected' : '' }}>Final</option>
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
                <a href="{{ route('stok.opname.index') }}"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">Reset</a>
                <a href="{{ route('stok.opname.export', request()->all()) }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg"><i class="fa-solid fa-file-excel mr-1"></i> Export</a>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
        <span>Menampilkan {{ $stokOpname->firstItem() ?? 0 }}-{{ $stokOpname->lastItem() ?? 0 }} dari
            {{ $stokOpname->total() }} data</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-gray-500 uppercase text-xs" style="position:sticky;top:0;background:#f9fafb;z-index:10;">
                <tr>
                    <th class="px-4 py-3 text-center w-12">No</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Wilayah</th>
                    <th class="px-4 py-3 text-left">Keterangan</th>
                    <th class="px-4 py-3 text-right">Total Produk</th>
                    <th class="px-4 py-3 text-right">Selisih Qty</th>
                    <th class="px-4 py-3 text-right">Nilai Selisih</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($stokOpname as $so)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $stokOpname->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ \Carbon\Carbon::parse($so->tanggal)->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $so->wilayah->nama }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $so->keterangan ?? '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ $so->details->count() }} produk</td>
                        <td
                            class="px-4 py-3 text-right font-medium {{ $so->details->sum('selisih') < 0 ? 'text-red-500' : ($so->details->sum('selisih') > 0 ? 'text-green-600' : 'text-gray-400') }}">
                            {{ $so->details->sum('selisih') > 0 ? '+' : '' }}{{ number_format($so->details->sum('selisih')) }}
                        </td>
                        <td
                            class="px-4 py-3 text-right font-medium {{ $so->details->sum('nilai_selisih') < 0 ? 'text-red-500' : 'text-gray-700' }}">
                            Rp {{ number_format($so->details->sum('nilai_selisih')) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span
                                class="px-2 py-1 rounded-full text-xs {{ $so->status === 'final' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">
                                {{ ucfirst($so->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('stok.opname.show', $so) }}"
                                class="text-xs px-3 py-1 bg-blue-50 hover:bg-blue-100 rounded-lg text-blue-600">Detail</a>
                            <form method="POST" action="{{ route('stok.opname.destroy', $so) }}"
                                data-confirm="Yakin ingin membatalkan stok opname ini?">
                                @csrf @method('DELETE')
                                <button
                                    class="text-xs px-3 py-1 bg-red-50 hover:bg-red-100 rounded-lg text-red-500">Batalkan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-400">Belum ada data stok opname.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($stokOpname->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">Halaman {{ $stokOpname->currentPage() }} dari {{ $stokOpname->lastPage() }}</div>
            <div>{{ $stokOpname->links() }}</div>
        </div>
    @endif

@endsection