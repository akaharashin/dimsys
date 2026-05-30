@extends('layouts.app')
@section('title', 'Detail Laporan Harian')

@section('content')

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('transaksi.laporan-harian.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">←
            Kembali</a>
        <h2 class="text-2xl font-bold text-gray-700">Detail Laporan Harian</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-gray-400 text-xs uppercase">Tanggal</p>
                <p class="font-medium text-gray-700 mt-1">
                    {{ \Carbon\Carbon::parse($laporanHarian->tanggal)->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs uppercase">Outlet</p>
                <p class="font-medium text-gray-700 mt-1">{{ $laporanHarian->outlet->nama }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs uppercase">Wilayah</p>
                <p class="font-medium text-gray-700 mt-1">{{ $laporanHarian->outlet->wilayah->nama }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs uppercase">Status</p>
                <p class="mt-1">
                    <span
                        class="px-2 py-1 rounded-full text-xs {{ $laporanHarian->status === 'final' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">
                        {{ ucfirst($laporanHarian->status) }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Produk</th>
                    <th class="px-4 py-3 text-right">Sisa</th>
                    <th class="px-4 py-3 text-right">Terjual</th>
                    <th class="px-4 py-3 text-right">Omset</th>
                    <th class="px-4 py-3 text-right">Modal</th>
                    <th class="px-4 py-3 text-right">Komisi</th>
                    <th class="px-4 py-3 text-right">Laba</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($laporanHarian->details as $d)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $d->produk->nama }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $d->sisa }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-700">{{ $d->terjual }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($d->omset) }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($d->modal) }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($d->komisi) }}</td>
                        <td class="px-4 py-3 text-right font-medium text-green-600">
                            Rp {{ number_format($d->omset - $d->modal - $d->komisi) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 font-semibold">
                <tr>
                    <td class="px-4 py-3 text-gray-600">Total</td>
                    <td class="px-4 py-3 text-right text-gray-500">{{ $laporanHarian->details->sum('sisa') }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ $laporanHarian->details->sum('terjual') }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">Rp
                        {{ number_format($laporanHarian->details->sum('omset')) }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">Rp
                        {{ number_format($laporanHarian->details->sum('modal')) }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">Rp
                        {{ number_format($laporanHarian->details->sum('komisi')) }}</td>
                    <td class="px-4 py-3 text-right text-green-600">
                        Rp {{ number_format($laporanHarian->details->sum(fn($d) => $d->omset - $d->modal - $d->komisi)) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>


    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div class="p-4 bg-green-50 rounded-lg">
                <p class="text-xs text-gray-400 uppercase">Total Setor</p>
                <p class="text-xl font-bold text-green-600 mt-1">Rp {{ number_format($laporanHarian->total_setor) }}</p>
            </div>
            <div class="p-4 bg-red-50 rounded-lg">
                <p class="text-xs text-gray-400 uppercase">Total Pengeluaran</p>
                <p class="text-xl font-bold text-red-500 mt-1">Rp {{ number_format($laporanHarian->total_pengeluaran) }}</p>
            </div>
            <div class="p-4 bg-red-50 rounded-lg">
                <p class="text-xs text-gray-400 uppercase">Total Omset</p>
                <p class="text-xl font-bold text-red-600 mt-1">
                    Rp {{ number_format($laporanHarian->details->sum('omset')) }}
                </p>
            </div>
        </div>

        @if(($laporanHarian->talangan ?? 0) > 0)
            <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                <div>
                    <p class="text-xs text-amber-600 uppercase font-semibold">Perlu Ditalangi</p>
                    <p class="text-xl font-bold text-amber-600 mt-0.5">Rp {{ number_format($laporanHarian->talangan) }}</p>
                    <p class="text-xs text-amber-600/80 mt-1">
                        Pengeluaran outlet melebihi (omset − komisi). Setoran = Rp 0; selisih ini perlu ditalangi perusahaan.
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- Pengeluaran --}}
    @if($laporanHarian->pengeluaran->count())
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-600">Detail Pengeluaran</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Keterangan</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($laporanHarian->pengeluaran as $p)
                        <tr>
                            <td class="px-4 py-3 text-gray-700">{{ $p->keterangan }}</td>
                            <td class="px-4 py-3 text-right font-medium text-red-500">Rp {{ number_format($p->jumlah) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-4 py-3 text-right font-semibold text-gray-600">Total Pengeluaran</td>
                        <td class="px-4 py-3 text-right font-bold text-red-500">
                            Rp {{ number_format($laporanHarian->pengeluaran->sum('jumlah')) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

@endsection