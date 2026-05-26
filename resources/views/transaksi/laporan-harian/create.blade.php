@extends('layouts.app')
@section('title', 'Input Laporan Harian')

@section('content')

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('transaksi.laporan-harian.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">←
            Kembali</a>
        <h2 class="text-2xl font-bold text-gray-700">Input Laporan Harian</h2>
    </div>

    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-100 text-red-700 rounded-lg text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('transaksi.laporan-harian.store') }}" id="form-laporan">
        @csrf

        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Informasi Laporan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required
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
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Sisa Stok Malam</h3>
            <p class="text-xs text-gray-400 mb-4">Isi jumlah sisa barang yang tidak terjual.</p>
            <div id="produk-container">
                <p class="text-sm text-gray-400 italic">Pilih outlet dan tanggal dulu untuk memuat data distribusi.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Pengeluaran</h3>
                <button type="button" onclick="tambahPengeluaran()"
                    class="text-xs px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-600">
                    + Tambah
                </button>
            </div>
            <div id="pengeluaran-container">
                <div class="pengeluaran-row grid grid-cols-1 md:grid-cols-2 gap-3 mb-3" id="pen-0">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Keterangan</label>
                        <input type="text" name="pengeluaran_ket[]" placeholder="Misal: Bensin, Sewa lapak..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Jumlah (Rp)</label>
                        <input type="number" name="pengeluaran_jml[]" value="0" min="0" onchange="hitungSetor()"
                            class="pengeluaran-jml w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Rekap Keuangan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-red-50 rounded-lg">
                    <p class="text-xs text-gray-400 uppercase">Total Omset</p>
                    <p class="text-xl font-bold text-red-600 mt-1" id="display-omset">Rp 0</p>
                </div>
                <div class="p-4 bg-yellow-50 rounded-lg">
                    <p class="text-xs text-gray-400 uppercase">Total Komisi</p>
                    <p class="text-xl font-bold text-yellow-500 mt-1" id="display-komisi">Rp 0</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg">
                    <p class="text-xs text-gray-400 uppercase">Total Pengeluaran</p>
                    <p class="text-xl font-bold text-red-400 mt-1" id="display-pengeluaran">Rp 0</p>
                </div>
            </div>
            <div class="mt-4 p-4 bg-green-50 rounded-lg">
                <p class="text-xs text-gray-400 uppercase">Total Setor (Auto)</p>
                <p class="text-2xl font-bold text-green-600 mt-1" id="display-setor">Rp 0</p>
                <input type="hidden" name="total_setor" id="input-setor" value="0">
                <input type="hidden" name="total_pengeluaran" id="input-pengeluaran" value="0">
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('transaksi.laporan-harian.index') }}"
                class="px-5 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg font-medium">
                Simpan Laporan
            </button>
        </div>

    </form>

    <script>
        // Data produk dari distribusi (diisi saat fetch)
        let distribusiData = [];

        function loadDistribusi() {
            const outletId = document.getElementById('outlet_id').value;
            const tanggal = document.getElementById('tanggal').value;
            const container = document.getElementById('produk-container');

            if (!outletId || !tanggal) {
                container.innerHTML = '<p class="text-sm text-gray-400 italic">Pilih outlet dan tanggal dulu untuk memuat data distribusi.</p>';
                distribusiData = [];
                hitungSetor();
                return;
            }

            container.innerHTML = '<p class="text-sm text-gray-400">Memuat data...</p>';

            fetch(`/dimsys/public/api/distribusi?outlet_id=${outletId}&tanggal=${tanggal}`)
                .then(res => res.json())
                .then(data => {
                    distribusiData = data;

                    if (!data.length) {
                        container.innerHTML = '<p class="text-sm text-yellow-600">⚠️ Tidak ada data distribusi untuk outlet dan tanggal ini.</p>';
                        hitungSetor();
                        return;
                    }

                    let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">';
                    data.forEach(item => {
                        html += `
                    <div class="flex items-center gap-3 p-3 border border-gray-100 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700">${item.produk_nama}</p>
                            <p class="text-xs text-gray-400">OUT: <span class="font-semibold text-amber-600">${item.jumlah_out} pcs</span></p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-400 mb-1">Sisa</p>
                            <input type="number"
                                name="sisa_${item.produk_id}"
                                id="sisa_${item.produk_id}"
                                value="0"
                                min="0"
                                max="${item.jumlah_out}"
                                data-out="${item.jumlah_out}"
                                data-harga="${item.harga_jual}"
                                data-komisi="${item.komisi}"
                                onchange="hitungSetor()"
                                oninput="hitungSetor()"
                                class="sisa-input w-20 border border-gray-200 rounded-lg px-2 py-1 text-sm text-center focus:outline-none focus:ring-2 focus:ring-red-300">
                        </div>
                    </div>`;
                    });
                    html += '</div>';
                    container.innerHTML = html;
                    hitungSetor();
                })
                .catch(() => {
                    container.innerHTML = '<p class="text-sm text-red-500">Gagal memuat data distribusi.</p>';
                });
        }

        function hitungSetor() {
            // Hitung dari input sisa
            let totalOmset = 0;
            let totalKomisi = 0;

            document.querySelectorAll('.sisa-input').forEach(input => {
                const out = parseFloat(input.dataset.out) || 0;
                const harga = parseFloat(input.dataset.harga) || 0;
                const komisi = parseFloat(input.dataset.komisi) || 0;
                const sisa = parseFloat(input.value) || 0;
                const terjual = Math.max(0, out - sisa);
                totalOmset += terjual * harga;
                totalKomisi += terjual * komisi;
            });

            // Hitung total pengeluaran
            let totalPengeluaran = 0;
            document.querySelectorAll('.pengeluaran-jml').forEach(input => {
                totalPengeluaran += parseFloat(input.value) || 0;
            });

            const totalSetor = Math.max(0, totalOmset - totalKomisi - totalPengeluaran);

            // Update display
            document.getElementById('display-omset').textContent = 'Rp ' + totalOmset.toLocaleString('id-ID');
            document.getElementById('display-komisi').textContent = 'Rp ' + totalKomisi.toLocaleString('id-ID');
            document.getElementById('display-pengeluaran').textContent = 'Rp ' + totalPengeluaran.toLocaleString('id-ID');
            document.getElementById('display-setor').textContent = 'Rp ' + totalSetor.toLocaleString('id-ID');

            // Update hidden input
            document.getElementById('input-setor').value = totalSetor;
            document.getElementById('input-pengeluaran').value = totalPengeluaran;
        }

        let pengeluaranCount = 1;
        function tambahPengeluaran() {
            if (pengeluaranCount >= 10) {
                alert('Maksimal 10 pengeluaran per laporan.');
                return;
            }
            const container = document.getElementById('pengeluaran-container');
            const div = document.createElement('div');
            div.className = 'pengeluaran-row grid grid-cols-1 md:grid-cols-2 gap-3 mb-3';
            div.id = `pen-${pengeluaranCount}`;
            div.innerHTML = `
            <div>
                <label class="block text-sm text-gray-600 mb-1">Keterangan</label>
                <input type="text" name="pengeluaran_ket[]" placeholder="Misal: Air minum, Token listrik..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
            </div>
            <div class="flex gap-2 items-end">
                <div class="flex-1">
                    <label class="block text-sm text-gray-600 mb-1">Jumlah (Rp)</label>
                    <input type="number" name="pengeluaran_jml[]" value="0" min="0"
                        onchange="hitungSetor()" oninput="hitungSetor()"
                        class="pengeluaran-jml w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
                <button type="button" onclick="hapusPengeluaran(${pengeluaranCount})"
                    class="px-3 py-2 text-sm bg-red-50 hover:bg-red-100 text-red-500 rounded-lg mb-0">✕</button>
            </div>`;
            container.appendChild(div);
            pengeluaranCount++;
        }

        function hapusPengeluaran(id) {
            document.getElementById(`pen-${id}`)?.remove();
            pengeluaranCount--;
            hitungSetor();
        }

        document.getElementById('outlet_id').addEventListener('change', loadDistribusi);
        document.getElementById('tanggal').addEventListener('change', loadDistribusi);

        // Client-side validation
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('form-laporan').addEventListener('submit', function (e) {
                var errors = [];
                if (!document.getElementById('outlet_id').value)
                    errors.push('Outlet wajib dipilih.');
                if (!document.getElementById('tanggal').value)
                    errors.push('Tanggal wajib diisi.');
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