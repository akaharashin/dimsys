@extends('layouts.app')
@section('title', 'Kontrol Penjualan')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Kontrol Penjualan</h2>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('laporan.kontrol') }}" class="flex flex-wrap items-end gap-3">
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
                <a href="{{ route('laporan.kontrol.export', ['bulan' => $bulan, 'wilayah_id' => $wilayahId]) }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg"><i class="fa-solid fa-file-excel mr-1"></i> Export Excel</a>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-red-600">
            <p class="text-xs text-gray-400 uppercase">Total Outlet</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $rekap->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-400">
            <p class="text-xs text-gray-400 uppercase">Outlet Aktif</p>
            <p class="text-2xl font-bold text-green-500 mt-1">
                {{ $rekap->where('total_hari', '>', 0)->count() }}
            </p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-red-400">
            <p class="text-xs text-gray-400 uppercase">Outlet Tidak Lapor</p>
            <p class="text-2xl font-bold text-red-500 mt-1">
                {{ $rekap->where('total_hari', 0)->count() }}
            </p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-400">
            <p class="text-xs text-gray-400 uppercase">Rata-rata Omset/Outlet</p>
            <p class="text-xl font-bold text-blue-500 mt-1">
                @php
                    $aktif = $rekap->where('total_hari', '>', 0)->count();
                    $totalOmset = $rekap->sum('total_omset');
                @endphp
                Rp {{ $aktif > 0 ? number_format($totalOmset / $aktif) : 0 }}
            </p>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-600">
                Kontrol Penjualan —
                <span style="color:#A51616">{{ \Carbon\Carbon::parse($bulan)->locale('id')->isoFormat('MMMM Y') }}</span>
            </h3>
        </div>
        <table class="w-full text-sm">
            <thead class="text-gray-500 uppercase text-xs" style="position:sticky;top:0;background:#f9fafb;z-index:10;">
                <tr>
                    <th class="px-4 py-3 text-center w-10">No</th>
                    <th class="px-4 py-3 text-left">Outlet</th>
                    <th class="px-4 py-3 text-left">Wilayah</th>
                    <th class="px-4 py-3 text-right">Hari Lapor</th>
                    <th class="px-4 py-3 text-right">Total Terjual</th>
                    <th class="px-4 py-3 text-right">Total Omset</th>
                    <th class="px-4 py-3 text-right">Total Laba</th>
                    <th class="px-4 py-3 text-right">Rata-rata/Hari</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rekap as $r)
                    <tr class="hover:bg-gray-50 {{ $r['total_hari'] == 0 ? 'bg-red-50/40' : '' }}">
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $r['outlet']->nama }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $r['outlet']->wilayah->nama }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">
                            {{ $r['total_hari'] }} / {{ \Carbon\Carbon::parse($bulan)->daysInMonth }} hari
                            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-1">
                                @php $pct = \Carbon\Carbon::parse($bulan)->daysInMonth > 0 ? round($r['total_hari'] / \Carbon\Carbon::parse($bulan)->daysInMonth * 100) : 0; @endphp
                                <div class="h-1.5 rounded-full" style="width:{{ $pct }}%;background-color:{{ $r['total_hari'] == 0 ? '#ef4444' : ($pct < 50 ? '#f59e0b' : '#A51616') }}"></div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($r['total_terjual']) }} pcs</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format($r['total_omset']) }}</td>
                        <td class="px-4 py-3 text-right font-medium text-green-600">Rp {{ number_format($r['total_laba']) }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">
                            Rp {{ $r['total_hari'] > 0 ? number_format($r['total_omset'] / $r['total_hari']) : 0 }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($r['total_hari'] == 0)
                                <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-500">Tidak Lapor</span>
                            @elseif($r['total_hari'] < 10)
                                <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-600">Jarang</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-600">Aktif</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-400">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($rekap->count())
                <tfoot class="bg-gray-50 font-semibold text-sm">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-gray-600">Total</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format($rekap->sum('total_terjual')) }} pcs
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format($rekap->sum('total_omset')) }}</td>
                        <td class="px-4 py-3 text-right text-green-600">Rp {{ number_format($rekap->sum('total_laba')) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

@endsection