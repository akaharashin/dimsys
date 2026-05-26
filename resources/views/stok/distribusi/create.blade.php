@extends('layouts.app')
@section('title', 'Tambah Distribusi')

@section('content')

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('stok.distribusi.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali</a>
        <h2 class="text-2xl font-bold text-gray-700">Tambah Distribusi (OUT)</h2>
    </div>

    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-100 text-red-700 rounded-lg text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('stok.distribusi.store') }}">
        @csrf

        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Informasi Distribusi</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Outlet / Gerobak</label>
                    <select name="outlet_id" id="outlet_id" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        <option value="">-- Pilih Outlet --</option>
                        @foreach($outlet as $o)
                            <option value="{{ $o->id }}" {{ old('outlet_id') == $o->id ? 'selected' : '' }}>
                                {{ $o->nama }} — {{ $o->wilayah->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Keterangan (opsional)</label>
                    <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Jumlah OUT per Produk</h3>
            <p class="text-xs text-gray-400 mb-4">Stok tersedia ditampilkan otomatis setelah pilih outlet. Tidak bisa
                melebihi stok tersedia.</p>
            <div id="produk-container">
                <p class="text-sm text-gray-400 italic">Pilih outlet dulu untuk melihat stok tersedia.</p>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('stok.distribusi.index') }}"
                class="px-5 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg font-medium">
                Simpan Distribusi
            </button>
        </div>

    </form>

    <script>
        function loadStok() {
            const outletId = document.getElementById('outlet_id').value;
            const container = document.getElementById('produk-container');

            if (!outletId) {
                container.innerHTML = '<p class="text-sm text-gray-400 italic">Pilih outlet dulu untuk melihat stok tersedia.</p>';
                return;
            }

            container.innerHTML = '<p class="text-sm text-gray-400">Memuat stok...</p>';

            fetch(`/dimsys/public/api/stok-tersedia?outlet_id=${outletId}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.length) {
                        container.innerHTML = '<p class="text-sm text-yellow-600">⚠️ Tidak ada data produk.</p>';
                        return;
                    }

                    let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">';
                    data.forEach(item => {
                        const stokClass = item.stok_tersedia <= 0 ? 'text-red-500' : 'text-green-600';
                        html += `
                    <div class="flex items-center gap-3 p-3 border border-gray-100 rounded-lg">
                        <input type="hidden" name="produk_id[]" value="${item.produk_id}">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700">${item.produk_nama}</p>
                            <p class="text-xs mt-1">Stok tersedia: <span class="font-semibold ${stokClass}">${item.stok_tersedia} pcs</span></p>
                        </div>
                        <div class="w-28">
                            <input type="number" name="jumlah_out[]" value="0"
                                min="0" max="${item.stok_tersedia}"
                                ${item.stok_tersedia <= 0 ? 'disabled' : ''}
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-center focus:outline-none focus:ring-2 focus:ring-red-300 ${item.stok_tersedia <= 0 ? 'bg-gray-100 cursor-not-allowed' : ''}">
                        </div>
                        <span class="text-xs text-gray-400 w-6">pcs</span>
                    </div>`;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                })
                .catch(() => {
                    container.innerHTML = '<p class="text-sm text-red-500">Gagal memuat data stok.</p>';
                });
        }

        document.getElementById('outlet_id').addEventListener('change', loadStok);

        // Client-side validation
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelector('form').addEventListener('submit', function (e) {
                var errors = [];
                if (!document.getElementById('outlet_id').value)
                    errors.push('Outlet wajib dipilih.');
                if (!document.querySelector('[name="tanggal"]').value)
                    errors.push('Tanggal wajib diisi.');
                var jumlahInputs = document.querySelectorAll('[name="jumlah_out[]"]');
                if (jumlahInputs.length === 0) {
                    errors.push('Pilih outlet terlebih dahulu untuk memuat data produk.');
                } else {
                    var hasAny = Array.from(jumlahInputs).some(function (inp) { return parseInt(inp.value) > 0; });
                    if (!hasAny)
                        errors.push('Minimal satu produk harus memiliki jumlah lebih dari 0.');
                }
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