@extends('layouts.app')
@section('title', 'Distribusi')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Distribusi (OUT)</h2>
        <a href="{{ route('stok.distribusi.create') }}"
            class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg">
            + Tambah Distribusi
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('stok.distribusi.index') }}" class="flex flex-wrap items-end gap-3">
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
                    <select name="wilayah_id" id="filter-wilayah"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                        style="min-width: 150px;">
                        <option value="">Semua Wilayah</option>
                        @foreach($wilayahList as $w)
                            <option value="{{ $w->id }}" {{ request('wilayah_id') == $w->id ? 'selected' : '' }}>
                                {{ $w->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="block text-xs text-gray-500 mb-1">Outlet</label>
                <select name="outlet_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                    <option value="">Semua Outlet</option>
                    @foreach($outletList as $o)
                        <option value="{{ $o->id }}" {{ request('outlet_id') == $o->id ? 'selected' : '' }}>
                            {{ $o->nama }} — {{ $o->wilayah->nama }}
                        </option>
                    @endforeach
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
                <button type="submit" class="px-4 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg">
                    Filter
                </button>
                <a href="{{ route('stok.distribusi.index') }}"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">
                    Reset
                </a>
                <a href="{{ route('stok.distribusi.export', request()->all()) }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg">
                    <i class="fa-solid fa-file-excel mr-1"></i> Export Excel
                </a>
            </div>
        </form>
    </div>
    <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
        <span>Menampilkan {{ $distribusi->firstItem() }}-{{ $distribusi->lastItem() }} dari {{ $distribusi->total() }}
            data</span>
    </div>

    {{-- Summary --}}
    @if($distribusi->count())
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4">
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase">Total Distribusi</p>
                <p class="text-xl font-bold text-gray-700 mt-1">{{ $distribusi->count() }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase">Total OUT</p>
                <p class="text-xl font-bold text-orange-500 mt-1">
                    {{ number_format($distribusi->sum(fn($d) => $d->details->sum('jumlah_out'))) }} pcs
                </p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase">Outlet Terlayani</p>
                <p class="text-xl font-bold text-blue-500 mt-1">
                    {{ $distribusi->pluck('outlet_id')->unique()->count() }} outlet
                </p>
            </div>
        </div>
    @endif

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-center w-12">No</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Outlet</th>
                    <th class="px-4 py-3 text-left">Wilayah</th>
                    <th class="px-4 py-3 text-left">Keterangan</th>
                    <th class="px-4 py-3 text-right">Total OUT</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($distribusi as $d)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">
                            {{ $distribusi->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ \Carbon\Carbon::parse($d->tanggal)->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $d->outlet->nama }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $d->outlet->wilayah->nama }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $d->keterangan ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-700">
                            {{ number_format($d->details->sum('jumlah_out')) }} pcs
                        </td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('stok.distribusi.show', $d) }}"
                                class="text-xs px-3 py-1 bg-blue-50 hover:bg-blue-100 rounded-lg text-blue-600">
                                Detail
                            </a>
                            <form method="POST" action="{{ route('stok.distribusi.destroy', $d) }}"
                                data-confirm="Yakin ingin membatalkan distribusi ini?">
                                @csrf @method('DELETE')
                                <button class="text-xs px-3 py-1 bg-red-50 hover:bg-red-100 rounded-lg text-red-500">
                                    Batalkan
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                            Belum ada data distribusi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($distribusi->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Halaman {{ $distribusi->currentPage() }} dari {{ $distribusi->lastPage() }}
            </div>
            <div>{{ $distribusi->links() }}</div>
        </div>
    @endif

@endsection