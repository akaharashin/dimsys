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

    {{-- Mode toggle: Kas Manual vs Setoran dari Outlet --}}
    <div class="flex gap-0 mb-4 border-b border-gray-200">
        <button type="button" id="mtab-manual" onclick="switchMode('manual')"
            class="px-5 py-2 text-sm font-medium border-b-2 border-red-600 text-red-700 -mb-px">
            <i class="fa-solid fa-pen mr-1"></i> Kas Manual
        </button>
        <button type="button" id="mtab-setoran" onclick="switchMode('setoran')"
            class="px-5 py-2 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-gray-600 -mb-px">
            <i class="fa-solid fa-money-bill-transfer mr-1"></i> Setoran dari Outlet
        </button>
    </div>

    {{-- ─── FORM MANUAL ─────────────────────────────────────────────────────── --}}
    <form id="form-manual" method="POST" action="{{ route('transaksi.kas.store') }}">
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

    {{-- ─── FORM SETORAN DARI OUTLET ─────────────────────────────────────────── --}}
    <form id="form-setoran" method="POST" action="{{ route('transaksi.kas.setoran.store') }}" style="display:none">
        @csrf

        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <div class="mb-4 px-4 py-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                <i class="fa-solid fa-circle-info mr-1"></i>
                Mode ini menarik <strong>total setor</strong> dari Laporan Harian outlet & tanggal yang dipilih,
                lalu mencatat pemasukan ke kas. Anda dapat menambahkan baris pengeluaran operasional agen yang akan mengurangi saldo.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tanggal <span class="text-red-600">*</span></label>
                    <input type="date" name="tanggal" id="s-tanggal" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required
                        onchange="fetchSetoran()"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Outlet <span class="text-red-600">*</span></label>
                    <select name="outlet_id" id="s-outlet" required onchange="fetchSetoran()"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        <option value="">-- Pilih Outlet --</option>
                        @foreach($outlet as $o)
                            <option value="{{ $o->id }}">{{ $o->nama }} — {{ $o->wilayah->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Rekening Tujuan <span class="text-red-600">*</span></label>
                    <select name="rekening_id" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                        <option value="">-- Pilih Rekening --</option>
                        @foreach($rekening as $r)
                            <option value="{{ $r->id }}">{{ $r->nama }} ({{ ucfirst($r->tipe) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 p-4 rounded-lg" style="background:#FFFDE7;border:1px solid #F5F028">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Jumlah Setoran (otomatis dari laporan harian)</p>
                        <p class="text-2xl font-bold" style="color:#A51616">
                            Rp <span id="s-total-setor-label">0</span>
                        </p>
                        <p class="text-xs text-gray-400 mt-1" id="s-setor-info">Pilih outlet & tanggal untuk memuat setoran.</p>
                    </div>
                </div>
                <input type="hidden" name="total_setor" id="s-total-setor" value="0">
            </div>

            {{-- Warning double-setoran --}}
            <div id="s-warning-duplicate" style="display:none"
                class="mt-3 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                <span id="s-warning-duplicate-text">Setoran outlet ini untuk tanggal tersebut sudah dicatat. Batalkan dulu yang lama jika ingin input ulang.</span>
            </div>
        </div>

        {{-- Pengeluaran Operasional Agen --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-600">
                    Pengeluaran Operasional Agen
                    <span class="font-normal text-gray-400 text-xs">(opsional · mengurangi setor)</span>
                </h3>
                <button type="button" onclick="addPengeluaranRow()"
                    class="text-xs px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg font-medium">
                    <i class="fa-solid fa-plus mr-1"></i> Tambah Baris
                </button>
            </div>

            <div id="s-pengeluaran-list" class="space-y-2">
                {{-- baris dinamis ditambahkan via JS --}}
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                <div>
                    <p class="text-xs text-gray-400 uppercase">Total Setor</p>
                    <p class="font-bold text-gray-700">Rp <span id="s-sum-setor">0</span></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase">Total Pengeluaran</p>
                    <p class="font-bold text-red-600">- Rp <span id="s-sum-pengeluaran">0</span></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase">Setor Bersih (Net)</p>
                    <p class="font-bold text-green-600 text-base">Rp <span id="s-net">0</span></p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('transaksi.kas.index') }}"
                class="px-5 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</a>
            <button type="submit" id="s-btn-simpan"
                class="px-5 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg font-medium">
                Simpan Setoran
            </button>
        </div>
    </form>

    <script>
        // ── Mode tabs ───────────────────────────────────────────────────────────
        function switchMode(mode) {
            var formManual  = document.getElementById('form-manual');
            var formSetoran = document.getElementById('form-setoran');
            var tManual  = document.getElementById('mtab-manual');
            var tSetoran = document.getElementById('mtab-setoran');

            if (mode === 'setoran') {
                formManual.style.display  = 'none';
                formSetoran.style.display = '';
                tSetoran.classList.add('border-red-600', 'text-red-700');
                tSetoran.classList.remove('border-transparent', 'text-gray-400');
                tManual.classList.remove('border-red-600', 'text-red-700');
                tManual.classList.add('border-transparent', 'text-gray-400');
            } else {
                formManual.style.display  = '';
                formSetoran.style.display = 'none';
                tManual.classList.add('border-red-600', 'text-red-700');
                tManual.classList.remove('border-transparent', 'text-gray-400');
                tSetoran.classList.remove('border-red-600', 'text-red-700');
                tSetoran.classList.add('border-transparent', 'text-gray-400');
            }
        }

        // ── Fetch setoran dari laporan harian ───────────────────────────────────
        function fetchSetoran() {
            var outletId = document.getElementById('s-outlet').value;
            var tanggal  = document.getElementById('s-tanggal').value;
            var elLabel  = document.getElementById('s-total-setor-label');
            var elInput  = document.getElementById('s-total-setor');
            var elInfo   = document.getElementById('s-setor-info');

            if (!outletId || !tanggal) {
                elLabel.textContent = '0';
                elInput.value = 0;
                elInfo.textContent = 'Pilih outlet & tanggal untuk memuat setoran.';
                hitungNet();
                return;
            }

            elInfo.textContent = 'Memuat data setoran...';
            fetch('{{ route("api.setoran-outlet") }}?outlet_id=' + outletId + '&tanggal=' + tanggal)
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    var t = res.total_setor || 0;
                    elLabel.textContent = t.toLocaleString('id-ID');
                    elInput.value = t;
                    elInfo.textContent = res.ada
                        ? 'Setoran ditarik otomatis dari laporan harian.'
                        : (res.pesan || 'Belum ada laporan harian untuk outlet & tanggal ini.');
                    if (!res.ada) {
                        elInfo.classList.add('text-yellow-600');
                        elInfo.classList.remove('text-gray-400');
                    } else {
                        elInfo.classList.remove('text-yellow-600');
                        elInfo.classList.add('text-gray-400');
                    }

                    // Tangani proteksi double setoran
                    var warnEl  = document.getElementById('s-warning-duplicate');
                    var warnTxt = document.getElementById('s-warning-duplicate-text');
                    var btn     = document.getElementById('s-btn-simpan');
                    if (res.sudah_disetor) {
                        warnEl.style.display = '';
                        if (warnTxt && res.pesan_disetor) warnTxt.textContent = res.pesan_disetor;
                        if (btn) {
                            btn.disabled = true;
                            btn.classList.add('bg-gray-300', 'cursor-not-allowed');
                            btn.classList.remove('bg-red-700', 'hover:bg-red-800');
                        }
                    } else {
                        warnEl.style.display = 'none';
                        if (btn) {
                            btn.disabled = false;
                            btn.classList.remove('bg-gray-300', 'cursor-not-allowed');
                            btn.classList.add('bg-red-700', 'hover:bg-red-800');
                        }
                    }

                    hitungNet();
                })
                .catch(function() {
                    elInfo.textContent = 'Gagal memuat data. Coba lagi.';
                });
        }

        // ── Pengeluaran rows ────────────────────────────────────────────────────
        var pengeluaranIdx = 0;
        function addPengeluaranRow() {
            var i = pengeluaranIdx++;
            var row = document.createElement('div');
            row.className = 'grid grid-cols-12 gap-2 items-center';
            row.id = 'p-row-' + i;
            row.innerHTML =
                '<input type="text" name="pengeluaran[' + i + '][keterangan]" placeholder="Keterangan (mis. Token listrik)" required ' +
                '       class="col-span-6 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">' +
                '<input type="number" min="1" name="pengeluaran[' + i + '][jumlah]" placeholder="Jumlah (Rp)" required oninput="hitungNet()" ' +
                '       class="col-span-5 border border-gray-200 rounded-lg px-3 py-2 text-sm text-right focus:outline-none focus:ring-2 focus:ring-red-300">' +
                '<button type="button" onclick="removePengeluaranRow(' + i + ')" ' +
                '       class="col-span-1 text-red-500 hover:text-red-700"><i class="fa-solid fa-trash"></i></button>';
            document.getElementById('s-pengeluaran-list').appendChild(row);
        }

        function removePengeluaranRow(i) {
            var el = document.getElementById('p-row-' + i);
            if (el) el.remove();
            hitungNet();
        }

        function hitungNet() {
            var setor = parseFloat(document.getElementById('s-total-setor').value) || 0;
            var total = 0;
            document.querySelectorAll('#s-pengeluaran-list input[name$="[jumlah]"]').forEach(function(el) {
                total += parseFloat(el.value) || 0;
            });
            var net = setor - total;
            document.getElementById('s-sum-setor').textContent       = setor.toLocaleString('id-ID');
            document.getElementById('s-sum-pengeluaran').textContent = total.toLocaleString('id-ID');
            document.getElementById('s-net').textContent             = net.toLocaleString('id-ID');
        }

        const kategoriOptions = {
            debit: [
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
            var fm = document.getElementById('form-manual');
            if (fm) fm.addEventListener('submit', function (e) {
                var errors = [];
                if (!fm.querySelector('[name="rekening_id"]').value)
                    errors.push('Rekening wajib dipilih.');
                if (!fm.querySelector('[name="tanggal"]').value)
                    errors.push('Tanggal wajib diisi.');
                var jumlah = parseFloat(fm.querySelector('[name="jumlah"]').value);
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

            var fs = document.getElementById('form-setoran');
            if (fs) fs.addEventListener('submit', function (e) {
                var errors = [];
                if (!fs.querySelector('[name="rekening_id"]').value) errors.push('Rekening tujuan wajib dipilih.');
                if (!fs.querySelector('[name="outlet_id"]').value)   errors.push('Outlet wajib dipilih.');
                if (!fs.querySelector('[name="tanggal"]').value)     errors.push('Tanggal wajib diisi.');
                var setor = parseFloat(document.getElementById('s-total-setor').value) || 0;
                var pengCount = fs.querySelectorAll('#s-pengeluaran-list input[name$="[jumlah]"]').length;
                if (setor <= 0 && pengCount === 0) {
                    errors.push('Tidak ada setoran maupun pengeluaran. Tidak ada yang dicatat.');
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