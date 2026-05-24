@extends('layouts.app')
@section('title', 'Rekap Stok Freezer')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Rekap Stok Freezer</h2>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('stok.rekap') }}" class="flex flex-wrap items-end gap-3">
            @if(!auth()->user()->hasRole('koordinator'))
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Wilayah</label>
                    <select name="wilayah_id"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                        style="min-width: 150px;">
                        @foreach($wilayahList as $w)
                            <option value="{{ $w->id }}" {{ $wilayahId == $w->id ? 'selected' : '' }}>
                                {{ $w->nama }} ({{ ucfirst($w->tipe) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
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
                    Tampilkan
                </button>
                <a href="{{ route('stok.rekap.export', ['wilayah_id' => $wilayahId]) }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg">
                    <i class="fa-solid fa-file-excel mr-1"></i> Export Excel
                </a>
            </div>
        </form>
    </div>
    <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
        <span>Menampilkan {{ $paginated->firstItem() ?? 0 }}-{{ $paginated->lastItem() ?? 0 }} dari
            {{ $paginated->total() }} produk</span>
    </div>
    {{-- Info wilayah --}}
    @if($wilayah)
        <div class="mb-4 px-4 py-3 bg-blue-50 text-blue-700 rounded-lg text-sm">
            Menampilkan stok freezer untuk wilayah: <strong>{{ $wilayah->nama }}</strong>
            ({{ ucfirst($wilayah->tipe) }}) — data real-time
        </div>
    @endif

    {{-- Summary Cards --}}
    @if($rekap->count())
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase">Total Produk</p>
                <p class="text-xl font-bold text-gray-700 mt-1">{{ $rekap->count() }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase">Total Stok</p>
                <p class="text-xl font-bold text-orange-500 mt-1">
                    {{ number_format($rekap->sum('stok_akhir')) }} pcs
                </p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase">Nilai Stok</p>
                <p class="text-xl font-bold text-green-600 mt-1">
                    Rp {{ number_format($rekap->sum('nilai_stok')) }}
                </p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm">
                <p class="text-xs text-gray-400 uppercase">Stok Menipis</p>
                <p class="text-xl font-bold text-red-500 mt-1">
                    {{ $rekap->where('status', 'menipis')->count() + $rekap->where('status', 'habis')->count() }} produk
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
                    <th class="px-4 py-3 text-left">Produk</th>
                    <th class="px-4 py-3 text-right">Total Masuk</th>
                    <th class="px-4 py-3 text-right">OUT Gerobak</th>
                    <th class="px-4 py-3 text-right">Keluar Wilayah</th>
                    <th class="px-4 py-3 text-right">Stok Akhir</th>
                    <th class="px-4 py-3 text-right">HPP Rata-rata</th>
                    <th class="px-4 py-3 text-right">Nilai Stok</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($paginated as $r)
                    <tr
                        class="hover:bg-gray-50 {{ $r['status'] === 'habis' ? 'bg-red-50' : ($r['status'] === 'menipis' ? 'bg-yellow-50' : '') }}">
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">
                            {{ $paginated->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $r['produk']->nama }}</td>
                        <td class="px-4 py-3 text-right text-green-600 font-medium">
                            {{ number_format($r['masuk']) }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">
                            {{ number_format($r['out_gerobak']) }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">
                            {{ number_format($r['keluar_wilayah']) }}
                        </td>
                        <td
                            class="px-4 py-3 text-right font-bold {{ $r['stok_akhir'] <= 0 ? 'text-red-600' : ($r['stok_akhir'] <= 50 ? 'text-yellow-600' : 'text-gray-700') }}">
                            {{ number_format($r['stok_akhir']) }} pcs
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">
                            Rp {{ number_format($r['hpp_rata']) }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-700">
                            Rp {{ number_format($r['nilai_stok']) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($r['status'] === 'habis')
                                <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-600">Habis</span>
                            @elseif($r['status'] === 'menipis')
                                <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-600">Menipis</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-600">Aman</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                            Belum ada data stok untuk wilayah ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($rekap->count())
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td class="px-4 py-3 text-gray-600" colspan="2">Total</td>
                        <td class="px-4 py-3 text-right text-green-600">{{ number_format($rekap->sum('masuk')) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format($rekap->sum('out_gerobak')) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format($rekap->sum('keluar_wilayah')) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format($rekap->sum('stok_akhir')) }} pcs</td>
                        <td></td>
                        <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format($rekap->sum('nilai_stok')) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
    @if($paginated->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Halaman {{ $paginated->currentPage() }} dari {{ $paginated->lastPage() }}
            </div>
            <div>{{ $paginated->links() }}</div>
        </div>
    @endif

@endsection