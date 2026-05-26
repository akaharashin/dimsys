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
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Wilayah</label>
                    <select name="wilayah_id" id="wilayah_id" required onchange="loadStokSistem()"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
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
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300"
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

        {{-- Foto Bukti (Opsional) --}}
        <div id="foto-bukti-section" class="hidden bg-white rounded-xl shadow-sm mb-4">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-600">
                    Foto Bukti
                    <span class="font-normal text-gray-400">(Opsional)</span>
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Foto kondisi stok · maks. 5 foto per tipe · JPG PNG WebP · maks. 10MB per foto</p>
            </div>
            <div class="p-6">
                {{-- Tab buttons --}}
                <div class="flex gap-0 mb-4 border-b border-gray-100">
                    <button type="button" onclick="switchFotoTab('foto_real')" id="ftab-foto_real"
                        class="px-4 py-2 text-sm font-medium border-b-2 border-red-600 text-red-600 -mb-px">
                        Foto Real (<span id="fcount-foto_real">0</span>/5)
                    </button>
                    <button type="button" onclick="switchFotoTab('berita_acara')" id="ftab-berita_acara"
                        class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-400 -mb-px">
                        Berita Acara (<span id="fcount-berita_acara">0</span>/5)
                    </button>
                </div>

                {{-- Panel: Foto Real --}}
                <div id="fpanel-foto_real">
                    <label class="block text-xs text-gray-500 mb-2">Pilih foto bukti kondisi stok</label>
                    <input type="file" id="finput-foto_real" accept="image/*" multiple
                        class="text-sm text-gray-600
                               file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                               file:text-sm file:font-medium file:bg-red-50 file:text-red-700
                               hover:file:bg-red-100">
                    <div id="fpreview-foto_real" class="grid grid-cols-2 gap-3 mt-3" style="display:none"></div>
                </div>

                {{-- Panel: Berita Acara --}}
                <div id="fpanel-berita_acara" style="display:none">
                    <label class="block text-xs text-gray-500 mb-2">Pilih foto berita acara</label>
                    <input type="file" id="finput-berita_acara" accept="image/*" multiple
                        class="text-sm text-gray-600
                               file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                               file:text-sm file:font-medium file:bg-red-50 file:text-red-700
                               hover:file:bg-red-100">
                    <div id="fpreview-berita_acara" class="grid grid-cols-2 gap-3 mt-3" style="display:none"></div>
                </div>
            </div>
        </div>

        <div id="footer-sto" class="hidden flex justify-end gap-3">
            <a href="{{ route('stok.opname.index') }}"
                class="px-5 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</a>
            <button type="submit" id="btn-simpan"
                class="px-5 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg font-medium">
                Simpan STO
            </button>
        </div>

    </form>

    <script>
        var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

        // ── Stok table ──────────────────────────────────────────────────────────
        function loadStokSistem() {
            const wilayahId = document.getElementById('wilayah_id').value;
            if (!wilayahId) return;

            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('tabel-sto').classList.add('hidden');
            document.getElementById('foto-bukti-section').classList.add('hidden');
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
                                        class="w-24 border border-gray-200 rounded-lg px-2 py-1 text-sm text-right focus:outline-none focus:ring-2 focus:ring-red-300">
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
                    document.getElementById('foto-bukti-section').classList.remove('hidden');
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

        // ── Foto queues ──────────────────────────────────────────────────────────
        var fotoQueues   = { foto_real: [], berita_acara: [] };
        var fotoTabAktif = 'foto_real';
        var maxFotoPerTipe = 5;

        function switchFotoTab(tab) {
            fotoTabAktif = tab;
            ['foto_real', 'berita_acara'].forEach(function(t) {
                document.getElementById('fpanel-' + t).style.display = t === tab ? '' : 'none';
                var btn = document.getElementById('ftab-' + t);
                if (t === tab) {
                    btn.classList.add('border-red-600', 'text-red-600');
                    btn.classList.remove('border-transparent', 'text-gray-400');
                } else {
                    btn.classList.remove('border-red-600', 'text-red-600');
                    btn.classList.add('border-transparent', 'text-gray-400');
                }
            });
        }

        function addFotoFiles(tipe, files) {
            var current   = fotoQueues[tipe].length;
            var remaining = maxFotoPerTipe - current;
            var added     = 0;

            Array.from(files).forEach(function(f) {
                if (added < remaining) {
                    fotoQueues[tipe].push(f);
                    added++;
                }
            });

            if (Array.from(files).length > remaining) {
                showAlert('warning', 'Batas Foto', 'Hanya ' + remaining + ' foto lagi yang bisa ditambahkan (maks. 5 per tipe).');
            }

            renderFotoPreviews(tipe);
            updateFotoCount(tipe);
        }

        function removeFromFotoQueue(tipe, idx) {
            fotoQueues[tipe].splice(idx, 1);
            renderFotoPreviews(tipe);
            updateFotoCount(tipe);
        }

        function updateFotoCount(tipe) {
            var el = document.getElementById('fcount-' + tipe);
            if (el) el.textContent = fotoQueues[tipe].length;
        }

        function renderFotoPreviews(tipe) {
            var preview = document.getElementById('fpreview-' + tipe);
            if (!preview) return;
            preview.innerHTML = '';

            if (fotoQueues[tipe].length === 0) {
                preview.style.display = 'none';
                return;
            }
            preview.style.display = 'grid';

            fotoQueues[tipe].forEach(function(file, idx) {
                var card = document.createElement('div');
                card.style.cssText = 'position:relative;border-radius:8px;border:1px solid #f3f4f6;';

                var img = document.createElement('img');
                img.alt = file.name;
                img.style.cssText = 'width:100%;min-height:140px;object-fit:cover;border-radius:8px 8px 0 0;display:block';
                card.appendChild(img);

                var reader = new FileReader();
                reader.onload = (function(i) { return function(e) { i.src = e.target.result; }; })(img);
                reader.readAsDataURL(file);

                var info = document.createElement('div');
                info.className = 'px-2 py-1.5';

                var nameEl = document.createElement('p');
                nameEl.className = 'text-xs text-gray-600 truncate';
                nameEl.textContent = file.name;

                var sizeEl = document.createElement('p');
                sizeEl.className = 'text-xs text-gray-400 mt-0.5';
                var sizeMB = file.size / 1024 / 1024;
                sizeEl.textContent = (sizeMB >= 1 ? sizeMB.toFixed(1) + ' MB' : Math.ceil(file.size / 1024) + ' KB') + ' (sebelum compress)';

                info.appendChild(nameEl);
                info.appendChild(sizeEl);
                card.appendChild(info);

                var xBtn = document.createElement('button');
                xBtn.type = 'button';
                xBtn.style.cssText = 'position:absolute;top:-8px;right:-8px;width:20px;height:20px;background:#ef4444;color:white;border:none;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:14px;z-index:10;line-height:1;padding:0;';
                xBtn.innerHTML = '&times;';
                xBtn.onclick = (function(t, i) { return function() { removeFromFotoQueue(t, i); }; })(tipe, idx);
                card.appendChild(xBtn);

                preview.appendChild(card);
            });
        }

        // ── Form submit (3 langkah) ──────────────────────────────────────────────
        document.getElementById('form-sto').addEventListener('submit', function(e) {
            e.preventDefault();
            submitSTO(this);
        });

        function submitSTO(form) {
            var btn = document.getElementById('btn-simpan');
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';

            var fd = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: fd,
            })
            .then(function(r) {
                return r.json().then(function(data) { return { ok: r.ok, status: r.status, data: data }; });
            })
            .then(function(res) {
                if (!res.ok) {
                    btn.disabled = false;
                    btn.textContent = 'Simpan STO';
                    if (res.status === 422 && res.data.errors) {
                        var msgs = Object.values(res.data.errors).flat().join('\n');
                        showAlert('error', 'Validasi Gagal', msgs);
                    } else {
                        showAlert('error', 'Gagal Menyimpan', res.data.error || res.data.message || 'Gagal menyimpan STO.');
                    }
                    return;
                }

                var uploadUrl  = res.data.upload_url;
                var redirectUrl = res.data.redirect;

                // Kumpulkan semua file dari kedua koleksi
                var allFiles = [];
                ['foto_real', 'berita_acara'].forEach(function(tipe) {
                    fotoQueues[tipe].forEach(function(file) {
                        allFiles.push({ file: file, tipe: tipe });
                    });
                });

                if (allFiles.length === 0) {
                    window.location.href = redirectUrl;
                    return;
                }

                var i = 0;
                btn.textContent = 'Mengupload foto 0/' + allFiles.length + '...';

                function uploadNext() {
                    if (i >= allFiles.length) {
                        window.location.href = redirectUrl;
                        return;
                    }

                    var item = allFiles[i];
                    var fd2  = new FormData();
                    fd2.append('foto', item.file);
                    fd2.append('tipe', item.tipe);

                    btn.textContent = 'Mengupload foto ' + (i + 1) + '/' + allFiles.length + '...';

                    fetch(uploadUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                        body: fd2,
                    })
                    .then(function(r) { return r.json(); })
                    .then(function() { i++; uploadNext(); })
                    .catch(function()  { i++; uploadNext(); });
                }

                uploadNext();
            })
            .catch(function() {
                btn.disabled = false;
                btn.textContent = 'Simpan STO';
                showAlert('error', 'Error Jaringan', 'Terjadi kesalahan. Coba lagi.');
            });
        }

        function showAlert(icon, title, text) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: icon, title: title, text: text, confirmButtonColor: '#A51616', confirmButtonText: 'OK' });
            } else {
                alert(title + ': ' + text);
            }
        }

        // ── DOMContentLoaded ─────────────────────────────────────────────────────
        window.addEventListener('DOMContentLoaded', function() {
            // File input listeners per tipe
            ['foto_real', 'berita_acara'].forEach(function(tipe) {
                var input = document.getElementById('finput-' + tipe);
                if (!input) return;
                input.addEventListener('change', function() {
                    addFotoFiles(tipe, this.files);
                    this.value = '';
                });
            });

            // Auto load jika koordinator sudah terpilih wilayah
            var sel = document.getElementById('wilayah_id');
            if (sel && sel.value) loadStokSistem();
        });
    </script>

@endsection
