@extends('layouts.app')
@section('title', 'Catat Transaksi Kas')

@section('content')

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('transaksi.kas.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali</a>
        <h2 class="text-2xl font-bold text-gray-700">Catat Transaksi Kas</h2>
    </div>

    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-100 text-red-700 rounded-lg text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('transaksi.kas.store') }}">
        @csrf

        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Rekening</label>
                    <select name="rekening_id" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        <option value="">-- Pilih Rekening --</option>
                        @foreach($rekening as $r)
                            <option value="{{ $r->id }}" {{ old('rekening_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->nama }} ({{ ucfirst($r->tipe) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tipe</label>
                    <select name="tipe" id="tipe" required onchange="updateKategori()"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        <option value="debit" {{ old('tipe') == 'debit' ? 'selected' : '' }}>Debit (Pemasukan)</option>
                        <option value="kredit" {{ old('tipe') == 'kredit' ? 'selected' : '' }}>Kredit (Pengeluaran)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Kategori</label>
                    <select name="kategori" id="kategori" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Sub Kategori <span
                            class="text-gray-400 text-xs">(opsional)</span></label>
                    <input type="text" name="sub_kategori" value="{{ old('sub_kategori') }}"
                        list="list-sub-kategori"
                        placeholder="Pilih atau ketik sub kategori..."
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    <datalist id="list-sub-kategori">
                        <option value="Agen">
                        <option value="Mitra">
                        <option value="Umum">
                    </datalist>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Jumlah (Rp)</label>
                    <input type="number" name="jumlah" value="{{ old('jumlah') }}" min="1" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Outlet <span
                            class="text-gray-400 text-xs">(opsional)</span></label>
                    <select name="outlet_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        <option value="">-- Tidak terkait outlet --</option>
                        @foreach($outlet as $o)
                            <option value="{{ $o->id }}" {{ old('outlet_id') == $o->id ? 'selected' : '' }}>
                                {{ $o->nama }} — {{ $o->wilayah->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Penerima <span
                            class="text-gray-400 text-xs">(opsional)</span></label>
                    <input type="text" name="penerima" value="{{ old('penerima') }}" placeholder="Nama penerima/staff..."
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-600 mb-1">Keterangan <span
                            class="text-gray-400 text-xs">(opsional)</span></label>
                    <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                        placeholder="Keterangan transaksi..."
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('transaksi.kas.index') }}"
                class="px-5 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</a>
            <button type="submit"
                class="px-5 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg font-medium">
                Simpan Transaksi
            </button>
        </div>

    </form>

    <script>
        const kategoriOptions = {
            debit: [
                'Setoran Outlet',
                'Pembayaran',
                'Transfer Masuk',
                'Lain-lain Masuk',
            ],
            kredit: [
                'Operasional Agen',
                'Sewa Lapak',
                'Bensin',
                'Gaji',
                'Kasbon',
                'BPD',
                'Biaya Admin',
                'Biaya Lain-lain',
                'Selisih Pembayaran',
                'Inventaris',
                'Service Kendaraan',
                'Biaya Listrik',
                'Biaya Internet',
                'Biaya Air/PDAM',
                'Pajak',
                'E-Toll',
            ]
        };

        function updateKategori() {
            const tipe = document.getElementById('tipe').value;
            const select = document.getElementById('kategori');
            const oldVal = '{{ old("kategori") }}';
            select.innerHTML = '';

            kategoriOptions[tipe].forEach(opt => {
                const o = document.createElement('option');
                o.value = opt;
                o.text = opt;
                if (opt === oldVal) o.selected = true;
                select.appendChild(o);
            });
        }

        // Init on load
        updateKategori();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelector('form').addEventListener('submit', function (e) {
                var errors = [];
                if (!document.querySelector('[name="rekening_id"]').value)
                    errors.push('Rekening wajib dipilih.');
                if (!document.querySelector('[name="tanggal"]').value)
                    errors.push('Tanggal wajib diisi.');
                var jumlah = parseFloat(document.querySelector('[name="jumlah"]').value);
                if (!jumlah || jumlah < 1)
                    errors.push('Jumlah transaksi minimal Rp 1.');
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