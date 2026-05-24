@extends('layouts.app')
@section('title', 'Detail Distribusi')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('stok.distribusi.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali</a>
    <h2 class="text-2xl font-bold text-gray-700">Detail Distribusi</h2>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-gray-400 text-xs uppercase">Tanggal</p>
            <p class="font-medium text-gray-700 mt-1">{{ \Carbon\Carbon::parse($distribusi->tanggal)->format('d M Y') }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase">Outlet</p>
            <p class="font-medium text-gray-700 mt-1">{{ $distribusi->outlet->nama }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase">Wilayah</p>
            <p class="font-medium text-gray-700 mt-1">{{ $distribusi->outlet->wilayah->nama }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase">Keterangan</p>
            <p class="font-medium text-gray-700 mt-1">{{ $distribusi->keterangan ?? '-' }}</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Produk</th>
                <th class="px-4 py-3 text-right">Jumlah OUT</th>
                <th class="px-4 py-3 text-right">Harga Jual</th>
                <th class="px-4 py-3 text-right">Potensi Omset</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($distribusi->details as $d)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-700">{{ $d->produk->nama }}</td>
                <td class="px-4 py-3 text-right text-gray-600">{{ number_format($d->jumlah_out) }} pcs</td>
                <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($d->produk->harga_jual) }}</td>
                <td class="px-4 py-3 text-right font-medium text-gray-700">
                    Rp {{ number_format($d->jumlah_out * $d->produk->harga_jual) }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-50">
            <tr>
                <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold text-gray-600">Total Potensi Omset</td>
                <td class="px-4 py-3 text-right font-bold text-gray-700">
                    Rp {{ number_format($distribusi->details->sum(fn($d) => $d->jumlah_out * $d->produk->harga_jual)) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>

@endsection