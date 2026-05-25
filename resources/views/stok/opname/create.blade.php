@extends('layouts.app')
@section('title', 'Tambah Stok Opname')

@section('content')

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('stok.opname.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali</a>
        <h2 class="text-2xl font-bold text-gray-700">Tambah Stok Opname</h2>
    </div>

    <form method="POST" action="{{ route('stok.opname.store') }}" id="form-sto">
        @csrf

        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Informasi STO</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Wilayah</label>
                    <select name="wilayah_id" id="wilayah_id" required onchange="loadStokSistem()"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                        <option value="">-- Pilih Wilayah --</option>
                        @foreach($wilayahList as $w)
                            <option value="{{ $w->id }}" {{ auth()->user()->hasRole('koordinator') ? 'selected' : '' }}>
                                {{ $w->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Keterangan (opsional)</label>
                    <input type="text" name="keterangan"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                        placeholder="Keterangan STO...">
                </div>
            </div>
        </div>

        {{-- Loading --}}
        <div id="loading" class="hidden bg-white rounded-xl shadow-sm p-8 text-center mb-4">
            <p class="text-gray-400 text-sm">Memuat data stok sistem...</p>
        </div>

        {{-- Tabel STO --}}
        <div id="tabel-sto" class="hidden bg-white rounded-xl shadow-sm mb-4">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-600">Input Stok Fisik</h3>
                <p class="text-xs text-gray-400">Isi jumlah fisik sesuai hasil hitung di freezer</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-center w-10">No</th>
                            <th class="px-4 py-3 text-left">Produk</th>
                            <th class="px-4 py-3 text-right">Stok Sistem</th>
                            <th class="px-4 py-3 text-right">Stok Fisik</th>
                            <th class="px-4 py-3 text-right">Selisih</th>
                            <th class="px-4 py-3 text-right">HPP</th>
                            <th class="px-4 py-3 text-right">Nilai Selisih</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-sto" class="divide-y divide-gray-100">
                    </tbody>
                </table>
            </div>
        </div>

        <div id="footer-sto" class="hidden flex justify-end gap-3">
            <a href="{{ route('stok.opname.index') }}"
                class="px-5 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium">
                Simpan STO
            </button>
        </div>

    </form>

    <script>
        function loadStokSistem() {
            const wilayahId = document.getElementById('wilayah_id').value;
            if (!wilayahId) return;

            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('tabel-sto').classList.add('hidden');
            document.getElementById('footer-sto').classList.add('hidden');

            fetch(`{{ route('stok.opname.stok-sistem') }}?wilayah_id=${wilayahId}`)
                .then(r => r.json())
                .then(data => {
                    const tbody = document.getElementById('tbody-sto');
                    tbody.innerHTML = '';

                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Tidak ada data stok untuk wilayah ini.</td></tr>';
                    } else {
                        data.forEach((item, i) => {
                            const row = `
                            <tr class="hover:bg-gray-50" id="row-${i}">
                                <td class="px-4 py-3 text-center text-gray-400 text-xs">${i + 1}</td>
                                <td class="px-4 py-3 font-medium text-gray-700">
                                    ${item.nama}
                                    <input type="hidden" name="produk_id[]" value="${item.produk_id}">
                                    <input type="hidden" name="stok_sistem[]" value="${item.stok_sistem}">
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600">${item.stok_sistem.toLocaleString('id')}</td>
                                <td class="px-4 py-3 text-right">
                                    <input type="number" name="stok_fisik[]"
                                        value="${item.stok_sistem}"
                                        min="0"
                                        oninput="hitungSelisih(${i}, ${item.stok_sistem}, ${item.hpp})"
                                        class="w-24 border border-gray-200 rounded-lg px-2 py-1 text-sm text-right focus:outline-none focus:ring-2 focus:ring-orange-300">
                                </td>
                                <td class="px-4 py-3 text-right font-medium" id="selisih-${i}">0</td>
                                <td class="px-4 py-3 text-right text-gray-500">Rp ${item.hpp.toLocaleString('id')}</td>
                                <td class="px-4 py-3 text-right font-medium" id="nilai-selisih-${i}">Rp 0</td>
                            </tr>
                        `;
                            tbody.insertAdjacentHTML('beforeend', row);
                        });
                    }

                    document.getElementById('loading').classList.add('hidden');
                    document.getElementById('tabel-sto').classList.remove('hidden');
                    document.getElementById('footer-sto').classList.remove('hidden');
                })
                .catch(() => {
                    document.getElementById('loading').classList.add('hidden');
                });
        }

        function hitungSelisih(i, stokSistem, hpp) {
            const inputFisik = document.querySelector(`#row-${i} input[name="stok_fisik[]"]`);
            const stokFisik = parseInt(inputFisik.value) || 0;
            const selisih = stokFisik - stokSistem;
            const nilaiSelisih = selisih * hpp;

            const elSelisih = document.getElementById(`selisih-${i}`);
            const elNilai = document.getElementById(`nilai-selisih-${i}`);

            elSelisih.textContent = (selisih > 0 ? '+' : '') + selisih.toLocaleString('id');
            elSelisih.className = 'px-4 py-3 text-right font-medium ' +
                (selisih < 0 ? 'text-red-500' : selisih > 0 ? 'text-green-600' : 'text-gray-400');

            elNilai.textContent = 'Rp ' + nilaiSelisih.toLocaleString('id');
            elNilai.className = 'px-4 py-3 text-right font-medium ' +
                (nilaiSelisih < 0 ? 'text-red-500' : nilaiSelisih > 0 ? 'text-green-600' : 'text-gray-400');
        }

        // Auto load jika koordinator sudah terpilih wilayah
        window.addEventListener('DOMContentLoaded', () => {
            const sel = document.getElementById('wilayah_id');
            if (sel.value) loadStokSistem();
        });
    </script>

@endsection