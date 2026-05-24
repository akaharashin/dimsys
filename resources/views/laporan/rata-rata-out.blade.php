@extends('layouts.app')
@section('title', 'Rata-rata OUT')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Rata-rata OUT</h2>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('laporan.rata-rata-out') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Bulan</label>
                <input type="month" name="bulan" value="{{ $bulan }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>
            @if(!auth()->user()->hasRole('koordinator'))
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Wilayah</label>
                    <select name="wilayah_id"
                        class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
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
                    class="px-4 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg">Tampilkan</button>
                <a href="{{ route('laporan.rata-rata-out.export', ['bulan' => $bulan, 'wilayah_id' => $wilayahId]) }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg"><i class="fa-solid fa-file-excel mr-1"></i> Export Excel</a>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-orange-400">
            <p class="text-xs text-gray-400 uppercase">Total Outlet</p>
            <p class="text-2xl font-bold text-orange-500 mt-1">{{ $outletList->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-400">
            <p class="text-xs text-gray-400 uppercase">Total Produk</p>
            <p class="text-2xl font-bold text-blue-500 mt-1">{{ $produkList->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-400">
            <p class="text-xs text-gray-400 uppercase">Bulan</p>
            <p class="text-xl font-bold text-green-600 mt-1">
                {{ \Carbon\Carbon::parse($bulan)->locale('id')->isoFormat('MMMM Y') }}
            </p>
        </div>
    </div>

    @if($outletList->count() && $produkList->count())

        {{-- Tabel Matrix --}}
        <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-600">
                    Rata-rata OUT per Outlet per Produk
                    <span class="text-xs text-gray-400 font-normal ml-2">(total/hari distribusi)</span>
                </h3>
            </div>
            <table class="text-sm" style="min-width: max-content; width: 100%;">
                <thead class="text-gray-500 uppercase text-xs" style="position:sticky;top:0;background:#f9fafb;z-index:10;">
                    <tr>
                        <th class="px-4 py-3 text-center w-10" style="position:sticky;left:0;background:#f9fafb;z-index:11;">No
                        </th>
                        <th class="px-4 py-3 text-left"
                            style="position:sticky;left:44px;background:#f9fafb;z-index:11;min-width:140px;">Outlet</th>
                        <th class="px-4 py-3 text-left"
                            style="position:sticky;left:184px;background:#f9fafb;z-index:11;min-width:100px;">Wilayah</th>
                        @foreach($produkList as $p)
                            <th class="px-4 py-3 text-right" style="min-width:90px;">
                                {{ $p->nama }}
                            </th>
                        @endforeach
                        <th class="px-4 py-3 text-right" style="min-width:100px;">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($outletList as $outlet)
                        @php
                            $outletData = $matrix[$outlet->id] ?? [];
                            $totalOutlet = collect($outletData)->sum('total');
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-center text-gray-400 text-xs"
                                style="position:sticky;left:0;background:inherit;">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-2.5 font-medium text-gray-700" style="position:sticky;left:44px;background:inherit;">
                                {{ $outlet->nama }}
                            </td>
                            <td class="px-4 py-2.5 text-gray-500" style="position:sticky;left:184px;background:inherit;">
                                {{ $outlet->wilayah->nama }}
                            </td>
                            @foreach($produkList as $p)
                                @php
                                    $data = $outletData[$p->id] ?? null;
                                    $total = $data['total'] ?? 0;
                                    $hari = $data['hari'] ?? 0;
                                    $rataRata = $hari > 0 ? round($total / $hari) : 0;
                                @endphp
                                <td class="px-4 py-2.5 text-right {{ $total > 0 ? 'text-gray-700' : 'text-gray-300' }}">
                                    @if($total > 0)
                                        <span class="font-medium">{{ number_format($rataRata) }}</span>
                                        <span class="text-xs text-gray-400">/hr</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-4 py-2.5 text-right font-bold text-orange-500">
                                {{ number_format($totalOutlet) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 font-semibold text-xs">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-gray-600" style="position:sticky;left:0;background:#f9fafb;">Total
                            per Produk</td>
                        @php $grandTotal = 0; @endphp
                        @foreach($produkList as $p)
                            @php
                                $totalProduk = collect($matrix)->sum(fn($outlet) => $outlet[$p->id]['total'] ?? 0);
                                $grandTotal += $totalProduk;
                            @endphp
                            <td class="px-4 py-3 text-right text-gray-700">{{ number_format($totalProduk) }}</td>
                        @endforeach
                        <td class="px-4 py-3 text-right text-orange-500">{{ number_format($grandTotal) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

    @else
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <p class="text-gray-400">Tidak ada data distribusi untuk bulan ini.</p>
        </div>
    @endif

@endsection