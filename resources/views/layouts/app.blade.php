<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIMSYS — @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">

    {{-- Sidebar --}}
    <div class="flex min-h-screen overflow-hidden">
        <aside class="w-64 bg-white flex flex-col fixed top-0 left-0 h-screen overflow-y-auto border-r border-gray-100">

            {{-- Logo --}}
            <div class="px-6 py-4 border-b border-gray-100 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('dimsumin.png') }}" alt="Logo" class="h-14 w-14 object-contain rounded-full">
                    <div class="leading-tight">
                        <h1 class="text-xl font-bold tracking-tight" style="color:#A51616">DIMSYS</h1>
                        <p class="text-xs text-gray-400">Dimsum In Management System</p>
                    </div>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 px-3 py-1 text-sm overflow-y-auto">

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2.5 mt-1 rounded-lg font-medium transition-all
                           {{ request()->routeIs('dashboard') ? 'text-white shadow-sm' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                    @if(request()->routeIs('dashboard')) style="background-color:#A51616" @endif>
                    <i class="fa-solid fa-house w-4 text-center"></i> Dashboard
                </a>

                @if(auth()->user()->hasRole(['admin_pusat', 'owner']))
                    {{-- MASTER DATA --}}
                    <button class="sidebar-section-toggle w-full flex items-center justify-between px-3 pt-4 pb-1.5 text-xs text-gray-400 uppercase tracking-widest font-semibold hover:text-gray-600 transition-colors"
                        data-section="master-data">
                        <span>Master Data</span>
                        <i class="fa-solid fa-chevron-down text-xs section-chevron" style="transition:transform 0.2s ease;"></i>
                    </button>
                    <div class="sidebar-section-content" data-section-content="master-data" style="overflow:hidden;">
                        <div class="space-y-0.5 pb-0.5">
                            <a href="{{ route('master.wilayah.index') }}"
                                class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                       {{ request()->routeIs('master.wilayah.*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                                @if(request()->routeIs('master.wilayah.*')) style="background-color:#A51616" @endif>
                                <i class="fa-solid fa-map-location-dot w-4 text-center"></i> Wilayah
                            </a>
                            <a href="{{ route('master.produk.index') }}"
                                class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                       {{ request()->routeIs('master.produk.*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                                @if(request()->routeIs('master.produk.*')) style="background-color:#A51616" @endif>
                                <i class="fa-solid fa-box-open w-4 text-center"></i> Produk
                            </a>
                            <a href="{{ route('master.outlet.index') }}"
                                class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                       {{ request()->routeIs('master.outlet.*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                                @if(request()->routeIs('master.outlet.*')) style="background-color:#A51616" @endif>
                                <i class="fa-solid fa-store w-4 text-center"></i> Outlet
                            </a>
                            <a href="{{ route('master.supplier.index') }}"
                                class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                       {{ request()->routeIs('master.supplier.*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                                @if(request()->routeIs('master.supplier.*')) style="background-color:#A51616" @endif>
                                <i class="fa-solid fa-truck w-4 text-center"></i> Supplier
                            </a>
                        </div>
                    </div>
                @endif

                @if(auth()->user()->hasRole(['admin_pusat', 'koordinator', 'owner']))
                    {{-- STOK --}}
                    <button class="sidebar-section-toggle w-full flex items-center justify-between px-3 pt-4 pb-1.5 text-xs text-gray-400 uppercase tracking-widest font-semibold hover:text-gray-600 transition-colors"
                        data-section="stok">
                        <span>Stok</span>
                        <i class="fa-solid fa-chevron-down text-xs section-chevron" style="transition:transform 0.2s ease;"></i>
                    </button>
                    <div class="sidebar-section-content" data-section-content="stok" style="overflow:hidden;">
                        <div class="space-y-0.5 pb-0.5">
                            <a href="{{ route('stok.masuk.index') }}"
                                class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                       {{ request()->routeIs('stok.masuk.*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                                @if(request()->routeIs('stok.masuk.*')) style="background-color:#A51616" @endif>
                                <i class="fa-solid fa-boxes-stacked w-4 text-center"></i> Stok Masuk
                            </a>
                            <a href="{{ route('stok.distribusi.index') }}"
                                class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                       {{ request()->routeIs('stok.distribusi.*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                                @if(request()->routeIs('stok.distribusi.*')) style="background-color:#A51616" @endif>
                                <i class="fa-solid fa-truck-fast w-4 text-center"></i> Distribusi
                            </a>
                            <a href="{{ route('stok.rekap') }}"
                                class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                       {{ request()->routeIs('stok.rekap*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                                @if(request()->routeIs('stok.rekap*')) style="background-color:#A51616" @endif>
                                <i class="fa-solid fa-chart-pie w-4 text-center"></i> Stok Freezer
                            </a>
                            <a href="{{ route('stok.opname.index') }}"
                                class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                       {{ request()->routeIs('stok.opname*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                                @if(request()->routeIs('stok.opname*')) style="background-color:#A51616" @endif>
                                <i class="fa-solid fa-clipboard-check w-4 text-center"></i> Stok Opname
                            </a>
                            @if(auth()->user()->hasRole(['admin_pusat', 'koordinator']))
                                <a href="{{ route('stok.generate-awal') }}"
                                    class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                           {{ request()->routeIs('stok.generate-awal*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                                    @if(request()->routeIs('stok.generate-awal*')) style="background-color:#A51616" @endif>
                                    <i class="fa-solid fa-arrows-rotate w-4 text-center"></i> Generate Stok Awal
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- TRANSAKSI --}}
                    <button class="sidebar-section-toggle w-full flex items-center justify-between px-3 pt-4 pb-1.5 text-xs text-gray-400 uppercase tracking-widest font-semibold hover:text-gray-600 transition-colors"
                        data-section="transaksi">
                        <span>Transaksi</span>
                        <i class="fa-solid fa-chevron-down text-xs section-chevron" style="transition:transform 0.2s ease;"></i>
                    </button>
                    <div class="sidebar-section-content" data-section-content="transaksi" style="overflow:hidden;">
                        <div class="space-y-0.5 pb-0.5">
                            <a href="{{ route('transaksi.laporan-harian.index') }}"
                                class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                       {{ request()->routeIs('transaksi.laporan-harian.*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                                @if(request()->routeIs('transaksi.laporan-harian.*')) style="background-color:#A51616" @endif>
                                <i class="fa-solid fa-file-lines w-4 text-center"></i> Laporan Harian
                            </a>
                            <a href="{{ route('transaksi.kas.index') }}"
                                class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                       {{ request()->routeIs('transaksi.kas.*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                                @if(request()->routeIs('transaksi.kas.*')) style="background-color:#A51616" @endif>
                                <i class="fa-solid fa-wallet w-4 text-center"></i> Kas Harian
                            </a>
                            <a href="{{ route('transaksi.penjualan-wilayah.index') }}"
                                class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                       {{ request()->routeIs('transaksi.penjualan-wilayah.*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                                @if(request()->routeIs('transaksi.penjualan-wilayah.*')) style="background-color:#A51616" @endif>
                                <i class="fa-solid fa-city w-4 text-center"></i> Pindah Stok
                            </a>
                        </div>
                    </div>
                @endif

                {{-- LAPORAN --}}
                <button class="sidebar-section-toggle w-full flex items-center justify-between px-3 pt-4 pb-1.5 text-xs text-gray-400 uppercase tracking-widest font-semibold hover:text-gray-600 transition-colors"
                    data-section="laporan">
                    <span>Laporan</span>
                    <i class="fa-solid fa-chevron-down text-xs section-chevron" style="transition:transform 0.2s ease;"></i>
                </button>
                <div class="sidebar-section-content" data-section-content="laporan" style="overflow:hidden;">
                    <div class="space-y-0.5 pb-0.5">
                        <a href="{{ route('laporan.omset') }}"
                            class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                   {{ request()->routeIs('laporan.omset*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                            @if(request()->routeIs('laporan.omset*')) style="background-color:#A51616" @endif>
                            <i class="fa-solid fa-chart-line w-4 text-center"></i> Rekap Omset
                        </a>
                        <a href="{{ route('laporan.kontrol') }}"
                            class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                   {{ request()->routeIs('laporan.kontrol*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                            @if(request()->routeIs('laporan.kontrol*')) style="background-color:#A51616" @endif>
                            <i class="fa-solid fa-sliders w-4 text-center"></i> Kontrol Penjualan
                        </a>
                        <a href="{{ route('laporan.stok') }}"
                            class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                   {{ request()->routeIs('laporan.stok*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                            @if(request()->routeIs('laporan.stok*')) style="background-color:#A51616" @endif>
                            <i class="fa-solid fa-chart-bar w-4 text-center"></i> Rekap Stok
                        </a>
                        <a href="{{ route('laporan.rata-rata-out') }}"
                            class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                   {{ request()->routeIs('laporan.rata-rata-out*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                            @if(request()->routeIs('laporan.rata-rata-out*')) style="background-color:#A51616" @endif>
                            <i class="fa-solid fa-ruler-horizontal w-4 text-center"></i> Rata-rata OUT
                        </a>
                    </div>
                </div>

                @if(auth()->user()->hasRole('admin_pusat'))
                    {{-- ADMIN --}}
                    <button class="sidebar-section-toggle w-full flex items-center justify-between px-3 pt-4 pb-1.5 text-xs text-gray-400 uppercase tracking-widest font-semibold hover:text-gray-600 transition-colors"
                        data-section="admin">
                        <span>Admin</span>
                        <i class="fa-solid fa-chevron-down text-xs section-chevron" style="transition:transform 0.2s ease;"></i>
                    </button>
                    <div class="sidebar-section-content" data-section-content="admin" style="overflow:hidden;">
                        <div class="space-y-0.5 pb-0.5">
                            <a href="{{ route('admin.activity-log') }}"
                                class="flex items-center gap-3 pl-5 pr-2 py-2.5 rounded-lg transition-all
                                       {{ request()->routeIs('admin.activity-log*') ? 'text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-red-50 hover:text-red-700' }}"
                                @if(request()->routeIs('admin.activity-log*')) style="background-color:#A51616" @endif>
                                <i class="fa-solid fa-clock-rotate-left w-4 text-center"></i> Activity Log
                            </a>
                        </div>
                    </div>
                @endif

            </nav>

            {{-- User Info --}}
            <div class="px-3 py-3 border-t border-gray-100 flex-shrink-0">
                <div class="flex items-center gap-3 px-3 py-2 rounded-lg bg-gray-50 border-l-4 border-red-600 mb-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
                        style="background-color:#A51616">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-700 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">
                            {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
                        </p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" data-confirm="Yakin ingin keluar?">
                    @csrf
                    <button
                        class="w-full text-left px-3 py-2 text-sm text-red-500 hover:bg-red-50 rounded-lg transition-all flex items-center gap-2">
                        <i class="fa-solid fa-right-from-bracket w-4 text-center"></i> Logout
                    </button>
                </form>
            </div>

        </aside>

        {{-- Main Content --}}
        <main class="flex-1 p-6 overflow-y-auto ml-64">
            {{-- Fallback banners: selalu tampil, akan diganti SweetAlert kalau Swal berhasil load --}}
            @if(session('success'))
                <div id="html-flash-success"
                    class="mb-4 p-3 bg-green-100 border border-green-300 text-green-700 rounded-lg flex items-center gap-2 text-sm">
                    <span class="flex-1">✅ {{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="font-bold text-green-600">✕</button>
                </div>
            @endif
            @if(session('error'))
                <div id="html-flash-error"
                    class="mb-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded-lg flex items-center gap-2 text-sm">
                    <span class="flex-1">❌ {{ session('error') }}</span>
                    <button onclick="this.parentElement.remove()" class="font-bold text-red-600">✕</button>
                </div>
            @endif
            @if(session('warning'))
                <div id="html-flash-warning"
                    class="mb-4 p-3 bg-yellow-100 border border-yellow-300 text-yellow-700 rounded-lg flex items-center gap-2 text-sm">
                    <span class="flex-1">⚠️ {{ session('warning') }}</span>
                    <button onclick="this.parentElement.remove()" class="font-bold text-yellow-600">✕</button>
                </div>
            @endif
            @if($errors->any())
                <div id="html-flash-validation"
                    class="mb-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded-lg text-sm">
                    <p class="font-semibold mb-1">⚠️ Validasi Gagal:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button onclick="this.parentElement.remove()" class="mt-2 underline text-xs">Tutup</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal === 'undefined') return;
                var el = document.getElementById('html-flash-success');
                if (el) el.remove();
                Swal.fire({
                    icon: 'success', title: 'Berhasil!', text: @json(session('success')),
                    timer: 2500, timerProgressBar: true, showConfirmButton: false,
                    toast: true, position: 'top-end',
                });
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal === 'undefined') return;
                var el = document.getElementById('html-flash-error');
                if (el) el.remove();
                Swal.fire({
                    icon: 'error', title: 'Gagal!', text: @json(session('error')),
                    timer: 3500, timerProgressBar: true, showConfirmButton: false,
                    toast: true, position: 'top-end',
                });
            });
        </script>
    @endif

    @if(session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal === 'undefined') return;
                var el = document.getElementById('html-flash-warning');
                if (el) el.remove();
                Swal.fire({
                    icon: 'warning', title: 'Perhatian!', text: @json(session('warning')),
                    timer: 3500, timerProgressBar: true, showConfirmButton: false,
                    toast: true, position: 'top-end',
                });
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal === 'undefined') return;
                var el = document.getElementById('html-flash-validation');
                if (el) el.remove();
                var errorMessages = @json($errors->all());
                var html = '<ul style="text-align:left;padding-left:20px;margin:0">' +
                    errorMessages.map(function (e) { return '<li>' + e + '</li>'; }).join('') +
                    '</ul>';
                Swal.fire({
                    icon: 'error', title: 'Validasi Gagal', html: html,
                    confirmButtonColor: '#A51616', confirmButtonText: 'Tutup',
                });
            });
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form').forEach(function (form) {
                if (form.dataset.confirm) return;

                form.addEventListener('submit', function () {
                    const btn = form.querySelector('button[type="submit"]');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span style="opacity:0.7">Menyimpan...</span>';
                        setTimeout(() => {
                            btn.disabled = false;
                            btn.innerHTML = btn.dataset.originalText || 'Simpan';
                        }, 5000);
                    }
                });
            });

            document.querySelectorAll('form[data-confirm]').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const msg = form.dataset.confirm || 'Yakin ingin melanjutkan?';
                    Swal.fire({
                        title: 'Konfirmasi',
                        text: msg,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#A51616',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, lanjutkan',
                        cancelButtonText: 'Batal',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const btn = form.querySelector('button[type="submit"]');
                            if (btn) {
                                btn.disabled = true;
                                btn.innerHTML = '<span style="opacity:0.7">Memproses...</span>';
                            }
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    {{-- Sidebar collapsible --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var STORAGE_KEY = 'dimsys_sidebar_state';

        function loadState() {
            try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {}; }
            catch (e) { return {}; }
        }

        function saveState(state) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
        }

        function sectionHasActive(content) {
            return content.querySelector('a[style*="A51616"]') !== null;
        }

        function applyState(content, chevron, collapsed, animate) {
            if (!animate) {
                content.style.transition = 'none';
            } else {
                content.style.transition = 'max-height 0.22s ease';
            }

            if (collapsed) {
                content.style.maxHeight = '0px';
                chevron.style.transform = 'rotate(-90deg)';
            } else {
                content.style.maxHeight = content.scrollHeight + 'px';
                chevron.style.transform = 'rotate(0deg)';
            }

            // Re-enable transition after synchronous style flush
            if (!animate) {
                requestAnimationFrame(function () {
                    content.style.transition = 'max-height 0.22s ease';
                });
            }
        }

        var state = loadState();

        document.querySelectorAll('.sidebar-section-toggle').forEach(function (btn) {
            var key     = btn.dataset.section;
            var content = document.querySelector('[data-section-content="' + key + '"]');
            var chevron = btn.querySelector('.section-chevron');

            if (!content || !chevron) return;

            var hasActive  = sectionHasActive(content);
            // Default expanded; collapse only if explicitly saved false AND no active link
            var isExpanded = hasActive || state[key] !== false;

            applyState(content, chevron, !isExpanded, false);

            btn.addEventListener('click', function () {
                var isCollapsed = content.style.maxHeight === '0px';
                var hasActiveNow = sectionHasActive(content);

                // Prevent collapsing a section that contains the active page
                if (!isCollapsed && hasActiveNow) return;

                var nowCollapsed = !isCollapsed;
                applyState(content, chevron, nowCollapsed, true);

                var s = loadState();
                s[key] = !nowCollapsed; // true = expanded
                saveState(s);
            });
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @stack('scripts')
</body>

</html>
