@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-700">Dashboard</h2>
            <p class="text-sm text-gray-400 mt-1">
                {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                Selamat datang, <span class="text-orange-500 font-medium">{{ auth()->user()->name }}</span>
            </p>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-400">Wilayah</p>
            <p class="text-sm font-semibold text-gray-600">
                {{ auth()->user()->wilayah?->nama ?? 'Semua Wilayah' }}
                @if(auth()->user()->wilayah)
                    <span class="text-xs font-normal text-gray-400">({{ ucfirst(auth()->user()->wilayah->tipe) }})</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Hari Ini --}}
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Ringkasan Hari Ini</p>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-orange-400">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Omset</p>
            <p class="text-2xl font-bold text-orange-500 mt-1">Rp {{ number_format($omsetHariIni) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-green-400">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Laba</p>
            <p class="text-2xl font-bold text-green-500 mt-1">Rp {{ number_format($labaHariIni) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-blue-400">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Total Setor</p>
            <p class="text-2xl font-bold text-blue-500 mt-1">Rp {{ number_format($setorHariIni) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-purple-400">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Total OUT</p>
            <p class="text-2xl font-bold text-purple-500 mt-1">{{ number_format($totalOutHariIni) }} pcs</p>
        </div>
    </div>

    {{-- Bulan Ini + Outlet --}}
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Bulan Ini</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Omset Bulan Ini</p>
            <p class="text-2xl font-bold text-orange-500 mt-1">Rp {{ number_format($omsetBulanIni) }}</p>
            <p class="text-xs text-gray-400 mt-2">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Laba Bulan Ini</p>
            <p class="text-2xl font-bold text-green-500 mt-1">Rp {{ number_format($labaBulanIni) }}</p>
            <p class="text-xs text-gray-400 mt-2">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Outlet Lapor Hari Ini</p>
            <div class="flex items-end gap-2 mt-1">
                <p class="text-2xl font-bold text-gray-700">{{ $outletSudahLapor }}</p>
                <p class="text-gray-400 text-sm mb-1">/ {{ $totalOutlet }} outlet</p>
            </div>
            @php $pct = $totalOutlet > 0 ? round($outletSudahLapor / $totalOutlet * 100) : 0; @endphp
            <div class="mt-2 w-full bg-gray-100 rounded-full h-1.5">
                <div class="bg-orange-400 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1">{{ $pct }}% sudah lapor</p>
        </div>
    </div>

    {{-- Shortcut --}}
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Akses Cepat</p>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        @if(auth()->user()->hasRole(['admin_pusat']))
            <a href="{{ route('stok.masuk.create') }}"
                class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md hover:border-orange-200 border border-transparent transition flex items-center gap-3">
                <div class="w-9 h-9 bg-orange-100 rounded-lg flex items-center justify-center text-orange-500">
                    <i class="fa-solid fa-boxes-stacked text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Stok Masuk</p>
                    <p class="text-xs text-gray-400">Tambah stok</p>
                </div>
            </a>
        @endif
        <a href="{{ route('stok.distribusi.create') }}"
            class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md hover:border-orange-200 border border-transparent transition flex items-center gap-3">
            <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center text-blue-500">
                <i class="fa-solid fa-truck-fast text-sm"></i>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-700">Distribusi</p>
                <p class="text-xs text-gray-400">OUT ke gerobak</p>
            </div>
        </a>
        <a href="{{ route('transaksi.laporan-harian.create') }}"
            class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md hover:border-orange-200 border border-transparent transition flex items-center gap-3">
            <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center text-green-500">
                <i class="fa-solid fa-file-lines text-sm"></i>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-700">Laporan Harian</p>
                <p class="text-xs text-gray-400">Input laporan</p>
            </div>
        </a>
        <a href="{{ route('stok.rekap') }}"
            class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md hover:border-orange-200 border border-transparent transition flex items-center gap-3">
            <div
                class="w-9 h-9 bg-purple-100 rounded-lg flex items-center justify-center text-purple-500">
                <i class="fa-solid fa-chart-pie text-sm"></i>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-700">Rekap Stok</p>
                <p class="text-xs text-gray-400">Cek stok freezer</p>
            </div>
        </a>
    </div>

    {{-- Laporan Terbaru --}}
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Laporan Terbaru</p>
    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">No</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Outlet</th>
                    <th class="px-4 py-3 text-right">Terjual</th>
                    <th class="px-4 py-3 text-right">Omset</th>
                    <th class="px-4 py-3 text-right">Laba</th>
                    <th class="px-4 py-3 text-right">Setor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($laporanTerbaru as $i => $l)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($l->tanggal)->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $l->outlet->nama }}
                            <span class="text-xs text-gray-400 font-normal">— {{ $l->outlet->wilayah->nama }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ $l->details->sum('terjual') }} pcs</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format($l->details->sum('omset')) }}</td>
                        <td class="px-4 py-3 text-right font-medium text-green-600">
                            Rp
                            {{ number_format($l->details->sum('omset') - $l->details->sum('modal') - $l->details->sum('komisi')) }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-blue-600">Rp {{ number_format($l->total_setor) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada laporan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection