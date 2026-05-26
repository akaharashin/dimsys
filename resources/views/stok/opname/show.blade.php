@extends('layouts.app')
@section('title', 'Detail Stok Opname')

@section('content')
@php
    $user        = auth()->user();
    $bolehHapus  = $user->hasRole('admin_pusat') || $user->hasRole('koordinator');
    $fotoReal    = $stokOpname->getMedia('foto_real');
    $beritaAcara = $stokOpname->getMedia('berita_acara');
    $totalFoto   = $fotoReal->count() + $beritaAcara->count();
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

{{-- Foto Bukti --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Foto Bukti</h3>
        @if($totalFoto > 0)
        <span class="text-xs text-gray-400">{{ $totalFoto }} foto</span>
        @endif
    </div>

    @if($totalFoto === 0)
        <p class="text-sm text-gray-400 text-center py-6">Tidak ada foto bukti untuk STO ini.</p>
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
    @endif
</div>

{{-- Lightbox --}}
<div id="lightbox"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.93);z-index:9999;align-items:center;justify-content:center"
    onclick="if(event.target===this)closeLightbox()">

    <img id="lightbox-img" src="" alt=""
        style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:8px;box-shadow:0 4px 32px rgba(0,0,0,0.5)">

    <button id="lb-prev" onclick="prevItem()"
        style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:white;background:rgba(255,255,255,0.15);border:none;border-radius:50%;width:44px;height:44px;font-size:22px;cursor:pointer;line-height:1">‹</button>
    <button id="lb-next" onclick="nextItem()"
        style="position:absolute;right:1rem;top:50%;transform:translateY(-50%);color:white;background:rgba(255,255,255,0.15);border:none;border-radius:50%;width:44px;height:44px;font-size:22px;cursor:pointer;line-height:1">›</button>
    <button onclick="closeLightbox()"
        style="position:absolute;top:1rem;right:1rem;color:white;background:rgba(255,255,255,0.15);border:none;border-radius:50%;width:36px;height:36px;font-size:18px;cursor:pointer;line-height:1">×</button>
    <div id="lightbox-counter"
        style="position:absolute;bottom:1rem;left:50%;transform:translateX(-50%);color:rgba(255,255,255,0.7);font-size:13px"></div>
</div>

<script>
var fotosData = {
    foto_real:    @json($fotoReal->map(fn($f) => asset('storage/' . $f->id . '/' . $f->file_name))->values()),
    berita_acara: @json($beritaAcara->map(fn($f) => asset('storage/' . $f->id . '/' . $f->file_name))->values()),
};
var currentTab = 'foto_real';
var currentIdx = 0;
var csrfToken  = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
var hapusBase  = '{{ url("stok/opname/foto") }}';

function switchTab(tab) {
    currentTab = tab;
    ['foto_real', 'berita_acara'].forEach(function(t) {
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
}

function updateLightboxItem() {
    var urls    = fotosData[currentTab] || [];
    var imgEl   = document.getElementById('lightbox-img');
    var prevBtn = document.getElementById('lb-prev');
    var nextBtn = document.getElementById('lb-next');

    imgEl.src = urls[currentIdx] || '';
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
    if (!confirm('Yakin ingin menghapus foto ini?')) return;

    fetch(hapusBase + '/' + fileId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.error || 'Gagal menghapus foto.');
        }
    })
    .catch(function() { alert('Terjadi kesalahan. Coba lagi.'); });
}
</script>

@endsection
