@extends('layouts.app')
@section('title', 'Rekap Stok Laporan')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Rekap Stok</h2>
    </div>

    <div class="mb-4 px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
        <i class="fa-solid fa-circle-info mr-1"></i>
        Rekap Stok menampilkan <strong>mutasi stok dalam periode bulan yang dipilih</strong>.
    </div>

    @php
        $bulanDipilih = \Carbon\Carbon::parse($bulan . '-01')->startOfMonth();
        $bulanBerjalan = \Carbon\Carbon::today()->startOfMonth();
    @endphp
    @if($bulanDipilih->ne($bulanBerjalan))
        <div class="bg-yellow-50 border border-yellow-200 px-4 py-3 rounded-lg text-sm text-yellow-700 mb-4">
            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
            Anda melihat data bulan <strong>{{ $bulanDipilih->locale('id')->isoFormat('MMMM Y') }}</strong>.
            Untuk posisi stok terkini, gunakan menu
            <a href="{{ route('stok.rekap') }}" class="underline font-semibold">Stok Freezer</a>.
        </div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('laporan.stok') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Bulan</label>
                <input type="month" name="bulan" value="{{ $bulan }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
            </div>
            @if(!auth()->user()->hasRole('koordinator'))
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Wilayah</label>
                    <select name="wilayah_id"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
                        style="min-width:140px">
                        <option value="semua" {{ $wilayahId === 'semua' ? 'selected' : '' }}>Semua Wilayah</option>
                        @foreach($wilayahList as $w)
                            <option value="{{ $w->id }}" {{ $wilayahId == $w->id ? 'selected' : '' }}>{{ $w->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg">Tampilkan</button>
                <a href="{{ route('laporan.stok.export', ['bulan' => $bulan, 'wilayah_id' => $wilayahId]) }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg"><i class="fa-solid fa-file-excel mr-1"></i> Export Excel</a>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-400">
            <p class="text-xs text-gray-400 uppercase">Total Masuk</p>
            <p class="text-xl font-bold text-green-600 mt-1">{{ number_format($rekap->sum('masuk')) }} pcs</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-yellow-400">
            <p class="text-xs text-gray-400 uppercase">Total Terjual</p>
            <p class="text-xl font-bold text-yellow-600 mt-1">{{ number_format($rekap->sum('terjual')) }} pcs</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-400">
            <p class="text-xs text-gray-400 uppercase">Sisa Stok</p>
            <p class="text-xl font-bold text-blue-500 mt-1">{{ number_format($rekap->sum('sisa')) }} pcs</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-purple-400">
            <p class="text-xs text-gray-400 uppercase">Nilai Stok Sisa</p>
            <p class="text-xl font-bold text-purple-500 mt-1">Rp {{ number_format($rekap->sum('nilai_sisa')) }}</p>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-600">
                Detail Stok —
                <span class="text-yellow-600">{{ \Carbon\Carbon::parse($bulan)->locale('id')->isoFormat('MMMM Y') }}</span>
            </h3>
        </div>
        <table class="w-full text-sm">
            <thead class="text-gray-500 uppercase text-xs" style="position:sticky;top:0;background:#f9fafb;z-index:10;">
                <tr>
                    <th class="px-4 py-3 text-center w-10">No</th>
                    <th class="px-4 py-3 text-left">Produk</th>
                    <th class="px-4 py-3 text-right">Stok Awal</th>
                    <th class="px-4 py-3 text-right">Masuk Bulan Ini</th>
                    <th class="px-4 py-3 text-right">Terjual Bulan Ini</th>
                    <th class="px-4 py-3 text-right">Estimasi Sisa Akhir Bulan</th>
                    <th class="px-4 py-3 text-right">HPP</th>
                    <th class="px-4 py-3 text-right">Nilai Sisa</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rekap as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $r['produk']->nama }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($r['stok_awal']) }}</td>
                        <td class="px-4 py-3 text-right text-green-600">{{ number_format($r['masuk']) }}</td>
                        <td class="px-4 py-3 text-right text-yellow-600">{{ number_format($r['terjual']) }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $r['sisa'] < 0 ? 'text-red-600' : 'text-gray-700' }}">
                            {{ number_format($r['sisa']) }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">Rp {{ number_format($r['hpp']) }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-700">Rp {{ number_format($r['nilai_sisa']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400">Tidak ada data stok.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($rekap->count())
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td colspan="2" class="px-4 py-3 text-gray-600">Total</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format($rekap->sum('stok_awal')) }}</td>
                        <td class="px-4 py-3 text-right text-green-600">{{ number_format($rekap->sum('masuk')) }}</td>
                        <td class="px-4 py-3 text-right text-yellow-600">{{ number_format($rekap->sum('terjual')) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format($rekap->sum('sisa')) }}</td>
                        <td></td>
                        <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format($rekap->sum('nilai_sisa')) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

@endsection