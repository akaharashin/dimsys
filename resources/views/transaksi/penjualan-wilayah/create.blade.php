@extends('layouts.app')
@section('title', 'Tambah Pindah Stok')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('transaksi.penjualan-wilayah.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali</a>
    <h2 class="text-2xl font-bold text-gray-700">Tambah Pindah Stok</h2>
</div>

@if($errors->any())
    <div class="mb-4 px-4 py-3 bg-red-100 text-red-700 rounded-lg text-sm">
        {{ $errors->first() }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 px-4 py-3 bg-red-100 text-red-700 rounded-lg text-sm">
        {{ session('error') }}
    </div>
@endif

<form method="POST" action="{{ route('transaksi.penjualan-wilayah.store') }}">
@csrf

<div class="bg-white rounded-xl shadow-sm p-6 mb-4">
    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Tipe Transaksi</h3>
    <div class="flex gap-6">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" name="tipe" value="penjualan" {{ old('tipe', 'transfer') === 'penjualan' ? 'checked' : '' }}
                onchange="toggleTipe(this.value)"
                class="accent-orange-500 w-4 h-4">
            <div>
                <span class="text-sm font-medium text-gray-700">Penjualan</span>
                <p class="text-xs text-gray-400">Jual ke agen/mitra luar, ada harga & piutang</p>
            </div>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" name="tipe" value="transfer" {{ old('tipe', 'transfer') === 'transfer' ? 'checked' : '' }}
                onchange="toggleTipe(this.value)"
                class="accent-orange-500 w-4 h-4">
            <div>
                <span class="text-sm font-medium text-gray-700">Transfer</span>
                <p class="text-xs text-gray-400">Pindah stok antar cabang sendiri, tanpa harga</p>
            </div>
        </label>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 mb-4">
    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Informasi Transaksi</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Tanggal</label>
            <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Dari Wilayah</label>
            <select name="wilayah_asal_id" id="wilayah_asal_id" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                <option value="">-- Pilih Wilayah Asal --</option>
                @foreach($wilayah as $w)
                <option value="{{ $w->id }}" {{ old('wilayah_asal_id') == $w->id ? 'selected' : '' }}>
                    {{ $w->nama }} ({{ ucfirst($w->tipe) }})
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Ke Wilayah</label>
            <select name="wilayah_tujuan_id" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                <option value="">-- Pilih Wilayah Tujuan --</option>
                @foreach($wilayah as $w)
                <option value="{{ $w->id }}" {{ old('wilayah_tujuan_id') == $w->id ? 'selected' : '' }}>
                    {{ $w->nama }} ({{ ucfirst($w->tipe) }})
                </option>
                @endforeach
            </select>
        </div>
        <div id="section-status-bayar">
            <label class="block text-sm text-gray-600 mb-1">Status Bayar</label>
            <select name="status_bayar"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                <option value="belum_lunas" {{ old('status_bayar', 'belum_lunas') === 'belum_lunas' ? 'selected' : '' }}>Belum Lunas</option>
                <option value="sebagian" {{ old('status_bayar') === 'sebagian' ? 'selected' : '' }}>Sebagian</option>
                <option value="lunas" {{ old('status_bayar') === 'lunas' ? 'selected' : '' }}>Lunas</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm text-gray-600 mb-1">Keterangan (opsional)</label>
            <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 mb-4">
    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Detail Produk</h3>
    <p id="hint-harga" class="text-xs text-gray-400 mb-4">Harga menggunakan <strong>Harga Agen</strong>. Stok tersedia dimuat otomatis setelah wilayah asal dipilih.</p>
    <p id="hint-transfer" class="text-xs text-gray-400 mb-4" style="display:none">
        Isi jumlah produk yang ditransfer. Stok tersedia dimuat otomatis setelah wilayah asal dipilih.
    </p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach($produk as $p)
        <div class="flex items-center gap-3 p-3 border border-gray-100 rounded-lg produk-row" data-produk-id="{{ $p->id }}">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-700 truncate">{{ $p->nama }}</p>
                <p class="text-xs text-gray-400 harga-info">Harga Agen: Rp {{ number_format($p->harga_agen) }}</p>
                <p class="stok-info text-xs mt-0.5" style="display:none"></p>
            </div>
            <div class="w-24 flex-shrink-0">
                <input type="number" name="jumlah[{{ $p->id }}]" value="{{ old('jumlah.'.$p->id, 0) }}" min="0"
                    class="jumlah-input w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-center focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>
            <span class="text-xs text-gray-400 w-6 flex-shrink-0">pcs</span>
        </div>
        @endforeach
    </div>
</div>

<div class="flex justify-end gap-3">
    <a href="{{ route('transaksi.penjualan-wilayah.index') }}"
        class="px-5 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</a>
    <button type="submit" id="btn-submit"
        class="px-5 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium">
        Simpan Penjualan
    </button>
</div>

</form>

<script>
    var stokApiUrl = "{{ url('api/stok-tersedia') }}";

    function loadStokTersedia() {
        var wilayahId = document.getElementById('wilayah_asal_id').value;
        if (!wilayahId) {
            clearStokInfo();
            return;
        }

        document.querySelectorAll('.stok-info').forEach(function(el) {
            el.style.display = '';
            el.innerHTML = '<span class="text-gray-400">Memuat stok...</span>';
        });

        fetch(stokApiUrl + '?wilayah_id=' + wilayahId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                data.forEach(function(item) {
                    var row = document.querySelector('[data-produk-id="' + item.produk_id + '"]');
                    if (!row) return;
                    var stokInfo = row.querySelector('.stok-info');
                    var input = row.querySelector('.jumlah-input');
                    stokInfo.style.display = '';
                    if (item.stok_tersedia <= 0) {
                        stokInfo.innerHTML = '<span class="text-red-500 font-medium">Stok Habis</span>';
                        input.disabled = true;
                        input.value = 0;
                        input.removeAttribute('max');
                        row.style.opacity = '0.55';
                    } else {
                        stokInfo.innerHTML = '<span class="text-green-600">Tersedia: ' + item.stok_tersedia + ' pcs</span>';
                        input.disabled = false;
                        input.setAttribute('max', item.stok_tersedia);
                        row.style.opacity = '1';
                    }
                });
            })
            .catch(function() {
                document.querySelectorAll('.stok-info').forEach(function(el) {
                    el.style.display = 'none';
                });
            });
    }

    function clearStokInfo() {
        document.querySelectorAll('.stok-info').forEach(function(el) {
            el.style.display = 'none';
            el.innerHTML = '';
        });
        document.querySelectorAll('.jumlah-input').forEach(function(input) {
            input.disabled = false;
            input.removeAttribute('max');
        });
        document.querySelectorAll('.produk-row').forEach(function(row) {
            row.style.opacity = '1';
        });
    }

    function toggleTipe(tipe) {
        var isTransfer = tipe === 'transfer';
        document.getElementById('section-status-bayar').style.display = isTransfer ? 'none' : '';
        document.getElementById('hint-harga').style.display = isTransfer ? 'none' : '';
        document.getElementById('hint-transfer').style.display = isTransfer ? '' : 'none';
        document.querySelectorAll('.harga-info').forEach(function(el) {
            el.style.display = isTransfer ? 'none' : '';
        });
        document.getElementById('btn-submit').textContent = isTransfer ? 'Simpan Transfer' : 'Simpan Penjualan';

        loadStokTersedia();
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Init on page load
        var checked = document.querySelector('[name="tipe"]:checked');
        if (checked) toggleTipe(checked.value);

        // Reload stok when wilayah asal changes
        document.getElementById('wilayah_asal_id').addEventListener('change', function() {
            loadStokTersedia();
        });

        document.querySelector('form').addEventListener('submit', function (e) {
            var tipe = document.querySelector('[name="tipe"]:checked').value;
            var errors = [];
            var asal = document.querySelector('[name="wilayah_asal_id"]').value;
            var tujuan = document.querySelector('[name="wilayah_tujuan_id"]').value;
            if (!asal) errors.push('Wilayah asal wajib dipilih.');
            if (!tujuan) errors.push('Wilayah tujuan wajib dipilih.');
            if (asal && tujuan && asal === tujuan)
                errors.push('Wilayah tujuan tidak boleh sama dengan wilayah asal.');
            if (!document.querySelector('[name="tanggal"]').value)
                errors.push('Tanggal wajib diisi.');
            var hasAny = Array.from(document.querySelectorAll('.jumlah-input'))
                .some(function (inp) { return !inp.disabled && parseInt(inp.value) > 0; });
            if (!hasAny)
                errors.push('Minimal satu produk harus memiliki jumlah lebih dari 0.');

            // Validasi max stok
            document.querySelectorAll('.produk-row').forEach(function(row) {
                var input = row.querySelector('.jumlah-input');
                var maxVal = input.getAttribute('max');
                var val = parseInt(input.value) || 0;
                if (val > 0 && maxVal !== null && val > parseInt(maxVal)) {
                    var nama = row.querySelector('p.text-sm.font-medium').textContent.trim();
                    errors.push(nama + ': jumlah ' + val + ' melebihi stok tersedia (' + maxVal + ')');
                }
            });

            if (errors.length === 0) return;
            e.preventDefault();
            var html = '<ul style="text-align:left;padding-left:20px;margin:0">' +
                errors.map(function (err) { return '<li>' + err + '</li>'; }).join('') + '</ul>';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Periksa Form', html: html, confirmButtonColor: '#f97316', confirmButtonText: 'OK' });
            } else {
                alert(errors.join('\n'));
            }
        });
    });
</script>
@endsection
