@extends('layouts.app')
@section('title', 'Tambah Penjualan Wilayah')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('transaksi.penjualan-wilayah.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali</a>
    <h2 class="text-2xl font-bold text-gray-700">Tambah Penjualan Wilayah</h2>
</div>

@if($errors->any())
    <div class="mb-4 px-4 py-3 bg-red-100 text-red-700 rounded-lg text-sm">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST" action="{{ route('transaksi.penjualan-wilayah.store') }}">
@csrf

<div class="bg-white rounded-xl shadow-sm p-6 mb-4">
    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Informasi Pengiriman</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Tanggal</label>
            <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Dari Wilayah</label>
            <select name="wilayah_asal_id" required
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
        <div>
            <label class="block text-sm text-gray-600 mb-1">Status Bayar</label>
            <select name="status_bayar" required
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                <option value="belum_lunas">Belum Lunas</option>
                <option value="sebagian">Sebagian</option>
                <option value="lunas">Lunas</option>
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
    <p class="text-xs text-gray-400 mb-4">Harga menggunakan <strong>Harga Agen</strong>. Isi jumlah produk yang dikirim.</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @foreach($produk as $i => $p)
        <div class="flex items-center gap-3 p-3 border border-gray-100 rounded-lg">
            <input type="hidden" name="produk_id[]" value="{{ $p->id }}">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-700">{{ $p->nama }}</p>
                <p class="text-xs text-gray-400">Harga Agen: Rp {{ number_format($p->harga_agen) }}</p>
            </div>
            <div class="w-28">
                <input type="number" name="jumlah[]" value="{{ old('jumlah.'.$i, 0) }}" min="0"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-center focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>
            <span class="text-xs text-gray-400 w-6">pcs</span>
        </div>
        @endforeach
    </div>
</div>

<div class="flex justify-end gap-3">
    <a href="{{ route('transaksi.penjualan-wilayah.index') }}"
        class="px-5 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</a>
    <button type="submit"
        class="px-5 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium">
        Simpan Penjualan
    </button>
</div>

</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelector('form').addEventListener('submit', function (e) {
            var errors = [];
            var asal = document.querySelector('[name="wilayah_asal_id"]').value;
            var tujuan = document.querySelector('[name="wilayah_tujuan_id"]').value;
            if (!asal) errors.push('Wilayah asal wajib dipilih.');
            if (!tujuan) errors.push('Wilayah tujuan wajib dipilih.');
            if (asal && tujuan && asal === tujuan)
                errors.push('Wilayah tujuan tidak boleh sama dengan wilayah asal.');
            if (!document.querySelector('[name="tanggal"]').value)
                errors.push('Tanggal wajib diisi.');
            var hasAny = Array.from(document.querySelectorAll('[name="jumlah[]"]'))
                .some(function (inp) { return parseInt(inp.value) > 0; });
            if (!hasAny)
                errors.push('Minimal satu produk harus memiliki jumlah lebih dari 0.');
            if (errors.length === 0) return;
            e.preventDefault();
            var html = '<ul style="text-align:left;padding-left:20px;margin:0">' +
                errors.map(function (err) { return '<li>' + err + '</li>'; }).join('') + '</ul>';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Periksa Form', html: html, confirmButtonColor: '#f97316', confirmButtonText: 'OK' });
            } else {
                alert('Periksa Form:\n' + errors.join('\n'));
            }
        });
    });
</script>
@endsection