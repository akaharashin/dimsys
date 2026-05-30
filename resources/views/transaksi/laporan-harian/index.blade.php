@extends('layouts.app')
@section('title', 'Laporan Harian')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Laporan Harian</h2>
        @if(!auth()->user()->hasRole('owner'))
        <a href="{{ route('transaksi.laporan-harian.create') }}"
            class="bg-red-700 hover:bg-red-800 text-white text-sm px-4 py-2 rounded-lg">
            + Input Laporan
        </a>
        @endif
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('transaksi.laporan-harian.index') }}" class="flex flex-wrap items-end gap-3">
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
            @if(!auth()->user()->hasRole('koordinator'))
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Wilayah</label>
                    <select name="wilayah_id"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                        style="min-width:130px">
                        <option value="">Semua</option>
                        @foreach($wilayahList as $w)
                            <option value="{{ $w->id }}" {{ request('wilayah_id') == $w->id ? 'selected' : '' }}>{{ $w->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="block text-xs text-gray-500 mb-1">Outlet</label>
                <select name="outlet_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                    style="min-width:140px">
                    <option value="">Semua Outlet</option>
                    @foreach($outletList as $o)
                        <option value="{{ $o->id }}" {{ request('outlet_id') == $o->id ? 'selected' : '' }}>{{ $o->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" style="min-width:100px">
                    <option value="">Semua</option>
                    <option value="final" {{ request('status') == 'final' ? 'selected' : '' }}>Final</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
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
                <a href="{{ route('transaksi.laporan-harian.index') }}"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">Reset</a>
                <a href="{{ route('transaksi.laporan-harian.export', request()->all()) }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg"><i class="fa-solid fa-file-excel mr-1"></i> Export</a>
            </div>
        </form>
    </div>

    {{-- Ringkasan talangan (muncul hanya bila ada kekurangan setor pada hasil filter) --}}
    @if(($totalTalangan ?? 0) > 0)
        <div class="mb-4 px-4 py-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
            <span>
                Total Perlu Ditalangi (sesuai filter aktif):
                <strong>Rp {{ number_format($totalTalangan) }}</strong>
                — ada outlet yang pengeluarannya melebihi (omset − komisi).
            </span>
        </div>
    @endif

    {{-- Info --}}
    <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
        <span>Menampilkan {{ $laporan->firstItem() ?? 0 }}-{{ $laporan->lastItem() ?? 0 }} dari {{ $laporan->total() }}
            laporan</span>
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
                        <a href="{{ sortUrl('outlet_id') }}" class="flex items-center gap-1 hover:text-red-700 transition-colors">
                            Outlet <i class="fa-solid {{ sortIcon('outlet_id') }} text-xs"></i>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left">Wilayah</th>
                    <th class="px-4 py-3 text-right">Omset</th>
                    <th class="px-4 py-3 text-right">Komisi</th>
                    <th class="px-4 py-3 text-right">Laba</th>
                    <th class="px-4 py-3 text-right">
                        <a href="{{ sortUrl('total_setor') }}" class="flex items-center justify-end gap-1 hover:text-red-700 transition-colors">
                            Setor <i class="fa-solid {{ sortIcon('total_setor') }} text-xs"></i>
                        </a>
                    </th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($laporan as $l)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $laporan->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ \Carbon\Carbon::parse($l->tanggal)->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $l->outlet->nama }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $l->outlet->wilayah->nama }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format($l->details->sum('omset')) }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($l->details->sum('komisi')) }}</td>
                        <td class="px-4 py-3 text-right font-medium text-green-600">
                            Rp
                            {{ number_format($l->details->sum('omset') - $l->details->sum('modal') - $l->details->sum('komisi')) }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-blue-600">
                            Rp {{ number_format($l->total_setor) }}
                            @if(($l->talangan ?? 0) > 0)
                                <span class="block mt-1 ml-auto w-fit px-2 py-0.5 rounded text-xs bg-amber-100 text-amber-700 font-medium">
                                    Talangan: Rp {{ number_format($l->talangan) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="px-2 py-1 rounded-full text-xs {{ $l->status === 'final' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">
                                {{ ucfirst($l->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('transaksi.laporan-harian.show', $l) }}"
                                class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 rounded-md text-blue-600 font-medium"><i class="fa-solid fa-eye text-xs"></i> Detail</a>
                            @if(!auth()->user()->hasRole('owner') && \Carbon\Carbon::parse($l->tanggal)->isToday())
                            <form method="POST" action="{{ route('transaksi.laporan-harian.destroy', $l) }}"
                                data-confirm="Yakin ingin membatalkan laporan ini?">
                                @csrf @method('DELETE')
                                <button
                                    class="inline-flex items-center gap-1 text-xs px-2.5 py-1.5 bg-red-50 hover:bg-red-100 rounded-md text-red-600 font-medium"><i class="fa-solid fa-times text-xs"></i> Batal</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-400">Belum ada laporan harian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($laporan->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">Halaman {{ $laporan->currentPage() }} dari {{ $laporan->lastPage() }}</div>
            <div>{{ $laporan->links() }}</div>
        </div>
    @endif

@endsection