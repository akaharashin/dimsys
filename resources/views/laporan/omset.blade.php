@extends('layouts.app')
@section('title', 'Rekap Omset')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Rekap Omset</h2>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('laporan.omset') }}" class="flex flex-wrap items-end gap-3">
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
                            <option value="{{ $w->id }}" {{ $wilayahId === $w->id ? 'selected' : '' }}>{{ $w->nama }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg">Tampilkan</button>
                <a href="{{ route('laporan.omset.export', ['bulan' => $bulan, 'wilayah_id' => $wilayahId]) }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg"><i
                        class="fa-solid fa-file-excel mr-1"></i> Export Excel</a>
                <a href="{{ route('laporan.export-bulanan', ['bulan' => $bulan, 'wilayah_id' => $wilayahId]) }}"
                    class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center gap-2">
                    <i class="fa-solid fa-file-excel"></i> Export Rekap Bulanan
                </a>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-red-600">
            <p class="text-xs text-gray-400 uppercase">Total Omset</p>
            <p class="text-lg font-bold text-red-600 mt-1">Rp {{ number_format($totalOmset) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-red-400">
            <p class="text-xs text-gray-400 uppercase">Total Modal</p>
            <p class="text-lg font-bold text-red-400 mt-1">Rp {{ number_format($totalModal) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-yellow-400">
            <p class="text-xs text-gray-400 uppercase">Total Komisi</p>
            <p class="text-lg font-bold text-yellow-500 mt-1">Rp {{ number_format($totalKomisi) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-400">
            <p class="text-xs text-gray-400 uppercase">Total Laba</p>
            <p class="text-lg font-bold text-green-500 mt-1">Rp {{ number_format($totalLaba) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-400">
            <p class="text-xs text-gray-400 uppercase">Total Setor</p>
            <p class="text-lg font-bold text-blue-500 mt-1">Rp {{ number_format($totalSetor) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        {{-- Rekap Per Outlet --}}
        <div class="md:col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-600">Rekap Per Outlet</h3>
                <span class="text-xs text-gray-400">{{ $rekapOutlet->count() }} outlet</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-center w-10">No</th>
                            <th class="px-4 py-3 text-left">Outlet</th>
                            <th class="px-4 py-3 text-right">Hari</th>
                            <th class="px-4 py-3 text-right">Terjual</th>
                            <th class="px-4 py-3 text-right">Omset</th>
                            <th class="px-4 py-3 text-right">Laba</th>
                            <th class="px-4 py-3 text-right">Setor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($rekapOutlet as $r)

                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 text-center text-gray-400 text-xs">{{ $loop->iteration }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <p class="font-medium text-gray-700">{{ $r['outlet']->nama }}</p>
                                    <p class="text-xs text-gray-400">{{ $r['outlet']->wilayah->nama }}</p>
                                </td>
                                <td class="px-4 py-2.5 text-right text-gray-500">{{ $r['hari'] }}h</td>
                                <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($r['terjual']) }}</td>
                                <td class="px-4 py-2.5 text-right text-gray-700">Rp {{ number_format($r['omset']) }}</td>
                                <td class="px-4 py-2.5 text-right font-medium text-green-600">Rp {{ number_format($r['laba']) }}
                                </td>
                                <td class="px-4 py-2.5 text-right text-blue-600">Rp {{ number_format($r['setor']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($rekapOutlet->count())
                        <tfoot class="bg-gray-50 font-semibold text-xs">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-gray-600">Total</td>
                                <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format($totalOmset) }}</td>
                                <td class="px-4 py-3 text-right text-green-600">Rp {{ number_format($totalLaba) }}</td>
                                <td class="px-4 py-3 text-right text-blue-600">Rp {{ number_format($totalSetor) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Produk Terlaris --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-600">Top 5 Produk Terlaris</h3>
            </div>
            <div class="p-4 space-y-3">
                @forelse($produkTerlaris as $pt)

                    @php $maxTerjual = $produkTerlaris->first()->total_terjual; @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">{{ $loop->iteration }}.
                                {{ $pt->produk->nama }}</span>
                            <span class="text-xs text-gray-500">{{ number_format($pt->total_terjual) }} pcs</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full"
                                style="width: {{ $maxTerjual > 0 ? round($pt->total_terjual / $maxTerjual * 100) : 0 }}%;background-color:#A51616">
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">Rp {{ number_format($pt->total_omset) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">Tidak ada data.</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Tren Harian Chart --}}
    @if(count($rekapHarian))
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-600 mb-4">Tren Omset Harian</h3>
        <canvas id="chartOmsetHarian" height="80"></canvas>
    </div>
    @endif

    {{-- Rekap Harian --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-600">Rekap Harian</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-center w-10">No</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-right">Omset</th>
                        <th class="px-4 py-3 text-right">Laba</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rekapHarian as $h)

                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-center text-gray-400 text-xs">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2.5 text-gray-700">
                                {{ \Carbon\Carbon::parse($h['tanggal'])->locale('id')->isoFormat('dddd, D MMMM Y') }}

                            </td>
                            <td class="px-4 py-2.5 text-right text-gray-700">Rp {{ number_format($h['omset']) }}</td>
                            <td class="px-4 py-2.5 text-right font-medium text-green-600">Rp {{ number_format($h['laba']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400">Tidak ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@push('scripts')
@if(count($rekapHarian))
<script>
(function() {
    if (typeof Chart === 'undefined') return;
    var labels = @json($rekapHarian->pluck('tanggal')->values());
    labels = labels.map(function(d) {
        var dt = new Date(d + 'T00:00:00');
        return dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    });
    var omset = @json($rekapHarian->pluck('omset')->values());
    var laba  = @json($rekapHarian->pluck('laba')->values());
    new Chart(document.getElementById('chartOmsetHarian').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Omset',
                    data: omset,
                    borderColor: '#A51616',
                    backgroundColor: 'rgba(165,22,22,0.08)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#A51616'
                },
                {
                    label: 'Laba',
                    data: laba,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22,163,74,0.06)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#16a34a'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(v) { return 'Rp ' + (v/1000).toFixed(0) + 'k'; },
                        font: { size: 10 }
                    }
                },
                x: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });
})();
</script>
@endif
@endpush

@endsection