@extends('layouts.app')
@section('title', 'Detail Stok Opname')

@section('content')

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('stok.opname.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali</a>
        <h2 class="text-2xl font-bold text-gray-700">Detail Stok Opname</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-400 uppercase">Tanggal</p>
                <p class="font-medium text-gray-700 mt-1">
                    {{ \Carbon\Carbon::parse($stokOpname->tanggal)->locale('id')->isoFormat('D MMMM Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase">Wilayah</p>
                <p class="font-medium text-gray-700 mt-1">{{ $stokOpname->wilayah->nama }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase">Status</p>
                <span
                    class="inline-block mt-1 px-2 py-1 rounded-full text-xs {{ $stokOpname->status === 'final' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">
                    {{ ucfirst($stokOpname->status) }}
                </span>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase">Dibuat Oleh</p>
                <p class="font-medium text-gray-700 mt-1">{{ $stokOpname->createdBy?->name ?? '-' }}</p>
            </div>
        </div>
        @if($stokOpname->keterangan)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400 uppercase">Keterangan</p>
                <p class="text-gray-600 mt-1">{{ $stokOpname->keterangan }}</p>
            </div>
        @endif
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-400">
            <p class="text-xs text-gray-400 uppercase">Total Produk</p>
            <p class="text-xl font-bold text-blue-500 mt-1">{{ $stokOpname->details->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-400">
            <p class="text-xs text-gray-400 uppercase">Sesuai</p>
            <p class="text-xl font-bold text-green-500 mt-1">{{ $stokOpname->details->where('selisih', 0)->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-red-400">
            <p class="text-xs text-gray-400 uppercase">Selisih</p>
            <p class="text-xl font-bold text-red-500 mt-1">{{ $stokOpname->details->where('selisih', '!=', 0)->count() }}
            </p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-orange-400">
            <p class="text-xs text-gray-400 uppercase">Nilai Selisih</p>
            <p
                class="text-xl font-bold {{ $stokOpname->details->sum('nilai_selisih') < 0 ? 'text-red-500' : 'text-orange-500' }} mt-1">
                Rp {{ number_format($stokOpname->details->sum('nilai_selisih')) }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-center w-10">No</th>
                    <th class="px-4 py-3 text-left">Produk</th>
                    <th class="px-4 py-3 text-right">Stok Sistem</th>
                    <th class="px-4 py-3 text-right">Stok Fisik</th>
                    <th class="px-4 py-3 text-right">Selisih</th>
                    <th class="px-4 py-3 text-right">HPP</th>
                    <th class="px-4 py-3 text-right">Nilai Selisih</th>
                    <th class="px-4 py-3 text-center">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($stokOpname->details as $d)
                    <tr
                        class="hover:bg-gray-50 {{ $d->selisih < 0 ? 'bg-red-50/30' : ($d->selisih > 0 ? 'bg-green-50/30' : '') }}">
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $d->produk->nama }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($d->stok_sistem) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format($d->stok_fisik) }}</td>
                        <td
                            class="px-4 py-3 text-right font-medium {{ $d->selisih < 0 ? 'text-red-500' : ($d->selisih > 0 ? 'text-green-600' : 'text-gray-400') }}">
                            {{ $d->selisih > 0 ? '+' : '' }}{{ number_format($d->selisih) }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">Rp {{ number_format($d->hpp_snapshot) }}</td>
                        <td
                            class="px-4 py-3 text-right font-medium {{ $d->nilai_selisih < 0 ? 'text-red-500' : ($d->nilai_selisih > 0 ? 'text-green-600' : 'text-gray-400') }}">
                            Rp {{ number_format($d->nilai_selisih) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($d->selisih == 0)
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-600">Sesuai</span>
                            @elseif($d->selisih < 0)
                                <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-500">Kurang</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-600">Lebih</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 font-semibold">
                <tr>
                    <td colspan="2" class="px-4 py-3 text-gray-600">Total</td>
                    <td class="px-4 py-3 text-right text-gray-700">
                        {{ number_format($stokOpname->details->sum('stok_sistem')) }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">
                        {{ number_format($stokOpname->details->sum('stok_fisik')) }}</td>
                    <td
                        class="px-4 py-3 text-right {{ $stokOpname->details->sum('selisih') < 0 ? 'text-red-500' : 'text-green-600' }}">
                        {{ $stokOpname->details->sum('selisih') > 0 ? '+' : '' }}{{ number_format($stokOpname->details->sum('selisih')) }}
                    </td>
                    <td></td>
                    <td
                        class="px-4 py-3 text-right {{ $stokOpname->details->sum('nilai_selisih') < 0 ? 'text-red-500' : 'text-gray-700' }}">
                        Rp {{ number_format($stokOpname->details->sum('nilai_selisih')) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

@endsection