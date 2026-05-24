@extends('layouts.app')
@section('title', 'Detail ' . ($penjualanWilayah->tipe === 'transfer' ? 'Transfer' : 'Penjualan') . ' Wilayah')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('transaksi.penjualan-wilayah.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali</a>
    <h2 class="text-2xl font-bold text-gray-700">
        Detail {{ $penjualanWilayah->tipe === 'transfer' ? 'Transfer' : 'Penjualan' }} Wilayah
    </h2>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-gray-400 text-xs uppercase">Tipe</p>
            <p class="mt-1">
                @if($penjualanWilayah->tipe === 'transfer')
                    <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-600">Transfer</span>
                @else
                    <span class="px-2 py-1 rounded-full text-xs bg-orange-100 text-orange-600">Penjualan</span>
                @endif
            </p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase">Tanggal</p>
            <p class="font-medium text-gray-700 mt-1">{{ \Carbon\Carbon::parse($penjualanWilayah->tanggal)->format('d M Y') }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase">Dari</p>
            <p class="font-medium text-gray-700 mt-1">{{ $penjualanWilayah->wilayahAsal->nama }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase">Ke</p>
            <p class="font-medium text-gray-700 mt-1">{{ $penjualanWilayah->wilayahTujuan->nama }}</p>
        </div>
        @if($penjualanWilayah->tipe === 'penjualan')
        <div>
            <p class="text-gray-400 text-xs uppercase">Status Bayar</p>
            <p class="mt-1">
                <span class="px-2 py-1 rounded-full text-xs
                    {{ $penjualanWilayah->status_bayar === 'lunas' ? 'bg-green-100 text-green-600' :
                      ($penjualanWilayah->status_bayar === 'sebagian' ? 'bg-yellow-100 text-yellow-600' :
                       'bg-red-100 text-red-600') }}">
                    {{ ucfirst(str_replace('_', ' ', $penjualanWilayah->status_bayar)) }}
                </span>
            </p>
        </div>
        @endif
        @if($penjualanWilayah->keterangan)
        <div class="md:col-span-2">
            <p class="text-gray-400 text-xs uppercase">Keterangan</p>
            <p class="font-medium text-gray-700 mt-1">{{ $penjualanWilayah->keterangan }}</p>
        </div>
        @endif
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Produk</th>
                <th class="px-4 py-3 text-right">Jumlah</th>
                @if($penjualanWilayah->tipe === 'penjualan')
                <th class="px-4 py-3 text-right">Harga Agen</th>
                <th class="px-4 py-3 text-right">Subtotal</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($penjualanWilayah->details as $d)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-700">{{ $d->produk->nama }}</td>
                <td class="px-4 py-3 text-right text-gray-600">{{ number_format($d->jumlah) }} pcs</td>
                @if($penjualanWilayah->tipe === 'penjualan')
                <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($d->harga_agen) }}</td>
                <td class="px-4 py-3 text-right font-medium text-gray-700">Rp {{ number_format($d->subtotal) }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
        @if($penjualanWilayah->tipe === 'penjualan')
        <tfoot class="bg-gray-50">
            <tr>
                <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-600">Total</td>
                <td class="px-4 py-3 text-right font-bold text-gray-700">
                    Rp {{ number_format($penjualanWilayah->total) }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

@endsection
