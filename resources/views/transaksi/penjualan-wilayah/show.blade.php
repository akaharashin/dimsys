@extends('layouts.app')
@section('title', 'Detail Pindah Stok')

@section('content')

@php
    $user        = auth()->user();
    $bolehUpload = $user->hasRole('admin_pusat') ||
                   ($user->hasRole('koordinator') &&
                    $user->wilayah_id === $penjualanWilayah->wilayah_tujuan_id);

    $fotoReal    = $penjualanWilayah->getMedia('foto_real');
    $beritaAcara = $penjualanWilayah->getMedia('berita_acara');
    $videos      = $penjualanWilayah->getMedia('video');
    $totalFoto   = $fotoReal->count() + $beritaAcara->count();
    $totalVideo  = $videos->count();
    $bisaUpload  = $bolehUpload && in_array($penjualanWilayah->status, ['menunggu', 'disetujui']);

    $bolehApprove = ($user->hasRole('admin_pusat') ||
                    ($user->hasRole('koordinator') && $user->wilayah_id === $penjualanWilayah->wilayah_tujuan_id)) &&
                   $penjualanWilayah->tipe === 'transfer' &&
                   $penjualanWilayah->status === 'menunggu';
    $adaFoto = $totalFoto > 0;
@endphp

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('transaksi.penjualan-wilayah.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali</a>
    <h2 class="text-2xl font-bold text-gray-700">Detail Pindah Stok</h2>
</div>

@if(session('error'))
<div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif
@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

{{-- Info card --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-gray-400 text-xs uppercase">Tipe</p>
            <p class="mt-1">
                @if($penjualanWilayah->tipe === 'transfer')
                    <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-600">Transfer</span>
                @else
                    <span class="px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-700">Penjualan</span>
                @endif
            </p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase">Status</p>
            <p class="mt-1">
                @php
                    $statusClass = match($penjualanWilayah->status) {
                        'menunggu'  => 'bg-yellow-100 text-yellow-700',
                        'disetujui' => 'bg-green-100 text-green-600',
                        'ditolak'   => 'bg-red-100 text-red-600',
                        default     => 'bg-gray-100 text-gray-600',
                    };
                    $statusLabel = match($penjualanWilayah->status) {
                        'menunggu'  => 'Menunggu',
                        'disetujui' => 'Disetujui',
                        'ditolak'   => 'Ditolak',
                        default     => ucfirst($penjualanWilayah->status),
                    };
                @endphp
                <span class="px-2 py-1 rounded-full text-xs {{ $statusClass }}">{{ $statusLabel }}</span>
            </p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase">Tanggal</p>
            <p class="font-medium text-gray-700 mt-1">{{ \Carbon\Carbon::parse($penjualanWilayah->tanggal)->format('d M Y') }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase">Dari</p>
            <p class="font-medium text-gray-700 mt-1">{{ $penjualanWilayah->wilayahAsal->nama }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs uppercase">Ke</p>
            <p class="font-medium text-gray-700 mt-1">{{ $penjualanWilayah->wilayahTujuan->nama }}</p>
        </div>
        @if($penjualanWilayah->tipe === 'penjualan')
        <div>
            <p class="text-gray-400 text-xs uppercase">Status Bayar</p>
            <p class="mt-1">
                <span class="px-2 py-1 rounded-full text-xs
                    {{ $penjualanWilayah->status_bayar === 'lunas' ? 'bg-green-100 text-green-600' :
                      ($penjualanWilayah->status_bayar === 'sebagian' ? 'bg-yellow-100 text-yellow-600' :
                       'bg-red-100 text-red-600') }}">
                    {{ ucfirst(str_replace('_', ' ', $penjualanWilayah->status_bayar)) }}
                </span>
            </p>
        </div>
        @endif
        @if($penjualanWilayah->keterangan)
        <div class="md:col-span-2">
            <p class="text-gray-400 text-xs uppercase">Keterangan</p>
            <p class="font-medium text-gray-700 mt-1">{{ $penjualanWilayah->keterangan }}</p>
        </div>
        @endif
    </div>
</div>

{{-- Tabel produk --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Produk</th>
                <th class="px-4 py-3 text-right">Jumlah</th>
                @if($penjualanWilayah->tipe === 'penjualan')
                <th class="px-4 py-3 text-right">Harga Agen</th>
                <th class="px-4 py-3 text-right">Subtotal</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($penjualanWilayah->details as $d)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-700">{{ $d->produk->nama }}</td>
                <td class="px-4 py-3 text-right text-gray-600">{{ number_format($d->jumlah) }} pcs</td>
                @if($penjualanWilayah->tipe === 'penjualan')
                <td class="px-4 py-3 text-right text-gray-600">Rp {{ number_format($d->harga_agen) }}</td>
                <td class="px-4 py-3 text-right font-medium text-gray-700">Rp {{ number_format($d->subtotal) }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
        @if($penjualanWilayah->tipe === 'penjualan')
        <tfoot class="bg-gray-50">
            <tr>
                <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-600">Total</td>
                <td class="px-4 py-3 text-right font-bold text-gray-700">
                    Rp {{ number_format($penjualanWilayah->total) }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

{{-- Approval actions --}}
@if($bolehApprove)
<div class="bg-white rounded-xl shadow-sm p-6 mb-4">
    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Tindakan Persetujuan</h3>
    @if(!$adaFoto)
    <div class="flex items-center gap-2 mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
        <svg class="w-4 h-4 text-yellow-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <p class="text-sm text-yellow-700">Upload minimal 1 foto bukti sebelum konfirmasi terima.</p>
    </div>
    @endif
    <div class="flex gap-3 flex-wrap">
        <form method="POST" action="{{ route('transaksi.penjualan-wilayah.approve', $penjualanWilayah) }}">
            @csrf
            <button type="submit"
                {{ !$adaFoto ? 'disabled' : '' }}
                class="px-5 py-2 text-sm rounded-lg font-medium transition-colors
                    {{ $adaFoto
                        ? 'bg-green-500 hover:bg-green-600 text-white cursor-pointer'
                        : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
                Konfirmasi Terima
            </button>
        </form>
        <form method="POST" action="{{ route('transaksi.penjualan-wilayah.reject', $penjualanWilayah) }}"
            data-confirm="Tolak pindah stok ini?">
            @csrf
            <button type="submit"
                class="px-5 py-2 text-sm bg-red-500 hover:bg-red-600 text-white rounded-lg font-medium">
                Tolak
            </button>
        </form>
    </div>
</div>
@endif

{{-- Foto & Video --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Foto & Video Bukti</h3>
        <div class="flex gap-4 text-xs text-gray-400">
            <span>Foto: <strong id="counter-foto" class="text-gray-600">{{ $totalFoto }}</strong>/10</span>
            <span>Video: <strong id="counter-video" class="text-gray-600">{{ $totalVideo }}</strong>/3</span>
        </div>
    </div>

    {{-- Tab buttons --}}
    <div class="flex gap-0 mb-4 border-b border-gray-100">
        <button onclick="switchTab('foto_real')" id="tab-foto_real"
            class="px-4 py-2 text-sm font-medium border-b-2 border-red-600 text-red-600 -mb-px">
            Foto Real (<span id="tab-count-foto_real">{{ $fotoReal->count() }}</span>)
        </button>
        <button onclick="switchTab('berita_acara')" id="tab-berita_acara"
            class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-400 -mb-px">
            Berita Acara (<span id="tab-count-berita_acara">{{ $beritaAcara->count() }}</span>)
        </button>
        <button onclick="switchTab('video')" id="tab-video"
            class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-400 -mb-px">
            Video (<span id="tab-count-video">{{ $videos->count() }}</span>)
        </button>
    </div>

    {{-- Panel: Foto Real --}}
    <div id="panel-foto_real">
        @if($fotoReal->isEmpty())
            <p class="text-sm text-gray-400 py-4 text-center">Belum ada foto real.</p>
        @else
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-2">
            @foreach($fotoReal as $i => $foto)
            <div class="relative group rounded-lg border border-gray-100">
                <img src="{{ asset('storage/' . $foto->id . '/' . $foto->file_name) }}" alt="{{ $foto->file_name }}"
                    class="w-full h-36 object-cover rounded-t-lg cursor-pointer hover:opacity-90 transition"
                    onclick="openLightbox('foto_real', {{ $i }})">
                <div class="px-2 py-1.5">
                    <p class="text-xs text-gray-500 truncate">{{ $foto->file_name }}</p>
                    <p class="text-xs text-gray-400">{{ (int) ceil($foto->size / 1024) }} KB · {{ \Carbon\Carbon::parse($foto->created_at)->format('d/m/y') }}</p>
                </div>
                @if($bolehUpload)
                <button onclick="hapusFile({{ $foto->id }}, this)"
                    class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs
                           opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center hover:bg-red-600">
                    ×
                </button>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Panel: Berita Acara --}}
    <div id="panel-berita_acara" style="display:none">
        @if($beritaAcara->isEmpty())
            <p class="text-sm text-gray-400 py-4 text-center">Belum ada foto berita acara.</p>
        @else
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-2">
            @foreach($beritaAcara as $i => $foto)
            <div class="relative group rounded-lg border border-gray-100">
                <img src="{{ asset('storage/' . $foto->id . '/' . $foto->file_name) }}" alt="{{ $foto->file_name }}"
                    class="w-full h-36 object-cover rounded-t-lg cursor-pointer hover:opacity-90 transition"
                    onclick="openLightbox('berita_acara', {{ $i }})">
                <div class="px-2 py-1.5">
                    <p class="text-xs text-gray-500 truncate">{{ $foto->file_name }}</p>
                    <p class="text-xs text-gray-400">{{ (int) ceil($foto->size / 1024) }} KB · {{ \Carbon\Carbon::parse($foto->created_at)->format('d/m/y') }}</p>
                </div>
                @if($bolehUpload)
                <button onclick="hapusFile({{ $foto->id }}, this)"
                    class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs
                           opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center hover:bg-red-600">
                    ×
                </button>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Panel: Video --}}
    <div id="panel-video" style="display:none">
        @if($videos->isEmpty())
            <p class="text-sm text-gray-400 py-4 text-center">Belum ada video.</p>
        @else
        <div class="grid grid-cols-2 gap-3 mb-2">
            @foreach($videos as $i => $vid)
            @php $durasi = $vid->getCustomProperty('durasi'); @endphp
            <div class="relative group rounded-lg border border-gray-100">
                <div class="relative cursor-pointer" onclick="openLightbox('video', {{ $i }})">
                    <video class="w-full h-36 object-cover rounded-t-lg" preload="metadata" muted data-thumb>
                        <source src="{{ asset('storage/' . $vid->id . '/' . $vid->file_name) }}" type="video/mp4">
                    </video>
                    {{-- Play button overlay --}}
                    <div class="absolute inset-0 flex items-center justify-center rounded-t-lg">
                        <div class="w-10 h-10 bg-white/80 backdrop-blur-sm rounded-full flex items-center justify-center shadow">
                            <div style="width:0;height:0;border-left:14px solid #f97316;border-top:9px solid transparent;border-bottom:9px solid transparent;margin-left:3px"></div>
                        </div>
                    </div>
                </div>
                <div class="px-2 py-1.5">
                    <p class="text-xs text-gray-500 truncate">{{ $vid->file_name }}</p>
                    <div class="flex gap-2 text-xs text-gray-400">
                        <span>{{ $vid->size >= 1048576 ? round($vid->size / 1048576, 1) . ' MB' : (int) ceil($vid->size / 1024) . ' KB' }}</span>
                        @if($durasi)
                        <span>{{ gmdate('i:s', $durasi) }}</span>
                        @endif
                        <span>{{ \Carbon\Carbon::parse($vid->created_at)->format('d/m/y') }}</span>
                    </div>
                </div>
                @if($bolehUpload)
                <button onclick="hapusFile({{ $vid->id }}, this)"
                    class="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs
                           opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center hover:bg-red-600">
                    ×
                </button>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Form Upload --}}
    @if($bisaUpload)
    <div id="upload-section" class="border-t border-gray-100 pt-4 mt-2">
        <input type="hidden" id="tipe-foto-aktif" value="foto_real">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-3" id="upload-section-title">Upload Foto</p>
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1" id="upload-label">
                    Pilih Foto (maks. {{ 10 - $totalFoto }} lagi · JPG PNG WebP)
                </label>
                <input type="file" id="upload-input" accept="image/*" multiple
                    class="text-sm text-gray-600
                           file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                           file:text-sm file:font-medium file:bg-red-50 file:text-red-700
                           hover:file:bg-red-100">
            </div>
            <button onclick="startUpload()" id="upload-btn"
                class="px-4 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg font-medium">
                Upload
            </button>
        </div>

        {{-- Preview antrian --}}
        <div id="upload-preview" class="grid grid-cols-2 gap-3 mt-4" style="display:none;padding-top:12px"></div>

        {{-- Progress --}}
        <div id="upload-progress" class="mt-3" style="display:none">
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                <div class="w-4 h-4 border-2 border-red-600 border-t-transparent rounded-full animate-spin flex-shrink-0"></div>
                <span id="upload-status">Mengupload...</span>
            </div>
            <div id="upload-progress-bar" style="display:none;height:6px;background:#fecaca;border-radius:3px;overflow:hidden">
                <div id="upload-progress-fill" style="height:100%;background:#A51616;border-radius:3px;transition:width 0.3s;width:0%"></div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Lightbox --}}
<div id="lightbox"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.93);z-index:9999;align-items:center;justify-content:center"
    onclick="if(event.target===this)closeLightbox()">

    <img id="lightbox-img" src="" alt=""
        style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:8px;box-shadow:0 4px 32px rgba(0,0,0,0.5)">

    <video id="lightbox-video" controls
        style="display:none;max-width:90vw;max-height:90vh;border-radius:8px;box-shadow:0 4px 32px rgba(0,0,0,0.5)">
    </video>

    <button id="lb-prev" onclick="prevItem()"
        style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:white;background:rgba(255,255,255,0.15);border:none;border-radius:50%;width:44px;height:44px;font-size:22px;cursor:pointer;line-height:1">
        ‹
    </button>
    <button id="lb-next" onclick="nextItem()"
        style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);color:white;background:rgba(255,255,255,0.15);border:none;border-radius:50%;width:44px;height:44px;font-size:22px;cursor:pointer;line-height:1">
        ›
    </button>
    <button onclick="closeLightbox()"
        style="position:absolute;top:1rem;right:1rem;color:white;background:rgba(255,255,255,0.15);border:none;border-radius:50%;width:36px;height:36px;font-size:18px;cursor:pointer;line-height:1">
        ×
    </button>
    <div id="lightbox-counter"
        style="position:absolute;bottom:1rem;left:50%;transform:translateX(-50%);color:rgba(255,255,255,0.7);font-size:13px">
    </div>
</div>

<script>
// ── Data dari server ──────────────────────────────────────────────────────────
var fotosData  = {
    foto_real:    @json($fotoReal->map(fn($f) => asset('storage/' . $f->id . '/' . $f->file_name))->values()),
    berita_acara: @json($beritaAcara->map(fn($f) => asset('storage/' . $f->id . '/' . $f->file_name))->values()),
};
var videosData = @json($videos->map(fn($v) => asset('storage/' . $v->id . '/' . $v->file_name))->values());

// ── State ─────────────────────────────────────────────────────────────────────
var currentTab    = 'foto_real';
var currentIdx    = 0;
var selectedFiles = [];
var objectUrls    = [];

var totalFoto  = {{ $totalFoto }};
var totalVideo = {{ $totalVideo }};
var maxFoto    = 10;
var maxVideo   = 3;

var uploadUrl  = '{{ route("transaksi.penjualan-wilayah.foto.upload", $penjualanWilayah->id) }}';
var hapusBase  = '{{ url("transaksi/penjualan-wilayah/foto") }}';
var csrfToken  = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

// ── Tab ───────────────────────────────────────────────────────────────────────
function switchTab(tab) {
    currentTab = tab;

    // CSS show/hide: konten panel TIDAK pernah dihapus dari DOM
    ['foto_real', 'berita_acara', 'video'].forEach(function(t) {
        document.getElementById('panel-' + t).style.display = t === tab ? '' : 'none';
        var btn = document.getElementById('tab-' + t);
        if (t === tab) {
            btn.classList.add('border-red-600', 'text-red-600');
            btn.classList.remove('border-transparent', 'text-gray-400');
        } else {
            btn.classList.remove('border-red-600', 'text-red-600');
            btn.classList.add('border-transparent', 'text-gray-400');
        }
    });

    var hidden = document.getElementById('tipe-foto-aktif');
    if (hidden) hidden.value = tab;

    var inputEl  = document.getElementById('upload-input');
    var labelEl  = document.getElementById('upload-label');
    var titleEl  = document.getElementById('upload-section-title');

    if (inputEl) {
        if (tab === 'video') {
            inputEl.accept   = 'video/*';
            inputEl.multiple = true;
            if (labelEl)  labelEl.textContent  = 'Pilih Video (maks. ' + Math.max(0, maxVideo - totalVideo) + ' lagi · MP4 MOV AVI WebM)';
            if (titleEl)  titleEl.textContent  = 'Upload Video';
        } else {
            inputEl.accept   = 'image/*';
            inputEl.multiple = true;
            if (labelEl)  labelEl.textContent  = 'Pilih Foto (maks. ' + Math.max(0, maxFoto - totalFoto) + ' lagi · JPG PNG WebP)';
            if (titleEl)  titleEl.textContent  = 'Upload Foto';
        }
    }

    clearQueue();
    updateUploadSectionVisibility();
}

function updateUploadSectionVisibility() {
    var section = document.getElementById('upload-section');
    if (!section) return;
    var tab     = (document.getElementById('tipe-foto-aktif') || {}).value || 'foto_real';
    var canMore = tab === 'video' ? totalVideo < maxVideo : totalFoto < maxFoto;
    section.style.display = canMore ? '' : 'none';
}

// ── Lightbox ──────────────────────────────────────────────────────────────────
function openLightbox(tab, idx) {
    currentTab = tab;
    currentIdx = idx;
    updateLightboxItem();
    document.getElementById('lightbox').style.display = 'flex';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    var vid = document.getElementById('lightbox-video');
    if (vid) { vid.pause(); vid.removeAttribute('src'); vid.load(); }
}

function updateLightboxItem() {
    var imgEl   = document.getElementById('lightbox-img');
    var vidEl   = document.getElementById('lightbox-video');
    var prevBtn = document.getElementById('lb-prev');
    var nextBtn = document.getElementById('lb-next');

    if (currentTab === 'video') {
        imgEl.style.display = 'none';
        vidEl.style.display = '';
        vidEl.src = videosData[currentIdx] || '';
        vidEl.play().catch(function() {});
        document.getElementById('lightbox-counter').textContent = (currentIdx + 1) + ' / ' + videosData.length;
        var showNav = videosData.length > 1;
        prevBtn.style.display = showNav ? '' : 'none';
        nextBtn.style.display = showNav ? '' : 'none';
    } else {
        vidEl.style.display = 'none';
        vidEl.pause();
        imgEl.style.display = '';
        var urls = fotosData[currentTab] || [];
        imgEl.src = urls[currentIdx] || '';
        document.getElementById('lightbox-counter').textContent = (currentIdx + 1) + ' / ' + urls.length;
        var showNav = urls.length > 1;
        prevBtn.style.display = showNav ? '' : 'none';
        nextBtn.style.display = showNav ? '' : 'none';
    }
}

function prevItem() {
    if (currentTab === 'video') {
        currentIdx = (currentIdx - 1 + videosData.length) % videosData.length;
    } else {
        var urls = fotosData[currentTab] || [];
        currentIdx = (currentIdx - 1 + urls.length) % urls.length;
    }
    updateLightboxItem();
}

function nextItem() {
    if (currentTab === 'video') {
        currentIdx = (currentIdx + 1) % videosData.length;
    } else {
        var urls = fotosData[currentTab] || [];
        currentIdx = (currentIdx + 1) % urls.length;
    }
    updateLightboxItem();
}

document.addEventListener('keydown', function(e) {
    var lb = document.getElementById('lightbox');
    if (!lb || lb.style.display === 'none') return;
    if (e.key === 'Escape')     closeLightbox();
    if (e.key === 'ArrowLeft')  prevItem();
    if (e.key === 'ArrowRight') nextItem();
});

// ── Thumbnail video (seekTo 0.1s agar frame pertama muncul) ──────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('video[data-thumb]').forEach(function(v) {
        v.addEventListener('loadedmetadata', function() { this.currentTime = 0.1; });
    });

    var inputEl = document.getElementById('upload-input');
    if (inputEl) {
        inputEl.addEventListener('change', function() {
            Array.from(this.files).forEach(function(f) {
                selectedFiles.push(f);
                if (f.type.startsWith('video/')) {
                    objectUrls.push(URL.createObjectURL(f));
                } else {
                    objectUrls.push(null);
                }
            });
            this.value = '';
            renderPreviews();
        });
    }

    updateUploadSectionVisibility();
});

// ── Antrian preview ───────────────────────────────────────────────────────────
function clearQueue() {
    objectUrls.forEach(function(u) { if (u) URL.revokeObjectURL(u); });
    selectedFiles = [];
    objectUrls    = [];
    renderPreviews();
}

function removeFromQueue(idx) {
    if (objectUrls[idx]) URL.revokeObjectURL(objectUrls[idx]);
    selectedFiles.splice(idx, 1);
    objectUrls.splice(idx, 1);
    renderPreviews();
}

function renderPreviews() {
    var preview = document.getElementById('upload-preview');
    if (!preview) return;
    preview.innerHTML = '';

    if (selectedFiles.length === 0) {
        preview.style.display = 'none';
        return;
    }

    preview.style.display = 'grid';

    selectedFiles.forEach(function(file, idx) {
        var isVideo = file.type.startsWith('video/');
        var sizeMB  = file.size / 1024 / 1024;

        // Kartu: TIDAK overflow:hidden agar tombol X (top:-8px) tidak terpotong
        var card = document.createElement('div');
        card.style.cssText = 'position:relative;border-radius:8px;border:1px solid #f3f4f6;';

        if (isVideo) {
            var vid = document.createElement('video');
            vid.src      = objectUrls[idx];
            vid.muted    = true;
            vid.preload  = 'metadata';
            vid.style.cssText = 'width:100%;min-height:200px;object-fit:cover;border-radius:8px 8px 0 0;display:block';
            vid.addEventListener('loadedmetadata', function() { this.currentTime = 0.1; });
            card.appendChild(vid);

            var play = document.createElement('div');
            play.style.cssText = 'position:absolute;top:calc(50% - 36px);left:50%;transform:translateX(-50%);width:36px;height:36px;background:rgba(255,255,255,0.8);border-radius:50%;display:flex;align-items:center;justify-content:center;pointer-events:none';
            play.innerHTML = '<div style="width:0;height:0;border-left:12px solid #f97316;border-top:8px solid transparent;border-bottom:8px solid transparent;margin-left:3px"></div>';
            card.appendChild(play);
        } else {
            var img = document.createElement('img');
            img.alt       = file.name;
            img.style.cssText = 'width:100%;min-height:200px;object-fit:cover;border-radius:8px 8px 0 0;display:block';
            card.appendChild(img);

            var reader = new FileReader();
            reader.onload = (function(i) { return function(e) { i.src = e.target.result; }; })(img);
            reader.readAsDataURL(file);
        }

        var info = document.createElement('div');
        info.className = 'px-2 py-1.5';

        var nameEl = document.createElement('p');
        nameEl.className   = 'text-xs text-gray-600 truncate';
        nameEl.textContent = file.name;

        var sizeEl = document.createElement('p');
        sizeEl.className   = 'text-xs text-gray-400 mt-0.5';
        sizeEl.textContent = (sizeMB >= 1 ? sizeMB.toFixed(1) + ' MB' : Math.ceil(file.size / 1024) + ' KB') + ' (sebelum compress)';

        info.appendChild(nameEl);
        info.appendChild(sizeEl);

        if (isVideo && file.size > 50 * 1024 * 1024) {
            var warn = document.createElement('p');
            warn.className   = 'text-xs text-yellow-600 mt-0.5';
            warn.textContent = 'File besar, upload mungkin membutuhkan waktu lebih lama';
            info.appendChild(warn);
        }

        card.appendChild(info);

        // Tombol X: keluar dari pojok gambar (top:-8px, right:-8px), 20×20px
        var xBtn = document.createElement('button');
        xBtn.type = 'button';
        xBtn.style.cssText = 'position:absolute;top:-8px;right:-8px;width:20px;height:20px;background:#ef4444;color:white;border:none;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:14px;z-index:10;line-height:1;padding:0;';
        xBtn.innerHTML = '&times;';
        xBtn.onclick = (function(i) { return function() { removeFromQueue(i); }; })(idx);
        card.appendChild(xBtn);

        preview.appendChild(card);
    });
}

// ── Upload ────────────────────────────────────────────────────────────────────
function startUpload() {
    if (selectedFiles.length === 0) {
        showAlert('warning', 'Pilih File', 'Pilih foto atau video terlebih dahulu.');
        return;
    }

    var tipe = (document.getElementById('tipe-foto-aktif') || {}).value || 'foto_real';

    if (tipe === 'video' && selectedFiles.length + totalVideo > maxVideo) {
        showAlert('error', 'Batas Terlampaui', 'Hanya bisa upload ' + Math.max(0, maxVideo - totalVideo) + ' video lagi (maks. 3 per transaksi).');
        return;
    }
    if (tipe !== 'video' && selectedFiles.length + totalFoto > maxFoto) {
        showAlert('error', 'Batas Terlampaui', 'Hanya bisa upload ' + Math.max(0, maxFoto - totalFoto) + ' foto lagi (maks. 10 per transaksi).');
        return;
    }

    var btn      = document.getElementById('upload-btn');
    var prog     = document.getElementById('upload-progress');
    var statusEl = document.getElementById('upload-status');
    var barWrap  = document.getElementById('upload-progress-bar');
    var barFill  = document.getElementById('upload-progress-fill');

    btn.disabled       = true;
    prog.style.display = '';

    var i = 0;

    function uploadNext() {
        if (i >= selectedFiles.length) {
            statusEl.textContent = 'Upload selesai. Memuat ulang halaman...';
            setTimeout(function() { window.location.reload(); }, 700);
            return;
        }

        var file    = selectedFiles[i];
        var isVideo = file.type.startsWith('video/');
        var fd      = new FormData();
        fd.append('foto', file);
        fd.append('tipe', tipe);

        if (isVideo) {
            statusEl.textContent = 'Mengupload video: ' + file.name;
            if (barWrap) barWrap.style.display = '';
            if (barFill) barFill.style.width   = '0%';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', uploadUrl);
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

            xhr.upload.onprogress = function(e) {
                if (!e.lengthComputable || !barFill) return;
                var pct = Math.round(e.loaded / e.total * 100);
                barFill.style.width = pct + '%';
                statusEl.textContent = pct < 100
                    ? 'Mengupload video: ' + pct + '% (' + file.name + ')'
                    : 'Memproses video... (mungkin beberapa menit jika file besar)';
            };

            xhr.onload = function() {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.error) {
                        btn.disabled = false;
                        prog.style.display = 'none';
                        showAlert('error', 'Gagal Upload', data.error);
                        return;
                    }
                    i++;
                    if (barFill) barFill.style.width = '0%';
                    uploadNext();
                } catch (ex) {
                    btn.disabled = false;
                    prog.style.display = 'none';
                    showAlert('error', 'Error', 'Terjadi kesalahan. Coba lagi.');
                }
            };

            xhr.onerror = function() {
                btn.disabled = false;
                prog.style.display = 'none';
                showAlert('error', 'Error Jaringan', 'Terjadi kesalahan jaringan saat upload video.');
            };

            xhr.send(fd);
        } else {
            statusEl.textContent = 'Mengupload foto ' + (i + 1) + '/' + selectedFiles.length + ': ' + file.name;
            if (barWrap) barWrap.style.display = 'none';

            fetch(uploadUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: fd,
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.error) {
                    btn.disabled = false;
                    prog.style.display = 'none';
                    showAlert('error', 'Gagal Upload', data.error);
                    return;
                }
                i++;
                uploadNext();
            })
            .catch(function() {
                btn.disabled = false;
                prog.style.display = 'none';
                showAlert('error', 'Error', 'Terjadi kesalahan saat upload foto.');
            });
        }
    }

    uploadNext();
}

// ── Hapus file yang sudah tersimpan ──────────────────────────────────────────
function hapusFile(fileId, btn) {
    if (!confirm('Yakin ingin menghapus file ini?')) return;

    fetch(hapusBase + '/' + fileId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.reload();
        } else {
            showAlert('error', 'Gagal', data.error || 'Gagal menghapus file.');
        }
    })
    .catch(function() { showAlert('error', 'Error', 'Terjadi kesalahan. Coba lagi.'); });
}

// ── Helper alert ──────────────────────────────────────────────────────────────
function showAlert(icon, title, text) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: icon, title: title, text: text, confirmButtonColor: '#f97316', confirmButtonText: 'OK' });
    } else {
        alert(title + ': ' + text);
    }
}
</script>

@endsection
