@extends('layouts.app')
@section('title', 'Detail Stok Opname')

@section('content')
@php
    $user           = auth()->user();
    $bolehHapus     = $user->hasRole('admin_pusat') || $user->hasRole('koordinator');
    $bolehKoreksi   = $user->hasRole('admin_pusat') || $user->hasRole('koordinator');
    $sudahKoreksi   = $stokOpname->sudahDikoreksi();
    $adaSelisih     = $stokOpname->details->where('selisih', '!=', 0)->count() > 0;
    $fotoReal       = $stokOpname->getMedia('foto_real');
    $beritaAcara    = $stokOpname->getMedia('berita_acara');
    $videos         = $stokOpname->getMedia('video');
    $totalFoto      = $fotoReal->count() + $beritaAcara->count();
    $totalVideo     = $videos->count();
    $totalMedia     = $totalFoto + $totalVideo;
@endphp

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('stok.opname.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali</a>
        <h2 class="text-2xl font-bold text-gray-700">Detail Stok Opname</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-400 uppercase">Tanggal</p>
                <p class="font-medium text-gray-700 mt-1">
                    {{ \Carbon\Carbon::parse($stokOpname->tanggal)->locale('id')->isoFormat('D MMMM Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase">Wilayah</p>
                <p class="font-medium text-gray-700 mt-1">{{ $stokOpname->wilayah->nama }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase">Status</p>
                <span
                    class="inline-block mt-1 px-2 py-1 rounded-full text-xs {{ $stokOpname->status === 'final' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">
                    {{ ucfirst($stokOpname->status) }}
                </span>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase">Dibuat Oleh</p>
                <p class="font-medium text-gray-700 mt-1">{{ $stokOpname->createdBy?->name ?? '-' }}</p>
            </div>
        </div>
        @if($stokOpname->keterangan)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400 uppercase">Keterangan</p>
                <p class="text-gray-600 mt-1">{{ $stokOpname->keterangan }}</p>
            </div>
        @endif
    </div>

    {{-- Status Koreksi --}}
    @if($stokOpname->status === 'final' && !$sudahKoreksi && $adaSelisih && $bolehKoreksi)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4 flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-amber-800">Koreksi belum diterapkan ke sistem</p>
                <p class="text-xs text-amber-600 mt-0.5">
                    Terapkan koreksi agar stok freezer menyesuaikan hasil opname fisik.
                </p>
            </div>
            <form method="POST" action="{{ route('stok.opname.koreksi', $stokOpname) }}"
                data-confirm="Terapkan koreksi STO ini ke stok sistem? Stok freezer akan disesuaikan dengan hasil opname fisik. Tindakan ini tidak bisa dibatalkan.">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg">
                    <i class="fa-solid fa-check-double"></i>
                    Terapkan Koreksi ke Sistem
                </button>
            </form>
        </div>
    @elseif($sudahKoreksi)
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-4 flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-green-500"></i>
            <p class="text-sm text-green-700">Koreksi sudah diterapkan ke sistem stok.</p>
        </div>
    @endif

    {{-- Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-400">
            <p class="text-xs text-gray-400 uppercase">Total Produk</p>
            <p class="text-xl font-bold text-blue-500 mt-1">{{ $stokOpname->details->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-400">
            <p class="text-xs text-gray-400 uppercase">Sesuai</p>
            <p class="text-xl font-bold text-green-500 mt-1">{{ $stokOpname->details->where('selisih', 0)->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-red-400">
            <p class="text-xs text-gray-400 uppercase">Selisih</p>
            <p class="text-xl font-bold text-red-500 mt-1">{{ $stokOpname->details->where('selisih', '!=', 0)->count() }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-amber-400">
            <p class="text-xs text-gray-400 uppercase">Nilai Selisih</p>
            <p class="text-xl font-bold {{ $stokOpname->details->sum('nilai_selisih') < 0 ? 'text-red-500' : 'text-amber-600' }} mt-1">
                Rp {{ number_format($stokOpname->details->sum('nilai_selisih')) }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto mb-4">
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
                    <th class="px-4 py-3 text-center">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($stokOpname->details as $d)
                    <tr class="hover:bg-gray-50 {{ $d->selisih < 0 ? 'bg-red-50/30' : ($d->selisih > 0 ? 'bg-green-50/30' : '') }}">
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $d->produk->nama }}</td>
                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($d->stok_sistem) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format($d->stok_fisik) }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $d->selisih < 0 ? 'text-red-500' : ($d->selisih > 0 ? 'text-green-600' : 'text-gray-400') }}">
                            {{ $d->selisih > 0 ? '+' : '' }}{{ number_format($d->selisih) }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">Rp {{ number_format($d->hpp_snapshot) }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $d->nilai_selisih < 0 ? 'text-red-500' : ($d->nilai_selisih > 0 ? 'text-green-600' : 'text-gray-400') }}">
                            Rp {{ number_format($d->nilai_selisih) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($d->selisih == 0)
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-600">Sesuai</span>
                            @elseif($d->selisih < 0)
                                <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-500">Kurang</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-600">Lebih</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 font-semibold">
                <tr>
                    <td colspan="2" class="px-4 py-3 text-gray-600">Total</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ number_format($stokOpname->details->sum('stok_sistem')) }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">{{ number_format($stokOpname->details->sum('stok_fisik')) }}</td>
                    <td class="px-4 py-3 text-right {{ $stokOpname->details->sum('selisih') < 0 ? 'text-red-500' : 'text-green-600' }}">
                        {{ $stokOpname->details->sum('selisih') > 0 ? '+' : '' }}{{ number_format($stokOpname->details->sum('selisih')) }}
                    </td>
                    <td></td>
                    <td class="px-4 py-3 text-right {{ $stokOpname->details->sum('nilai_selisih') < 0 ? 'text-red-500' : 'text-gray-700' }}">
                        Rp {{ number_format($stokOpname->details->sum('nilai_selisih')) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

{{-- Bukti Media (Foto & Video) --}}
@php
    $bolehTambah = $bolehHapus; // admin_pusat / koordinator
    $maxFoto     = 5;
    $maxVideo    = 3;
@endphp
<div class="bg-white rounded-xl shadow-sm p-6 mb-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Bukti Media</h3>
        @if($totalMedia > 0)
        <span class="text-xs text-gray-400">{{ $totalFoto }} foto · {{ $totalVideo }} video</span>
        @endif
    </div>

    @if($bolehTambah)
    {{-- Section: Tambah Media (susulan) --}}
    <div class="mb-5 p-4 rounded-lg border border-dashed border-red-200" style="background:#FDECEC">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-semibold text-gray-700">
                <i class="fa-solid fa-plus mr-1" style="color:#A51616"></i>
                Tambah Media
            </p>
            <p class="text-xs text-gray-500">
                Foto Real: {{ $fotoReal->count() }}/{{ $maxFoto }} ·
                Berita Acara: {{ $beritaAcara->count() }}/{{ $maxFoto }} ·
                Video: {{ $videos->count() }}/{{ $maxVideo }}
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Foto Real (JPG/PNG/WebP · maks 10MB)</label>
                <input type="file" id="add-input-foto_real" accept="image/jpeg,image/png,image/webp" multiple
                    {{ $fotoReal->count() >= $maxFoto ? 'disabled' : '' }}
                    onchange="handleTambahMedia(this, 'foto_real')"
                    class="w-full text-xs text-gray-600
                           file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                           file:text-xs file:font-medium file:bg-red-50 file:text-red-700 hover:file:bg-red-100
                           disabled:opacity-50">
                @if($fotoReal->count() >= $maxFoto)
                    <p class="text-xs text-red-500 mt-1">Batas 5 foto tercapai.</p>
                @endif
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Berita Acara (JPG/PNG/WebP · maks 10MB)</label>
                <input type="file" id="add-input-berita_acara" accept="image/jpeg,image/png,image/webp" multiple
                    {{ $beritaAcara->count() >= $maxFoto ? 'disabled' : '' }}
                    onchange="handleTambahMedia(this, 'berita_acara')"
                    class="w-full text-xs text-gray-600
                           file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                           file:text-xs file:font-medium file:bg-red-50 file:text-red-700 hover:file:bg-red-100
                           disabled:opacity-50">
                @if($beritaAcara->count() >= $maxFoto)
                    <p class="text-xs text-red-500 mt-1">Batas 5 foto tercapai.</p>
                @endif
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Video bukti: MP4/MOV/AVI/WebM, maks. 100 MB. Klip pendek 30-60 detik.</label>
                <input type="file" id="add-input-video" accept="video/mp4,video/quicktime,video/x-msvideo,video/webm" multiple
                    {{ $videos->count() >= $maxVideo ? 'disabled' : '' }}
                    onchange="handleTambahMedia(this, 'video')"
                    class="w-full text-xs text-gray-600
                           file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                           file:text-xs file:font-medium file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100
                           disabled:opacity-50">
                @if($videos->count() >= $maxVideo)
                    <p class="text-xs text-red-500 mt-1">Batas 3 video tercapai.</p>
                @endif
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-2">
            <i class="fa-solid fa-circle-info"></i>
            File yang dipilih akan langsung diupload. Tunggu sampai proses selesai.
        </p>
    </div>
    @endif

    @if($totalMedia === 0)
        <p class="text-sm text-gray-400 text-center py-6">Belum ada bukti media untuk STO ini.</p>
    @else
    {{-- Tab buttons --}}
    <div class="flex gap-0 mb-4 border-b border-gray-100">
        <button onclick="switchTab('foto_real')" id="tab-foto_real"
            class="px-4 py-2 text-sm font-medium border-b-2 border-red-600 text-red-600 -mb-px">
            Foto Real ({{ $fotoReal->count() }})
        </button>
        <button onclick="switchTab('berita_acara')" id="tab-berita_acara"
            class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-400 -mb-px">
            Berita Acara ({{ $beritaAcara->count() }})
        </button>
        <button onclick="switchTab('video')" id="tab-video"
            class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-400 -mb-px">
            <i class="fa-solid fa-video mr-1"></i>
            Video ({{ $videos->count() }})
        </button>
    </div>

    {{-- Panel: Foto Real --}}
    <div id="panel-foto_real">
        @if($fotoReal->isEmpty())
            <p class="text-sm text-gray-400 py-4 text-center">Tidak ada foto real.</p>
        @else
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($fotoReal as $i => $foto)
            <div class="relative group rounded-lg border border-gray-100">
                <img src="{{ asset('storage/' . $foto->id . '/' . $foto->file_name) }}" alt="{{ $foto->file_name }}"
                    class="w-full h-36 object-cover rounded-t-lg cursor-pointer hover:opacity-90 transition"
                    onclick="openLightbox('foto_real', {{ $i }})">
                <div class="px-2 py-1.5">
                    <p class="text-xs text-gray-500 truncate">{{ $foto->file_name }}</p>
                    <p class="text-xs text-gray-400">{{ (int) ceil($foto->size / 1024) }} KB · {{ \Carbon\Carbon::parse($foto->created_at)->format('d/m/y') }}</p>
                </div>
                @if($bolehHapus)
                <button onclick="hapusFile({{ $foto->id }}, this)"
                    aria-label="Hapus"
                    class="absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center
                           text-white text-sm leading-none shadow-md z-10
                           opacity-0 group-hover:opacity-100 transition-opacity"
                    style="background-color:#A51616"
                    onmouseover="this.style.backgroundColor='#7c1010'"
                    onmouseout="this.style.backgroundColor='#A51616'">
                    <i class="fa-solid fa-xmark"></i>
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
            <p class="text-sm text-gray-400 py-4 text-center">Tidak ada foto berita acara.</p>
        @else
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($beritaAcara as $i => $foto)
            <div class="relative group rounded-lg border border-gray-100">
                <img src="{{ asset('storage/' . $foto->id . '/' . $foto->file_name) }}" alt="{{ $foto->file_name }}"
                    class="w-full h-36 object-cover rounded-t-lg cursor-pointer hover:opacity-90 transition"
                    onclick="openLightbox('berita_acara', {{ $i }})">
                <div class="px-2 py-1.5">
                    <p class="text-xs text-gray-500 truncate">{{ $foto->file_name }}</p>
                    <p class="text-xs text-gray-400">{{ (int) ceil($foto->size / 1024) }} KB · {{ \Carbon\Carbon::parse($foto->created_at)->format('d/m/y') }}</p>
                </div>
                @if($bolehHapus)
                <button onclick="hapusFile({{ $foto->id }}, this)"
                    aria-label="Hapus"
                    class="absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center
                           text-white text-sm leading-none shadow-md z-10
                           opacity-0 group-hover:opacity-100 transition-opacity"
                    style="background-color:#A51616"
                    onmouseover="this.style.backgroundColor='#7c1010'"
                    onmouseout="this.style.backgroundColor='#A51616'">
                    <i class="fa-solid fa-xmark"></i>
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
            <p class="text-sm text-gray-400 py-4 text-center">Tidak ada video bukti.</p>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($videos as $i => $vid)
            <div class="relative group rounded-lg border border-gray-100">
                <video class="w-full h-48 object-cover rounded-t-lg bg-black cursor-pointer" preload="metadata" muted
                    onclick="openLightbox('video', {{ $i }})">
                    <source src="{{ asset('storage/' . $vid->id . '/' . $vid->file_name) }}" type="video/mp4">
                </video>
                <div class="px-2 py-1.5">
                    <p class="text-xs text-gray-500 truncate">{{ $vid->file_name }}</p>
                    <p class="text-xs text-gray-400">
                        {{ (int) ceil($vid->size / 1024) }} KB
                        @if(($vid->getCustomProperty('durasi')) > 0)
                            · {{ gmdate('i:s', $vid->getCustomProperty('durasi')) }}
                        @endif
                        · {{ \Carbon\Carbon::parse($vid->created_at)->format('d/m/y') }}
                    </p>
                </div>
                @if($bolehHapus)
                <button onclick="hapusFile({{ $vid->id }}, this)"
                    aria-label="Hapus"
                    class="absolute top-2 right-2 w-7 h-7 rounded-full flex items-center justify-center
                           text-white text-sm leading-none shadow-md z-10
                           opacity-0 group-hover:opacity-100 transition-opacity"
                    style="background-color:#A51616"
                    onmouseover="this.style.backgroundColor='#7c1010'"
                    onmouseout="this.style.backgroundColor='#A51616'">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                @endif
            </div>
            @endforeach
        </div>
        @endif
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
        style="display:none;max-width:90vw;max-height:90vh;border-radius:8px;box-shadow:0 4px 32px rgba(0,0,0,0.5);background:#000">
    </video>

    <button id="lb-prev" onclick="prevItem()"
        style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:white;background:rgba(255,255,255,0.15);border:none;border-radius:50%;width:44px;height:44px;font-size:22px;cursor:pointer;line-height:1">‹</button>
    <button id="lb-next" onclick="nextItem()"
        style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);color:white;background:rgba(255,255,255,0.15);border:none;border-radius:50%;width:44px;height:44px;font-size:22px;cursor:pointer;line-height:1">›</button>
    <button onclick="closeLightbox()"
        style="position:absolute;top:1rem;right:1rem;color:white;background:rgba(255,255,255,0.15);border:none;border-radius:50%;width:36px;height:36px;font-size:18px;cursor:pointer;line-height:1">×</button>
    <div id="lightbox-counter"
        style="position:absolute;bottom:1rem;left:50%;transform:translateX(-50%);color:rgba(255,255,255,0.7);font-size:13px"></div>
</div>

@if($bolehTambah)
{{-- Overlay loading saat upload media tambahan --}}
<div id="upload-overlay" aria-hidden="true"
    style="display:none;position:fixed;inset:0;background:rgba(15,15,15,0.78);z-index:9998;align-items:center;justify-content:center;backdrop-filter:blur(2px);">
    <div style="background:#fff;border-radius:16px;padding:28px 32px;max-width:480px;width:90%;box-shadow:0 12px 40px rgba(0,0,0,0.3);text-align:center;"
         onclick="event.stopPropagation()">
        <div style="margin-bottom:16px">
            <i id="ovl-icon" class="fa-solid fa-spinner fa-spin" style="font-size:36px;color:#A51616"></i>
        </div>
        <h3 id="ovl-title" style="font-size:16px;font-weight:600;color:#374151;margin-bottom:6px">Mengupload media...</h3>
        <p id="ovl-status" style="font-size:13px;color:#6b7280;margin-bottom:18px">Mempersiapkan upload...</p>

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
@endif

<script>
var fotosData = {
    foto_real:    @json($fotoReal->map(fn($f) => asset('storage/' . $f->id . '/' . $f->file_name))->values()),
    berita_acara: @json($beritaAcara->map(fn($f) => asset('storage/' . $f->id . '/' . $f->file_name))->values()),
    video:        @json($videos->map(fn($v) => asset('storage/' . $v->id . '/' . $v->file_name))->values()),
};
var currentTab = 'foto_real';
var currentIdx = 0;
var csrfToken  = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
var hapusBase  = '{{ url("stok/opname/foto") }}';

function switchTab(tab) {
    currentTab = tab;
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
}

function openLightbox(tab, idx) {
    currentTab = tab;
    currentIdx = idx;
    updateLightboxItem();
    document.getElementById('lightbox').style.display = 'flex';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    var vid = document.getElementById('lightbox-video');
    if (vid) { vid.pause(); vid.src = ''; }
}

function updateLightboxItem() {
    var urls    = fotosData[currentTab] || [];
    var imgEl   = document.getElementById('lightbox-img');
    var vidEl   = document.getElementById('lightbox-video');
    var prevBtn = document.getElementById('lb-prev');
    var nextBtn = document.getElementById('lb-next');

    if (currentTab === 'video') {
        imgEl.style.display = 'none';
        vidEl.style.display = '';
        vidEl.src = urls[currentIdx] || '';
    } else {
        if (vidEl) { vidEl.pause(); vidEl.src = ''; vidEl.style.display = 'none'; }
        imgEl.style.display = '';
        imgEl.src = urls[currentIdx] || '';
    }
    document.getElementById('lightbox-counter').textContent = (currentIdx + 1) + ' / ' + urls.length;
    var showNav = urls.length > 1;
    prevBtn.style.display = showNav ? '' : 'none';
    nextBtn.style.display = showNav ? '' : 'none';
}

function prevItem() {
    var urls = fotosData[currentTab] || [];
    currentIdx = (currentIdx - 1 + urls.length) % urls.length;
    updateLightboxItem();
}

function nextItem() {
    var urls = fotosData[currentTab] || [];
    currentIdx = (currentIdx + 1) % urls.length;
    updateLightboxItem();
}

document.addEventListener('keydown', function(e) {
    var lb = document.getElementById('lightbox');
    if (!lb || lb.style.display === 'none') return;
    if (e.key === 'Escape')     closeLightbox();
    if (e.key === 'ArrowLeft')  prevItem();
    if (e.key === 'ArrowRight') nextItem();
});

function hapusFile(fileId) {
    var doDelete = function() {
        fetch(hapusBase + '/' + fileId, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.reload();
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Gagal Menghapus', text: data.error || 'Gagal menghapus file.', confirmButtonColor: '#A51616' });
            } else {
                alert(data.error || 'Gagal menghapus file.');
            }
        })
        .catch(function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error Jaringan', text: 'Terjadi kesalahan. Coba lagi.', confirmButtonColor: '#A51616' });
            } else {
                alert('Terjadi kesalahan. Coba lagi.');
            }
        });
    };

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Hapus File?',
            text: 'File bukti ini akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#A51616',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(function(result) {
            if (result.isConfirmed) doDelete();
        });
    } else if (confirm('Yakin ingin menghapus file ini?')) {
        doDelete();
    }
}

@if($bolehTambah)
// ── Tambah Media: pola overlay + XHR + onprogress (mirror create.blade.php) ─────
var addUploadUrl = '{{ route("stok.opname.foto.upload", $stokOpname->id) }}';
var MAX_VIDEO_BYTES = 100 * 1024 * 1024; // 100 MB — konsisten dengan validasi server

function showOverlay() {
    var ovl = document.getElementById('upload-overlay');
    if (ovl) { ovl.style.display = 'flex'; ovl.setAttribute('aria-hidden', 'false'); }
    document.body.style.overflow = 'hidden';
    window.addEventListener('beforeunload', beforeUnloadHandler);
}
function hideOverlay() {
    var ovl = document.getElementById('upload-overlay');
    if (ovl) { ovl.style.display = 'none'; ovl.setAttribute('aria-hidden', 'true'); }
    document.body.style.overflow = '';
    window.removeEventListener('beforeunload', beforeUnloadHandler);
}
function beforeUnloadHandler(e) {
    e.preventDefault();
    e.returnValue = 'Upload sedang berlangsung. Yakin ingin meninggalkan halaman?';
    return e.returnValue;
}
// Helper: remove beforeunload sebelum reload — cegah dialog "Leave site?" saat sukses
function safeReload() {
    window.removeEventListener('beforeunload', beforeUnloadHandler);
    window.location.reload();
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
        fill.style.width = '0%';
        pct.textContent = '0%';
    }
    if (opts.icon) {
        var iconEl = document.getElementById('ovl-icon');
        iconEl.className = opts.icon;
        iconEl.style.color = opts.iconColor || '#A51616';
    }
}

function uploadOneFile(item, idx, total) {
    return new Promise(function(resolve, reject) {
        var fd = new FormData();
        fd.append('foto', item.file);
        fd.append('tipe', item.tipe);

        var isVideo  = item.tipe === 'video';
        var fileName = item.file.name || ((isVideo ? 'video' : 'foto') + ' #' + (idx + 1));
        var prefix   = isVideo ? 'Mengupload video' : 'Mengupload foto';

        setOverlay({
            title:   'Mengupload media tambahan...',
            status:  prefix + ' ' + (idx + 1) + '/' + total + ': ' + fileName,
            counter: 'Media ' + (idx + 1) + ' dari ' + total,
            progress: 0,
        });

        var xhr = new XMLHttpRequest();
        xhr.open('POST', addUploadUrl, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.onprogress = function(e) {
            if (!e.lengthComputable) return;
            var p = Math.round(e.loaded / e.total * 100);
            if (p < 100) {
                setOverlay({ status: prefix + ' ' + p + '% (' + fileName + ')', progress: p });
            } else {
                setOverlay({
                    status: 'Menyimpan media ke server...',
                    progress: 100,
                });
            }
        };

        xhr.onload = function() {
            var data = null;
            try { data = JSON.parse(xhr.responseText || '{}'); } catch (e) { data = null; }
            if (xhr.status >= 200 && xhr.status < 300 && data && data.success) {
                resolve(data); return;
            }
            var msg = (data && data.error) || ('Upload gagal (HTTP ' + xhr.status + ').');
            if (xhr.status === 422 && data && data.errors) msg = Object.values(data.errors).flat().join('\n');
            reject({ status: xhr.status, message: msg, file: fileName });
        };
        xhr.onerror   = function() { reject({ status: 0, message: 'Koneksi jaringan terputus saat upload.', file: fileName }); };
        xhr.ontimeout = function() { reject({ status: 0, message: 'Upload timeout. File mungkin terlalu besar.', file: fileName }); };
        xhr.timeout   = 600000;
        xhr.send(fd);
    });
}

function showAddAlert(icon, title, text) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: icon, title: title, text: text, confirmButtonColor: '#A51616', confirmButtonText: 'OK' });
    } else {
        alert(title + ': ' + text);
    }
}

function uploadFilesSequential(files, tipe) {
    if (!files || files.length === 0) return;
    var items = Array.from(files).map(function(f) { return { file: f, tipe: tipe }; });

    showOverlay();
    setOverlay({
        icon: 'fa-solid fa-spinner fa-spin', iconColor: '#A51616',
        title: 'Mengupload media tambahan...',
        status: 'Mempersiapkan upload...',
        hideProgress: true,
    });

    var idx = 0, sukses = 0, gagal = [];

    function next() {
        if (idx >= items.length) {
            if (sukses === items.length) {
                setOverlay({
                    icon: 'fa-solid fa-circle-check', iconColor: '#16a34a',
                    title: 'Selesai!',
                    status: 'Semua media tersimpan. Memuat ulang halaman...',
                    hideProgress: true, counter: '',
                });
                try { sessionStorage.setItem('flash_success', sukses + ' media berhasil ditambahkan.'); } catch (e) {}
                setTimeout(function() { safeReload(); }, 700);
            } else if (sukses > 0) {
                hideOverlay();
                var daftar = gagal.map(function(g) { return '• ' + g.file; }).join('\n');
                showAddAlert('warning', 'Sebagian Media Gagal',
                    sukses + ' dari ' + items.length + ' media berhasil. Yang gagal:\n\n' + daftar +
                    '\n\nAnda bisa coba upload ulang.');
                try { sessionStorage.setItem('flash_success', sukses + ' media berhasil ditambahkan.'); } catch (e) {}
                setTimeout(function() { safeReload(); }, 1500);
            } else {
                hideOverlay();
                var daftar2 = gagal.map(function(g) { return '• ' + g.file + ' — ' + g.message; }).join('\n');
                showAddAlert('error', 'Upload Gagal', 'Tidak ada media yang berhasil:\n\n' + daftar2);
            }
            return;
        }
        uploadOneFile(items[idx], idx, items.length)
            .then(function() { sukses++; idx++; next(); })
            .catch(function(err) { gagal.push(err); idx++; next(); });
    }
    next();
}

// Handler global: dipanggil dari inline onchange — lebih robust dari attach listener
// (tidak tergantung urutan eksekusi script / DOMContentLoaded).
function handleTambahMedia(input, tipe) {
    // PENTING: snapshot files SEBELUM reset value.
    // Spec HTML: set input.value = '' WAJIB mengosongkan FileList input.
    // Tanpa Array.from snapshot, files akan jadi length=0 saat uploadFilesSequential dibaca.
    var files = Array.from(input.files);
    input.value = ''; // reset agar pilih file sama bisa trigger event lagi
    if (files.length === 0) return;

    // Pre-check ukuran video — tolak SEBELUM upload (hemat bandwidth & waktu)
    if (tipe === 'video') {
        var ditolakBesar = files.filter(function(f) { return f.size > MAX_VIDEO_BYTES; });
        if (ditolakBesar.length > 0) {
            var daftar = ditolakBesar.map(function(f) {
                return '"' + f.name + '" (' + (f.size / 1024 / 1024).toFixed(1) + ' MB)';
            }).join(', ');
            showAddAlert('error', 'Video Terlalu Besar',
                daftar + ' melebihi batas 100 MB. Silakan pilih/potong video lebih kecil.');
            files = files.filter(function(f) { return f.size <= MAX_VIDEO_BYTES; });
            if (files.length === 0) return;
        }
    }

    uploadFilesSequential(files, tipe);
}

// Backup listener (selain inline onchange) — defensive, jaga-jaga kalau inline tidak fire.
document.addEventListener('DOMContentLoaded', function() {
    ['foto_real', 'berita_acara', 'video'].forEach(function(tipe) {
        var input = document.getElementById('add-input-' + tipe);
        if (!input || input.dataset.bound === '1') return;
        input.dataset.bound = '1';
        input.addEventListener('change', function() {
            handleTambahMedia(this, tipe);
        });
    });
});
@endif
</script>

@endsection
