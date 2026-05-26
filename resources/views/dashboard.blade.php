@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-700">Dashboard</h2>
            <div id="live-clock" class="text-sm text-gray-400 mt-1">
                Selamat datang, <span class="font-medium" style="color:#A51616">{{ auth()->user()->name }}</span>
                &nbsp;·&nbsp;
                <span id="clock-date"></span>
                <span class="font-mono text-gray-500" id="clock-time"></span>
            </div>
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
        <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-red-600">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Omset</p>
            <p class="text-2xl font-bold mt-1" style="color:#A51616">Rp {{ number_format($omsetHariIni) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-green-400">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Laba</p>
            <p class="text-2xl font-bold text-green-500 mt-1">Rp {{ number_format($labaHariIni) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-blue-400">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Total Setor</p>
            <p class="text-2xl font-bold text-blue-500 mt-1">Rp {{ number_format($setorHariIni) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-yellow-400">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Total OUT</p>
            <p class="text-2xl font-bold text-purple-500 mt-1">{{ number_format($totalOutHariIni) }} pcs</p>
        </div>
    </div>

    {{-- Bulan Ini + Outlet --}}
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Bulan Ini</p>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm">
            <p class="text-xs text-gray-400 uppercase tracking-wide">Omset Bulan Ini</p>
            <p class="text-2xl font-bold mt-1" style="color:#A51616">Rp {{ number_format($omsetBulanIni) }}</p>
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
                <div class="h-1.5 rounded-full" style="width: {{ $pct }}%; background-color:#A51616"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1">{{ $pct }}% sudah lapor</p>
        </div>
    </div>

    {{-- Notifikasi Pindah Stok Menunggu --}}
    @if(auth()->user()->hasRole(['koordinator', 'admin_pusat']) && $pindahStokMenunggu > 0)
        <a href="{{ route('transaksi.penjualan-wilayah.index', ['status' => 'menunggu', 'tipe' => 'transfer']) }}"
            class="flex items-center gap-4 border rounded-xl p-4 mb-6 hover:opacity-90 transition"
            style="background-color:#FFFDE7;border-color:#F5F028">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-gray-800 font-bold text-sm flex-shrink-0"
                style="background-color:#F5F028">
                {{ $pindahStokMenunggu }}
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-yellow-800">Pindah Stok Menunggu Persetujuan</p>
                <p class="text-xs text-yellow-700">
                    {{ $pindahStokMenunggu }} permintaan pindah stok
                    {{ auth()->user()->hasRole('koordinator') ? 'ke wilayah Anda' : '' }}
                    menunggu tindakan → klik untuk review
                </p>
            </div>
            <i class="fa-solid fa-chevron-right text-yellow-600"></i>
        </a>
    @endif

    {{-- Shortcut --}}
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Akses Cepat</p>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">

        @if(auth()->user()->hasRole(['admin_pusat']))
            <a href="{{ route('stok.masuk.index') }}"
                class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md hover:border-red-200 border border-transparent transition flex items-center gap-3">
                <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center text-red-700 text-lg">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Stok Masuk</p>
                    <p class="text-xs text-gray-400">Lihat & tambah stok</p>
                </div>
            </a>
        @endif

        @if(auth()->user()->hasRole(['admin_pusat', 'koordinator']))
            <a href="{{ route('stok.distribusi.index') }}"
                class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md hover:border-red-200 border border-transparent transition flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 text-lg">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Distribusi</p>
                    <p class="text-xs text-gray-400">Lihat & input OUT</p>
                </div>
            </a>
            <a href="{{ route('transaksi.laporan-harian.index') }}"
                class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md hover:border-red-200 border border-transparent transition flex items-center gap-3">
                <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center text-green-600 text-lg">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Laporan Harian</p>
                    <p class="text-xs text-gray-400">Lihat & input laporan</p>
                </div>
            </a>
        @endif

        @if(auth()->user()->hasRole(['admin_pusat', 'koordinator', 'owner']))
            <a href="{{ route('stok.rekap') }}"
                class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md hover:border-red-200 border border-transparent transition flex items-center gap-3">
                <div class="w-9 h-9 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600 text-lg">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Stok Freezer</p>
                    <p class="text-xs text-gray-400">Cek stok freezer</p>
                </div>
            </a>
        @endif

        @if(auth()->user()->hasRole('owner'))
            <a href="{{ route('laporan.omset') }}"
                class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md hover:border-red-200 border border-transparent transition flex items-center gap-3">
                <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center text-red-700 text-lg">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Rekap Omset</p>
                    <p class="text-xs text-gray-400">Lihat laporan</p>
                </div>
            </a>
            <a href="{{ route('laporan.kontrol') }}"
                class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md hover:border-red-200 border border-transparent transition flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 text-lg">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Kontrol Penjualan</p>
                    <p class="text-xs text-gray-400">Monitor outlet</p>
                </div>
            </a>
            <a href="{{ route('laporan.rata-rata-out') }}"
                class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md hover:border-red-200 border border-transparent transition flex items-center gap-3">
                <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center text-green-600 text-lg">
                    <i class="fa-solid fa-ruler-horizontal"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Rata-rata OUT</p>
                    <p class="text-xs text-gray-400">Lihat rata-rata</p>
                </div>
            </a>
        @endif

    </div>

    {{-- Tren 7 Hari Terakhir --}}
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Tren 7 Hari Terakhir</p>
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
        <canvas id="chart-tren" height="90"></canvas>
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

@push('scripts')
    <script>
        // Live clock
        function updateClock() {
            const now = new Date();
            document.getElementById('clock-date').textContent =
                now.toLocaleDateString('id-ID', {
                    weekday: 'long', day: 'numeric',
                    month: 'long', year: 'numeric',
                    timeZone: 'Asia/Jakarta'
                });
            document.getElementById('clock-time').textContent =
                now.toLocaleTimeString('id-ID', {
                    hour: '2-digit', minute: '2-digit',
                    timeZone: 'Asia/Jakarta', hour12: false
                }) + ' WIB';
        }

        updateClock();

        // Hitung sisa waktu sampai menit berikutnya, baru mulai interval
        const now = new Date();
        const msToNextMinute = (60 - now.getSeconds()) * 1000 - now.getMilliseconds();
        setTimeout(() => {
            updateClock();
            setInterval(updateClock, 60000);
        }, msToNextMinute);

        // Tren 7 Hari Chart
        document.addEventListener('DOMContentLoaded', function () {
            var labels = @json($tren7Hari->pluck('tanggal')->values());
            labels = labels.map(function(d) {
                var dt = new Date(d + 'T00:00:00');
                return dt.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'numeric' });
            });
            var data = @json($tren7Hari->pluck('omset')->values());
            var ctx = document.getElementById('chart-tren');
            if (!ctx || typeof Chart === 'undefined') return;
            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Omset',
                        data: data,
                        backgroundColor: '#A51616',
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    return 'Rp ' + ctx.raw.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6' },
                            ticks: {
                                callback: function (val) {
                                    if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + 'jt';
                                    if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + 'rb';
                                    return 'Rp ' + val;
                                }
                            }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
@endpush