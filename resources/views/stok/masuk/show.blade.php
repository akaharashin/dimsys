@extends('layouts.app')
@section('title', 'Detail Stok Masuk')

@section('content')

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('stok.masuk.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali</a>
        <h2 class="text-2xl font-bold text-gray-700">Detail Stok Masuk</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-gray-400 text-xs uppercase">Tanggal</p>
                <p class="font-medium text-gray-700 mt-1">{{ \Carbon\Carbon::parse($masuk->tanggal)->format('d M Y') }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs uppercase">Wilayah</p>
                <p class="font-medium text-gray-700 mt-1">{{ $masuk->wilayah->nama }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs uppercase">Supplier</p>
                <p class="font-medium text-gray-700 mt-1">{{ $masuk->supplier->nama }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-xs uppercase">Keterangan</p>
                <p class="font-medium text-gray-700 mt-1">{{ $masuk->keterangan ?? '-' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Produk</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3 text-right">HPP/pcs</th>
                    <th class="px-4 py-3 text-right">Total HPP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($masuk->details as $d)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $d->produk->nama }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($d->jumlah) }} pcs</td>
                        <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($d->hpp) }}</td>

                        <td class="px-4 py-3 text-right font-medium text-gray-700">Rp {{ number_format($d->jumlah * $d->hpp) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold text-gray-600">Total</td>
                    <td class="px-4 py-3 text-right font-bold text-gray-700">
                        Rp {{ number_format($masuk->details->sum(fn($d) => $d->jumlah * $d->produk->hpp)) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

@endsection