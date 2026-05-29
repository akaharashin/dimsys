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

            {{-- Banner: STO sudah dikoreksi (BLOK) --}}
            <div id="banner-sto-blocked" style="display:none"
                class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                <i class="fa-solid fa-circle-xmark mr-1"></i>
                Sudah ada STO untuk wilayah & tanggal ini yang koreksinya telah diterapkan.
                Batalkan STO tersebut dulu jika ingin opname ulang.
            </div>

            {{-- Banner: ada STO belum dikoreksi (WARNING) --}}
            <div id="banner-sto-warning" style="display:none"
                class="mb-4 px-4 py-3 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-lg text-sm"
                style="background:#FFFDE7;border-color:#F5F028;color:#7c5e00">
                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                Sudah ada STO lain untuk wilayah & tanggal ini (belum dikoreksi).
                Anda tetap bisa membuat STO baru, tapi konfirmasi akan diminta saat menyimpan.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required onchange="loadStokSistem(); cekStoExisting();"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Wilayah</label>
                    <select name="wilayah_id" id="wilayah_id" required onchange="loadStokSistem(); cekStoExisting();"
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
                            <th class="px-4 py-3 text-right">Freezer</th>
                            <th class="px-4 py-3 text-right">Gerobak</th>
                            <th class="px-4 py-3 text-right text-red-700">Stok Sistem (Total)</th>
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

        {{-- Foto Bukti (Wajib) --}}
        <div id="foto-bukti-section" class="hidden bg-white rounded-xl shadow-sm mb-4">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-600">
                    Foto Bukti
                    <span class="text-red-600">*</span>
                    <span class="font-normal text-red-500 text-xs">(Wajib minimal 1 foto)</span>
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Foto: maks. 5 per koleksi · JPG/PNG/WebP · maks. 10MB · Video: maks. 3 · MP4/MOV/AVI/WebM · maks. 100MB</p>
                <p id="foto-warning" class="text-xs text-red-600 mt-1 font-medium">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i>
                    Belum ada bukti. Tombol Simpan akan aktif setelah upload minimal 1 foto atau video.
                </p>
            </div>
            <input type="hidden" name="jumlah_media" id="jumlah_media" value="0">
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
                    <button type="button" onclick="switchFotoTab('video')" id="ftab-video"
                        class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-400 -mb-px">
                        <i class="fa-solid fa-video mr-1"></i>
                        Video (<span id="fcount-video">0</span>/3)
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

                {{-- Panel: Video --}}
                <div id="fpanel-video" style="display:none">
                    <label class="block text-xs text-gray-500 mb-2">Video bukti: MP4/MOV/AVI/WebM, maksimal 100 MB per video</label>
                    <input type="file" id="finput-video" accept="video/*" multiple
                        class="text-sm text-gray-600
                               file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                               file:text-sm file:font-medium file:bg-red-50 file:text-red-700
                               hover:file:bg-red-100">
                    <p class="text-xs text-gray-400 mt-1">Gunakan klip pendek (cukup 30-60 detik).</p>
                    <div id="fpreview-video" class="grid grid-cols-2 gap-3 mt-3" style="display:none"></div>
                </div>
            </div>
        </div>

        <div id="footer-sto" class="hidden flex justify-end gap-3">
            <a href="{{ route('stok.opname.index') }}"
                class="px-5 py-2 text-sm text-gray-500 hover:bg-gray-100 rounded-lg">Batal</a>
            <button type="submit" id="btn-simpan" disabled aria-disabled="true"
                style="pointer-events:none"
                class="px-5 py-2 text-sm bg-gray-300 text-white rounded-lg font-medium cursor-not-allowed">
                Simpan STO
            </button>
        </div>

    </form>

    {{-- Overlay loading saat upload media (tidak bisa di-tutup) --}}
    <div id="upload-overlay" aria-hidden="true"
        style="display:none;position:fixed;inset:0;background:rgba(15,15,15,0.78);z-index:9998;align-items:center;justify-content:center;backdrop-filter:blur(2px);">
        <div style="background:#fff;border-radius:16px;padding:28px 32px;max-width:480px;width:90%;box-shadow:0 12px 40px rgba(0,0,0,0.3);text-align:center;"
             onclick="event.stopPropagation()">
            <div style="margin-bottom:16px">
                <i id="ovl-icon" class="fa-solid fa-spinner fa-spin" style="font-size:36px;color:#A51616"></i>
            </div>
            <h3 id="ovl-title" style="font-size:16px;font-weight:600;color:#374151;margin-bottom:6px">Menyimpan Stok Opname...</h3>
            <p id="ovl-status" style="font-size:13px;color:#6b7280;margin-bottom:18px">Menyimpan data STO ke server...</p>

            <div id="ovl-progress-wrap" style="display:none">
                <div style="height:8px;background:#FDECEC;border-radius:4px;overflow:hidden;margin-bottom:6px">
                    <div id="ovl-progress-fill" style="height:100%;background:#A51616;width:0%;transition:width 0.25s ease;border-radius:4px"></div>
                </div>
                <p id="ovl-progress-pct" style="font-size:11px;color:#9ca3af;margin:0">0%</p>
            </div>

            <p id="ovl-counter" style="font-size:12px;color:#9ca3af;margin-top:10px"></p>
            <p style="font-size:11px;color:#d1d5db;margin-top:14px;font-style:italic">
                <i class="fa-solid fa-circle-info"></i>
                Jangan tutup atau refresh halaman ini sampai proses selesai.
            </p>
        </div>
    </div>

    <script>
        var csrfToken = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

        // ── Stok table ──────────────────────────────────────────────────────────
        function loadStokSistem() {
            const wilayahId = document.getElementById('wilayah_id').value;
            const tanggal   = document.getElementById('tanggal').value;
            if (!wilayahId) return;

            document.getElementById('loading').classList.remove('hidden');
            document.getElementById('tabel-sto').classList.add('hidden');
            document.getElementById('foto-bukti-section').classList.add('hidden');
            document.getElementById('footer-sto').classList.add('hidden');

            fetch(`{{ route('stok.opname.stok-sistem') }}?wilayah_id=${wilayahId}&tanggal=${tanggal}`)
                .then(r => r.json())
                .then(data => {
                    const tbody = document.getElementById('tbody-sto');
                    tbody.innerHTML = '';

                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">Tidak ada data stok untuk wilayah ini.</td></tr>';
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
                                <td class="px-4 py-3 text-right text-gray-500">${item.stok_freezer.toLocaleString('id')}</td>
                                <td class="px-4 py-3 text-right text-gray-500">${item.stok_gerobak.toLocaleString('id')}</td>
                                <td class="px-4 py-3 text-right font-semibold text-red-700">${item.stok_sistem.toLocaleString('id')}</td>
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

        // ── Foto & Video queues ─────────────────────────────────────────────────
        var fotoQueues   = { foto_real: [], berita_acara: [], video: [] };
        var fotoTabAktif = 'foto_real';
        var maxFotoPerTipe = 5;
        var maxVideoTotal  = 3;
        var MAX_VIDEO_BYTES = 100 * 1024 * 1024; // 100 MB — konsisten dengan validasi server

        function switchFotoTab(tab) {
            fotoTabAktif = tab;
            ['foto_real', 'berita_acara', 'video'].forEach(function(t) {
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
            var batas     = (tipe === 'video') ? maxVideoTotal : maxFotoPerTipe;
            var remaining = batas - current;
            var added     = 0;
            var ditolakBesar = [];

            // Snapshot ke array independen agar aman dari FileList yang ter-clear saat reset
            var snapshot = Array.from(files);

            snapshot.forEach(function(f) {
                // Pre-check ukuran video — tolak sebelum masuk antrian
                if (tipe === 'video' && f.size > MAX_VIDEO_BYTES) {
                    ditolakBesar.push(f);
                    return;
                }
                if (added < remaining) {
                    fotoQueues[tipe].push(f);
                    added++;
                }
            });

            if (ditolakBesar.length > 0) {
                var daftar = ditolakBesar.map(function(f) {
                    return '"' + f.name + '" (' + (f.size / 1024 / 1024).toFixed(1) + ' MB)';
                }).join(', ');
                showAlert('error', 'Video Terlalu Besar',
                    daftar + ' melebihi batas 100 MB. Silakan pilih/potong video lebih kecil.');
            }

            var lolosUkuran = snapshot.length - ditolakBesar.length;
            if (lolosUkuran > remaining) {
                var labelTipe = (tipe === 'video') ? 'video' : 'foto';
                showAlert('warning', 'Batas ' + (tipe === 'video' ? 'Video' : 'Foto'),
                    'Hanya ' + remaining + ' ' + labelTipe + ' lagi yang bisa ditambahkan (maks. ' + batas + ').');
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
            refreshMediaState();
        }

        function refreshMediaState() {
            var total = fotoQueues.foto_real.length + fotoQueues.berita_acara.length + fotoQueues.video.length;
            var hidden = document.getElementById('jumlah_media');
            if (hidden) hidden.value = total;

            var warn = document.getElementById('foto-warning');
            if (warn) warn.style.display = total >= 1 ? 'none' : '';

            refreshSubmitState();
        }

        function refreshSubmitState() {
            var btn = document.getElementById('btn-simpan');
            if (!btn) return;
            // Jangan ubah state saat sedang submit — setSubmittingState yang pegang kendali
            if (isSubmitting) return;

            var total = fotoQueues.foto_real.length + fotoQueues.berita_acara.length + fotoQueues.video.length;
            var bisaSimpan = total >= 1 && !stoAdaSudahKoreksi;

            if (bisaSimpan) {
                btn.disabled = false;
                btn.removeAttribute('aria-disabled');
                btn.style.pointerEvents = '';
                btn.classList.remove('bg-gray-300', 'bg-gray-400', 'cursor-not-allowed');
                btn.classList.add('bg-red-700', 'hover:bg-red-800');
            } else {
                btn.disabled = true;
                btn.setAttribute('aria-disabled', 'true');
                btn.style.pointerEvents = 'none';
                btn.classList.add('bg-gray-300', 'cursor-not-allowed');
                btn.classList.remove('bg-red-700', 'hover:bg-red-800');
            }
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

                var isVideo = (tipe === 'video') || (file.type && file.type.indexOf('video/') === 0);
                var mediaEl;
                if (isVideo) {
                    mediaEl = document.createElement('video');
                    mediaEl.muted = true;
                    mediaEl.preload = 'metadata';
                    mediaEl.controls = true;
                    mediaEl.style.cssText = 'width:100%;min-height:140px;background:#000;object-fit:cover;border-radius:8px 8px 0 0;display:block';
                    mediaEl.src = URL.createObjectURL(file);
                } else {
                    mediaEl = document.createElement('img');
                    mediaEl.alt = file.name;
                    mediaEl.style.cssText = 'width:100%;min-height:140px;object-fit:cover;border-radius:8px 8px 0 0;display:block';
                    var reader = new FileReader();
                    reader.onload = (function(i) { return function(e) { i.src = e.target.result; }; })(mediaEl);
                    reader.readAsDataURL(file);
                }
                card.appendChild(mediaEl);

                var info = document.createElement('div');
                info.className = 'px-2 py-1.5';

                var nameEl = document.createElement('p');
                nameEl.className = 'text-xs text-gray-600 truncate';
                nameEl.textContent = file.name;

                var sizeEl = document.createElement('p');
                sizeEl.className = 'text-xs text-gray-400 mt-0.5';
                var sizeMB = file.size / 1024 / 1024;
                sizeEl.textContent = (sizeMB >= 1 ? sizeMB.toFixed(1) + ' MB' : Math.ceil(file.size / 1024) + ' KB');

                info.appendChild(nameEl);
                info.appendChild(sizeEl);
                card.appendChild(info);

                var xBtn = document.createElement('button');
                xBtn.type = 'button';
                xBtn.setAttribute('aria-label', 'Hapus');
                xBtn.style.cssText = 'position:absolute;top:8px;right:8px;width:28px;height:28px;background:#A51616;color:#fff;border:none;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:14px;line-height:1;padding:0;z-index:10;box-shadow:0 2px 6px rgba(0,0,0,0.25);';
                xBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                xBtn.onmouseover = function() { xBtn.style.background = '#7c1010'; };
                xBtn.onmouseout  = function() { xBtn.style.background = '#A51616'; };
                xBtn.onclick = (function(t, i) { return function() { removeFromFotoQueue(t, i); }; })(tipe, idx);
                card.appendChild(xBtn);

                preview.appendChild(card);
            });
        }

        // ── Cek STO existing di wilayah+tanggal yang sama ────────────────────────
        var stoAdaBelumKoreksi = false;
        var stoAdaSudahKoreksi = false;

        function cekStoExisting() {
            var wilayahId = document.getElementById('wilayah_id').value;
            var tanggal   = document.getElementById('tanggal').value;
            var bBlocked  = document.getElementById('banner-sto-blocked');
            var bWarning  = document.getElementById('banner-sto-warning');

            stoAdaBelumKoreksi = false;
            stoAdaSudahKoreksi = false;
            if (bBlocked) bBlocked.style.display = 'none';
            if (bWarning) bWarning.style.display = 'none';

            if (!wilayahId || !tanggal) { refreshSubmitState(); return; }

            fetch('{{ route("api.cek-sto-existing") }}?wilayah_id=' + wilayahId + '&tanggal=' + tanggal)
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    stoAdaSudahKoreksi = !!res.ada_sto_sudah_koreksi;
                    stoAdaBelumKoreksi = !!res.ada_sto_belum_koreksi;

                    if (stoAdaSudahKoreksi && bBlocked) bBlocked.style.display = '';
                    else if (stoAdaBelumKoreksi && bWarning) bWarning.style.display = '';

                    refreshSubmitState();
                })
                .catch(function() { /* silent */ });
        }

        // ── Form submit (3 langkah) — guard double-submit ───────────────────────
        var isSubmitting = false;

        document.getElementById('form-sto').addEventListener('submit', function(e) {
            e.preventDefault();
            if (isSubmitting) return false;

            // Blok keras kalau ada STO sudah dikoreksi
            if (stoAdaSudahKoreksi) {
                showAlert('error', 'Tidak Dapat Disimpan',
                    'Sudah ada STO untuk wilayah & tanggal ini yang koreksinya telah diterapkan. Batalkan STO tersebut dulu jika ingin opname ulang.');
                return false;
            }

            // Warning + konfirmasi kalau ada STO belum dikoreksi
            if (stoAdaBelumKoreksi && typeof Swal !== 'undefined') {
                var formEl = this;
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    html: 'Sudah ada STO lain untuk wilayah & tanggal ini <strong>(belum dikoreksi)</strong>.<br><br>Lanjutkan membuat STO baru? Pastikan tidak ada duplikasi.',
                    showCancelButton: true,
                    confirmButtonColor: '#A51616',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal',
                }).then(function(result) {
                    if (result.isConfirmed) submitSTO(formEl);
                });
                return false;
            }

            submitSTO(this);
        });

        function setSubmittingState(state, text) {
            var btn = document.getElementById('btn-simpan');
            isSubmitting = state;
            if (!btn) return;
            // Disable benar-benar: attribute + aria + pointer-events
            btn.disabled = state;
            if (state) {
                btn.setAttribute('aria-disabled', 'true');
                btn.style.pointerEvents = 'none';
                btn.classList.add('bg-gray-400', 'cursor-not-allowed');
                btn.classList.remove('bg-red-700', 'hover:bg-red-800');
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>' + (text || 'Menyimpan...');
            } else {
                btn.removeAttribute('aria-disabled');
                btn.style.pointerEvents = '';
                btn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                btn.classList.add('bg-red-700', 'hover:bg-red-800');
                btn.textContent = 'Simpan STO';
            }
        }

        // ── Overlay loading helpers ─────────────────────────────────────────────
        function showOverlay() {
            var ovl = document.getElementById('upload-overlay');
            if (ovl) {
                ovl.style.display = 'flex';
                ovl.setAttribute('aria-hidden', 'false');
            }
            // Cegah scroll body saat overlay aktif
            document.body.style.overflow = 'hidden';
            // Cegah user tinggalkan halaman
            window.addEventListener('beforeunload', beforeUnloadHandler);
        }

        function hideOverlay() {
            var ovl = document.getElementById('upload-overlay');
            if (ovl) {
                ovl.style.display = 'none';
                ovl.setAttribute('aria-hidden', 'true');
            }
            document.body.style.overflow = '';
            window.removeEventListener('beforeunload', beforeUnloadHandler);
        }

        // Helper: remove beforeunload sebelum redirect — cegah dialog "Leave site?" saat sukses
        function safeRedirect(url) {
            window.removeEventListener('beforeunload', beforeUnloadHandler);
            window.location.href = url;
        }

        function beforeUnloadHandler(e) {
            e.preventDefault();
            e.returnValue = 'Upload sedang berlangsung. Yakin ingin meninggalkan halaman?';
            return e.returnValue;
        }

        function setOverlay(opts) {
            opts = opts || {};
            if (opts.title)    document.getElementById('ovl-title').textContent  = opts.title;
            if (opts.status)   document.getElementById('ovl-status').textContent = opts.status;
            if (opts.counter !== undefined) document.getElementById('ovl-counter').textContent = opts.counter;
            var wrap = document.getElementById('ovl-progress-wrap');
            var fill = document.getElementById('ovl-progress-fill');
            var pct  = document.getElementById('ovl-progress-pct');
            if (opts.progress !== undefined) {
                wrap.style.display = '';
                var p = Math.max(0, Math.min(100, Math.round(opts.progress)));
                fill.style.width = p + '%';
                pct.textContent  = p + '%';
            } else if (opts.hideProgress) {
                wrap.style.display = 'none';
                fill.style.width   = '0%';
                pct.textContent    = '0%';
            }
            if (opts.icon) {
                var iconEl = document.getElementById('ovl-icon');
                iconEl.className = opts.icon;
                iconEl.style.color = opts.iconColor || '#A51616';
            }
        }

        // ── Upload helper: 1 file, Promise-based ────────────────────────────────
        function uploadOneFile(uploadUrl, item, idx, total) {
            return new Promise(function(resolve, reject) {
                var fd = new FormData();
                fd.append('foto', item.file);
                fd.append('tipe', item.tipe);

                var isVideo  = item.tipe === 'video';
                var label    = isVideo ? 'video' : 'foto';
                var fileName = item.file.name || (label + ' #' + (idx + 1));
                var prefix   = isVideo ? 'Mengupload video' : 'Mengupload foto';

                setOverlay({
                    title:   'Mengupload media...',
                    status:  prefix + ' ' + (idx + 1) + '/' + total + ': ' + fileName,
                    counter: 'Media ' + (idx + 1) + ' dari ' + total,
                    progress: 0,
                });

                var xhr = new XMLHttpRequest();
                xhr.open('POST', uploadUrl, true);
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.onprogress = function(e) {
                    if (!e.lengthComputable) return;
                    var pct = Math.round(e.loaded / e.total * 100);
                    if (pct < 100) {
                        setOverlay({
                            status:   prefix + ' ' + pct + '% (' + fileName + ')',
                            progress: pct,
                        });
                    } else {
                        // 100% transfer selesai, server menyimpan media
                        setOverlay({
                            status:   'Menyimpan media ke server...',
                            progress: 100,
                        });
                    }
                };

                xhr.onload = function() {
                    var data = null;
                    try { data = JSON.parse(xhr.responseText || '{}'); } catch (e) { data = null; }

                    if (xhr.status >= 200 && xhr.status < 300 && data && data.success) {
                        resolve(data);
                        return;
                    }

                    var msg = (data && data.error) || (data && data.message) || ('Upload gagal (HTTP ' + xhr.status + ').');
                    if (xhr.status === 422 && data && data.errors) {
                        msg = Object.values(data.errors).flat().join('\n');
                    }
                    reject({ status: xhr.status, message: msg, file: fileName });
                };

                xhr.onerror = function() {
                    reject({ status: 0, message: 'Koneksi jaringan terputus saat upload.', file: fileName });
                };

                xhr.ontimeout = function() {
                    reject({ status: 0, message: 'Upload timeout. File mungkin terlalu besar.', file: fileName });
                };

                // Timeout besar untuk akomodasi upload video besar (10 menit)
                xhr.timeout = 600000;
                xhr.send(fd);
            });
        }

        // ── Submit STO: STRICT sequential await, no early redirect ──────────────
        function submitSTO(form) {
            // Guard berlapis: cegah double-call
            if (isSubmitting) return;
            setSubmittingState(true, 'Menyimpan STO...');
            showOverlay();
            setOverlay({
                icon: 'fa-solid fa-spinner fa-spin',
                title: 'Menyimpan Stok Opname...',
                status: 'Menyimpan data STO ke server...',
                counter: '',
                hideProgress: true,
            });

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
                if (!res.ok || !res.data.success) {
                    hideOverlay();
                    setSubmittingState(false);
                    if (res.status === 422 && res.data.errors) {
                        var msgs = Object.values(res.data.errors).flat().join('\n');
                        showAlert('error', 'Validasi Gagal', msgs);
                    } else {
                        showAlert('error', 'Gagal Menyimpan', res.data.error || res.data.message || 'Gagal menyimpan STO.');
                    }
                    return;
                }

                var uploadUrl   = res.data.upload_url;
                var redirectUrl = res.data.redirect;
                var showUrl     = res.data.show_url;
                var cancelUrl   = res.data.cancel_url;
                var flashMsg    = res.data.flash_message || 'Stok Opname berhasil disimpan.';

                // Kumpulkan semua file
                var allFiles = [];
                ['foto_real', 'berita_acara', 'video'].forEach(function(tipe) {
                    fotoQueues[tipe].forEach(function(file) {
                        allFiles.push({ file: file, tipe: tipe });
                    });
                });

                // Edge case: tidak ada media yang dipilih (seharusnya tidak terjadi karena validasi)
                if (allFiles.length === 0) {
                    setOverlay({
                        icon: 'fa-solid fa-circle-check',
                        iconColor: '#16a34a',
                        title: 'Berhasil!',
                        status: 'STO tersimpan. Mengalihkan ke daftar...',
                        hideProgress: true,
                    });
                    try { sessionStorage.setItem('flash_success', flashMsg); } catch (e) {}
                    setTimeout(function() { safeRedirect(redirectUrl); }, 400);
                    return;
                }

                // Upload sequential — KUMPULKAN sukses/gagal, JANGAN stop di error pertama
                var idx        = 0;
                var sukses     = 0;
                var gagalList  = [];

                function next() {
                    if (idx >= allFiles.length) {
                        finalizeFlow();
                        return;
                    }

                    uploadOneFile(uploadUrl, allFiles[idx], idx, allFiles.length)
                        .then(function() {
                            sukses++;
                            idx++;
                            next();
                        })
                        .catch(function(err) {
                            gagalList.push({
                                file: err.file || allFiles[idx].file.name || 'media',
                                message: err.message || 'Error tidak diketahui',
                            });
                            idx++;
                            next();
                        });
                }

                function finalizeFlow() {
                    var totalMedia = allFiles.length;
                    var totalGagal = gagalList.length;

                    if (sukses === 0) {
                        // SEMUA GAGAL → rollback STO
                        setOverlay({
                            icon: 'fa-solid fa-rotate fa-spin',
                            iconColor: '#A51616',
                            title: 'Membatalkan STO...',
                            status: 'Semua upload media gagal. STO akan dibatalkan.',
                            hideProgress: true,
                            counter: '',
                        });
                        rollbackSTO(cancelUrl, totalGagal);
                        return;
                    }

                    if (totalGagal > 0) {
                        // SEBAGIAN GAGAL → STO tetap tersimpan, redirect ke detail
                        hideOverlay();
                        setSubmittingState(false);
                        var daftar = gagalList.map(function(g) { return '• ' + g.file; }).join('\n');
                        var pesan = 'STO tersimpan, tapi ' + totalGagal + ' dari ' + totalMedia + ' media gagal diupload:\n\n' +
                                    daftar +
                                    '\n\nAnda bisa menambahkannya nanti dari halaman Detail STO.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Sebagian Media Gagal',
                                text: pesan,
                                confirmButtonColor: '#A51616',
                                confirmButtonText: 'Buka Detail STO',
                            }).then(function() {
                                try { sessionStorage.setItem('flash_success', 'STO tersimpan. Lengkapi media yang gagal di sini.'); } catch (e) {}
                                safeRedirect(showUrl);
                            });
                        } else {
                            alert(pesan);
                            safeRedirect(showUrl);
                        }
                        return;
                    }

                    // SEMUA SUKSES
                    setOverlay({
                        icon: 'fa-solid fa-circle-check',
                        iconColor: '#16a34a',
                        title: 'Selesai!',
                        status: 'Semua media tersimpan. Mengalihkan ke daftar...',
                        hideProgress: true,
                        counter: '',
                    });
                    try { sessionStorage.setItem('flash_success', flashMsg); } catch (e) {}
                    setTimeout(function() { safeRedirect(redirectUrl); }, 600);
                }

                function rollbackSTO(url, jumlahGagal) {
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    })
                    .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
                    .then(function(out) {
                        hideOverlay();
                        setSubmittingState(false);
                        if (out.ok && out.data.success) {
                            showAlert(
                                'error',
                                'STO Dibatalkan',
                                'STO dibatalkan karena tidak ada bukti media yang berhasil diupload (' + jumlahGagal + ' file gagal). Silakan coba lagi.'
                            );
                        } else {
                            showAlert(
                                'error',
                                'Gagal Membatalkan STO',
                                (out.data && out.data.error) || 'STO tidak dapat dibatalkan otomatis. Silakan hapus manual dari daftar STO.'
                            );
                        }
                    })
                    .catch(function() {
                        hideOverlay();
                        setSubmittingState(false);
                        showAlert(
                            'error',
                            'Gagal Membatalkan STO',
                            'STO tidak dapat dibatalkan otomatis (jaringan). Silakan hapus manual dari daftar STO.'
                        );
                    });
                }

                next();
            })
            .catch(function() {
                hideOverlay();
                setSubmittingState(false);
                showAlert('error', 'Error Jaringan', 'Terjadi kesalahan jaringan. Coba lagi.');
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
            ['foto_real', 'berita_acara', 'video'].forEach(function(tipe) {
                var input = document.getElementById('finput-' + tipe);
                if (!input) return;
                input.addEventListener('change', function() {
                    addFotoFiles(tipe, this.files);
                    this.value = '';
                });
            });

            // Auto load jika koordinator sudah terpilih wilayah
            var sel = document.getElementById('wilayah_id');
            if (sel && sel.value) {
                loadStokSistem();
                cekStoExisting();
            }
        });
    </script>

@endsection
