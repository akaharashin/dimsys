@extends('layouts.app')
@section('title', 'Tambah Stok Masuk')

@section('content')

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('stok.masuk.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali</a>
        <h2 class="text-2xl font-bold text-gray-700">Tambah Stok Masuk</h2>
    </div>

    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-100 text-red-700 rounded-lg text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('stok.masuk.store') }}">
        @csrf

        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Informasi Stok</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Jenis</label>
                    <select name="jenis" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        <option value="masuk" {{ old('jenis') == 'masuk' ? 'selected' : '' }}>
                            Stok Masuk (dari Supplier)
                        </option>
                        <option value="awal" {{ old('jenis') == 'awal' ? 'selected' : '' }}>
                            Stok Awal (input awal periode)
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Wilayah Tujuan</label>
                    <select name="wilayah_id" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        <option value="">-- Pilih Wilayah --</option>
                        @foreach($wilayah as $w)
                            <option value="{{ $w->id }}" {{ old('wilayah_id') == $w->id ? 'selected' : '' }}>
                                {{ $w->nama }} ({{ ucfirst($w->tipe) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Supplier</label>
                    <select name="supplier_id" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($supplier as $s)
                            <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-600 mb-1">Keterangan (opsional)</label>
                    <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Detail Produk</h3>
            <p class="text-xs text-gray-400 mb-4">HPP akan otomatis tersimpan sesuai harga saat ini. Kosongkan atau isi 0
                untuk produk yang tidak ada.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($produk as $i => $p)
                    <div class="flex items-center gap-3 p-3 border border-gray-100 rounded-lg">
                        <input type="hidden" name="produk_id[]" value="{{ $p->id }}">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700">{{ $p->nama }}</p>
                            <p class="text-xs text-gray-400">HPP: Rp {{ number_format($p->hpp) }}</p>
                        </div>
                        <div class="w-28">
                            <input type="number" name="jumlah[]" value="{{ old('jumlah.' . $i, 0) }}" min="0"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-center focus:outline-none focus:ring-2 focus:ring-red-300">
                        </div>
                        <span class="text-xs text-gray-400 w-6">pcs</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('stok.masuk.index') }}"
                class="px-5 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg font-medium">
                Simpan Stok
            </button>
        </div>

    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelector('form').addEventListener('submit', function (e) {
                var errors = [];
                if (!document.querySelector('[name="wilayah_id"]').value)
                    errors.push('Wilayah tujuan wajib dipilih.');
                if (!document.querySelector('[name="supplier_id"]').value)
                    errors.push('Supplier wajib dipilih.');
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
                    Swal.fire({ icon: 'error', title: 'Periksa Form', html: html, confirmButtonColor: '#A51616', confirmButtonText: 'OK' });
                } else {
                    alert('Periksa Form:\n' + errors.join('\n'));
                }
            });
        });
    </script>
@endsection