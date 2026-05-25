@extends('layouts.app')
@section('title', 'Stok Masuk')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Stok Masuk</h2>
        @if(!auth()->user()->hasRole('owner'))
            <a href="{{ route('stok.masuk.create') }}"
                class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg">
                + Tambah Stok
            </a>
        @endif
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('stok.masuk.index') }}" class="flex flex-wrap items-end gap-3">
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
                <label class="block text-xs text-gray-500 mb-1">Supplier</label>
                <select name="supplier_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                    style="min-width: 150px;">
                    <option value="">Semua Supplier</option>
                    @foreach($supplierList as $s)
                        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Jenis</label>
                <select name="jenis"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                    style="min-width: 130px;">
                    <option value="">Semua Jenis</option>
                    <option value="masuk" {{ request('jenis') == 'masuk' ? 'selected' : '' }}>Stok Masuk</option>
                    <option value="awal" {{ request('jenis') == 'awal' ? 'selected' : '' }}>Stok Awal</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Per Halaman</label>
                <select name="per_page"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                    style="min-width: 60px;">
                    @foreach([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" {{ request('per_page', 25) == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">

                <button type="submit" class="px-4 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg">
                    Filter
                </button>
                <a href="{{ route('stok.masuk.index') }}"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">
                    Reset
                </a>

                <a href="{{ route('stok.masuk.export', request()->all()) }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg">
                    <i class="fa-solid fa-file-excel mr-1"></i> Export Excel
                </a>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
        <span>Menampilkan {{ $stokMasuk->firstItem() }}-{{ $stokMasuk->lastItem() }} dari {{ $stokMasuk->total() }}
            data</span>
    </div>

    {{-- Summary --}}
    @if($stokMasuk->count())
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase">Total Transaksi</p>
                <p class="text-xl font-bold text-gray-700 mt-1">{{ $stokMasuk->count() }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase">Total Item</p>
                <p class="text-xl font-bold text-orange-500 mt-1">
                    {{ number_format($stokMasuk->sum(fn($s) => $s->details->sum('jumlah'))) }} pcs
                </p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase">Total HPP</p>
                <p class="text-xl font-bold text-green-600 mt-1">
                    Rp {{ number_format($stokMasuk->sum(fn($s) => $s->details->sum(fn($d) => $d->jumlah * $d->hpp))) }}
                </p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase">Stok Awal</p>
                <p class="text-xl font-bold text-purple-500 mt-1">
                    {{ $stokMasuk->where('jenis', 'awal')->count() }} entri
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
                    <th class="px-4 py-3 text-left">Jenis</th>
                    <th class="px-4 py-3 text-left">Wilayah</th>
                    <th class="px-4 py-3 text-left">Supplier</th>
                    <th class="px-4 py-3 text-left">Keterangan</th>
                    <th class="px-4 py-3 text-right">Total Item</th>
                    <th class="px-4 py-3 text-right">Total HPP</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($stokMasuk as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">
                            {{ $stokMasuk->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ \Carbon\Carbon::parse($s->tanggal)->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="px-2 py-1 rounded-full text-xs {{ $s->jenis === 'awal' ? 'bg-purple-100 text-purple-600' : 'bg-green-100 text-green-600' }}">
                                {{ $s->jenis === 'awal' ? 'Stok Awal' : 'Stok Masuk' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $s->wilayah->nama }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $s->supplier?->nama ?? '-' }}</td>

                        <td class="px-4 py-3 text-gray-500">{{ $s->keterangan ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-700">
                            {{ number_format($s->details->sum('jumlah')) }} pcs
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-green-600">
                            Rp {{ number_format($s->details->sum(fn($d) => $d->jumlah * $d->hpp)) }}
                        </td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('stok.masuk.show', $s) }}"
                                class="text-xs px-3 py-1 bg-blue-50 hover:bg-blue-100 rounded-lg text-blue-600">
                                Detail
                            </a>
                            @if(!auth()->user()->hasRole('owner') && \Carbon\Carbon::parse($s->tanggal)->isToday())
                                <form method="POST" action="{{ route('stok.masuk.destroy', $s) }}"
                                    data-confirm="Yakin ingin membatalkan stok masuk ini?">
                                    @csrf @method('DELETE')
                                    <button class="text-xs px-3 py-1 bg-red-50 hover:bg-red-100 rounded-lg text-red-500">
                                        Batalkan
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                            Belum ada data stok masuk.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($stokMasuk->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Halaman {{ $stokMasuk->currentPage() }} dari {{ $stokMasuk->lastPage() }}
            </div>
            <div>{{ $stokMasuk->links() }}</div>
        </div>
    @endif
@endsection