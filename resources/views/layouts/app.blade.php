<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIMSYS — @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">

    {{-- Sidebar --}}
    <div class="flex min-h-screen overflow-hidden">
        <aside class="w-64 bg-white flex flex-col fixed top-0 left-0 h-screen overflow-y-auto border-r border-gray-100">

            {{-- Logo --}}
            <div class="px-6 py-4 border-b border-gray-100 flex-shrink-0">
                <h1 class="text-2xl font-bold text-orange-500 tracking-tight">DIMSYS</h1>
                <p class="text-xs text-gray-400 mt-0.5">Dimsum In Management System</p>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 px-3 py-1 space-y-0.5 text-sm overflow-y-auto">
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all
        {{ request()->routeIs('dashboard') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                    <i class="fa-solid fa-house w-4 text-center"></i> Dashboard
                </a>

                @if(auth()->user()->hasRole(['admin_pusat', 'owner']))
                    <p class="px-3 pt-5 pb-1.5 text-xs text-gray-300 uppercase tracking-widest font-semibold">Master Data
                    </p>
                    <a href="{{ route('master.wilayah.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                        {{ request()->routeIs('master.wilayah.*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fa-solid fa-map-location-dot w-4 text-center"></i> Wilayah
                    </a>
                    <a href="{{ route('master.produk.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                        {{ request()->routeIs('master.produk.*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fa-solid fa-box-open w-4 text-center"></i> Produk
                    </a>
                    <a href="{{ route('master.outlet.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                        {{ request()->routeIs('master.outlet.*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fa-solid fa-store w-4 text-center"></i> Outlet
                    </a>
                    <a href="{{ route('master.supplier.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                        {{ request()->routeIs('master.supplier.*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fa-solid fa-truck w-4 text-center"></i> Supplier
                    </a>
                @endif

                @if(auth()->user()->hasRole(['admin_pusat', 'koordinator', 'owner']))
                    <p class="px-3 pt-5 pb-1.5 text-xs text-gray-300 uppercase tracking-widest font-semibold">Stok</p>
                    <a href="{{ route('stok.masuk.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                        {{ request()->routeIs('stok.masuk.*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fa-solid fa-boxes-stacked w-4 text-center"></i> Stok Masuk
                    </a>
                    <a href="{{ route('stok.distribusi.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                        {{ request()->routeIs('stok.distribusi.*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fa-solid fa-truck-fast w-4 text-center"></i> Distribusi
                    </a>
                    <a href="{{ route('stok.rekap') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                        {{ request()->routeIs('stok.rekap*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fa-solid fa-chart-pie w-4 text-center"></i> Stok Freezer
                    </a>
                    <a href="{{ route('stok.opname.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                        {{ request()->routeIs('stok.opname*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fa-solid fa-clipboard-check w-4 text-center"></i> Stok Opname
                    </a>
                    @if(auth()->user()->hasRole(['admin_pusat', 'koordinator']))
                    <a href="{{ route('stok.generate-awal') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                        {{ request()->routeIs('stok.generate-awal*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fa-solid fa-arrows-rotate w-4 text-center"></i> Generate Stok Awal
                    </a>
                    @endif

                    <p class="px-3 pt-5 pb-1.5 text-xs text-gray-300 uppercase tracking-widest font-semibold">Transaksi</p>
                    <a href="{{ route('transaksi.laporan-harian.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                        {{ request()->routeIs('transaksi.laporan-harian.*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fa-solid fa-file-lines w-4 text-center"></i> Laporan Harian
                    </a>
                    <a href="{{ route('transaksi.kas.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                        {{ request()->routeIs('transaksi.kas.*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fa-solid fa-wallet w-4 text-center"></i> Kas Harian
                    </a>
                @endif

                @if(auth()->user()->hasRole(['admin_pusat', 'koordinator', 'owner']))
                    <a href="{{ route('transaksi.penjualan-wilayah.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
                        {{ request()->routeIs('transaksi.penjualan-wilayah.*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                        <i class="fa-solid fa-city w-4 text-center"></i> Pindah Stok
                    </a>
                @endif

                <p class="px-3 pt-5 pb-1.5 text-xs text-gray-300 uppercase tracking-widest font-semibold">Laporan</p>
                <a href="{{ route('laporan.omset') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
        {{ request()->routeIs('laporan.omset*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                    <i class="fa-solid fa-chart-line w-4 text-center"></i> Rekap Omset
                </a>
                <a href="{{ route('laporan.kontrol') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
        {{ request()->routeIs('laporan.kontrol*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                    <i class="fa-solid fa-sliders w-4 text-center"></i> Kontrol Penjualan
                </a>
                <a href="{{ route('laporan.stok') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
        {{ request()->routeIs('laporan.stok*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                    <i class="fa-solid fa-chart-bar w-4 text-center"></i> Rekap Stok
                </a>
                <a href="{{ route('laporan.rata-rata-out') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all
        {{ request()->routeIs('laporan.rata-rata-out*') ? 'bg-orange-500 text-white shadow-sm font-medium' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600' }}">
                    <i class="fa-solid fa-ruler-horizontal w-4 text-center"></i> Rata-rata OUT
                </a>

            </nav>

            {{-- User Info --}}
            <div class="px-3 py-3 border-t border-gray-100 flex-shrink-0">
                <div class="flex items-center gap-3 px-3 py-2 rounded-lg bg-gray-50 mb-2">
                    <div
                        class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
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
                    confirmButtonColor: '#f97316', confirmButtonText: 'Tutup',
                });
            });
        </script>
    @endif
    <script>
        // Prevent double submit - disable button after first click
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form').forEach(function (form) {
                // Skip form yang pakai data-confirm (sudah dihandle SweetAlert)
                if (form.dataset.confirm) return;

                form.addEventListener('submit', function () {
                    const btn = form.querySelector('button[type="submit"]');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span style="opacity:0.7">Menyimpan...</span>';

                        // Re-enable setelah 5 detik kalau ada error
                        setTimeout(() => {
                            btn.disabled = false;
                            btn.innerHTML = btn.dataset.originalText || 'Simpan';
                        }, 5000);
                    }
                });
            });

            // Untuk form dengan data-confirm, disable setelah konfirmasi
            document.querySelectorAll('form[data-confirm]').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const msg = form.dataset.confirm || 'Yakin ingin melanjutkan?';
                    Swal.fire({
                        title: 'Konfirmasi',
                        text: msg,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#f97316',
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
</body>

</html>