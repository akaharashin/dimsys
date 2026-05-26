@extends('layouts.app')
@section('title', 'Generate Stok Awal')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('stok.masuk.index') }}" class="text-gray-400 hover:text-gray-600 text-sm">← Kembali</a>
    <h2 class="text-2xl font-bold text-gray-700">Generate Stok Awal</h2>
</div>

@if(session('error'))
    <div class="mb-4 px-4 py-3 bg-red-100 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 text-sm text-blue-700">
    <strong>Cara Penggunaan:</strong> Pilih wilayah dan bulan yang sudah selesai. Sistem menghitung stok akhir bulan tersebut dan menjadikannya stok awal bulan berikutnya secara otomatis.
</div>

{{-- Form Parameter --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-4">
    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Parameter Generate</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

        @if(auth()->user()->hasRole('koordinator'))
            <div>
                <label class="block text-sm text-gray-600 mb-1">Wilayah</label>
                <input type="text" value="{{ auth()->user()->wilayah->nama ?? '-' }}" readonly
                    class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-500">
                <input type="hidden" id="wilayah_id" value="{{ auth()->user()->wilayah_id }}">
            </div>
        @else
            <div>
                <label class="block text-sm text-gray-600 mb-1">Wilayah</label>
                <select id="wilayah_id"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    <option value="">-- Pilih Wilayah --</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ $w->id }}">{{ $w->nama }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="block text-sm text-gray-600 mb-1">Bulan Sumber <span class="text-gray-400">(stok akhir bulan ini yang dihitung)</span></label>
            <input type="month" id="bulan" value="{{ $defaultBulan }}"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
        </div>

        <div>
            <button type="button" onclick="loadPreview()"
                class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium">
                <i class="fa-solid fa-eye mr-1"></i> Preview Stok
            </button>
        </div>
    </div>
</div>

{{-- Preview Area --}}
<div id="preview-area" style="display:none">
    <div class="bg-white rounded-xl shadow-sm p-4 mb-2 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">
                Stok akhir <strong id="lbl-bulan">-</strong> → akan jadi stok awal <strong id="lbl-bulan-tujuan" style="color:#A51616">-</strong>
                <span class="ml-2 text-gray-400" id="lbl-wilayah"></span>
            </p>
        </div>
        <span id="lbl-jumlah-produk" class="text-xs text-gray-400"></span>
    </div>

    {{-- Warning: sudah ada --}}
    <div id="warning-exists" style="display:none"
        class="mb-4 px-4 py-3 bg-yellow-50 border border-yellow-200 text-yellow-700 rounded-lg text-sm">
        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
        Stok awal <strong id="lbl-exists-bulan"></strong> untuk wilayah ini <strong>sudah ada</strong>. Generate akan dibatalkan.
    </div>

    {{-- Tabel Preview --}}
    <div class="bg-white rounded-xl shadow-sm overflow-x-auto mb-4">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-center w-10">No</th>
                    <th class="px-4 py-3 text-left">Produk</th>
                    <th class="px-4 py-3 text-right">Stok Awal</th>
                    <th class="px-4 py-3 text-right">Masuk</th>
                    <th class="px-4 py-3 text-right">OUT</th>
                    <th class="px-4 py-3 text-right">Stok Akhir <span id="th-bulan" class="normal-case font-normal"></span></th>
                    <th class="px-4 py-3 text-right text-green-600">→ Stok Awal <span id="th-bulan-tujuan" class="normal-case font-normal"></span></th>
                </tr>
            </thead>
            <tbody id="preview-table-body" class="divide-y divide-gray-100">
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">Klik "Preview Stok" untuk melihat data.</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Form Generate --}}
    <form id="form-generate" method="POST" action="{{ route('stok.generate-awal.store') }}">
        @csrf
        <input type="hidden" id="form-wilayah-id" name="wilayah_id" value="">
        <input type="hidden" id="form-bulan" name="bulan" value="">
        <div class="flex justify-end">
            <button type="button" id="btn-generate" onclick="confirmGenerate()" style="display:none"
                class="px-6 py-2.5 bg-red-700 hover:bg-red-800 text-white rounded-lg text-sm font-medium">
                <i class="fa-solid fa-rotate mr-1"></i> Generate Stok Awal <span id="btn-bulan-label"></span>
            </button>
        </div>
    </form>
</div>

<script>
    var previewUrl = "{{ route('stok.generate-awal.preview') }}";

    function loadPreview() {
        var wilayahId = document.getElementById('wilayah_id').value;
        var bulan = document.getElementById('bulan').value;

        if (!wilayahId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Pilih wilayah terlebih dahulu.', confirmButtonColor: '#A51616' });
            } else { alert('Pilih wilayah terlebih dahulu.'); }
            return;
        }
        if (!bulan) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Pilih bulan terlebih dahulu.', confirmButtonColor: '#A51616' });
            } else { alert('Pilih bulan terlebih dahulu.'); }
            return;
        }

        // Show area + loading
        document.getElementById('preview-area').style.display = '';
        document.getElementById('preview-table-body').innerHTML =
            '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-400"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>';
        document.getElementById('btn-generate').style.display = 'none';
        document.getElementById('warning-exists').style.display = 'none';

        fetch(previewUrl + '?wilayah_id=' + wilayahId + '&bulan=' + bulan)
            .then(function(r) { return r.json(); })
            .then(function(res) { renderPreview(res); })
            .catch(function() {
                document.getElementById('preview-table-body').innerHTML =
                    '<tr><td colspan="7" class="px-4 py-8 text-center text-red-500">Gagal memuat data. Silakan coba lagi.</td></tr>';
            });
    }

    function renderPreview(res) {
        document.getElementById('lbl-bulan').textContent = res.bulan_label;
        document.getElementById('lbl-bulan-tujuan').textContent = res.bulan_tujuan_label;
        document.getElementById('lbl-wilayah').textContent = '— ' + res.wilayah_nama;
        document.getElementById('th-bulan').textContent = '(' + res.bulan_label + ')';
        document.getElementById('th-bulan-tujuan').textContent = '(' + res.bulan_tujuan_label + ')';

        // Set form hidden fields
        document.getElementById('form-wilayah-id').value = res.wilayah_id;
        document.getElementById('form-bulan').value = res.bulan;

        var rows = '';
        if (res.data.length === 0) {
            rows = '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Tidak ada produk dengan stok akhir > 0 pada bulan ini.</td></tr>';
        } else {
            res.data.forEach(function(r, i) {
                rows += '<tr class="hover:bg-gray-50">' +
                    '<td class="px-4 py-3 text-center text-gray-400 text-xs">' + (i + 1) + '</td>' +
                    '<td class="px-4 py-3 font-medium text-gray-700">' + r.produk_nama + '</td>' +
                    '<td class="px-4 py-3 text-right text-gray-500">' + r.stok_awal.toLocaleString('id') + '</td>' +
                    '<td class="px-4 py-3 text-right text-gray-500">' + r.masuk.toLocaleString('id') + '</td>' +
                    '<td class="px-4 py-3 text-right text-gray-500">' + r.out.toLocaleString('id') + '</td>' +
                    '<td class="px-4 py-3 text-right font-medium text-gray-700">' + r.stok_akhir.toLocaleString('id') + '</td>' +
                    '<td class="px-4 py-3 text-right font-bold text-green-600">' + r.stok_akhir.toLocaleString('id') + '</td>' +
                '</tr>';
            });
            document.getElementById('lbl-jumlah-produk').textContent = res.data.length + ' produk';
        }

        document.getElementById('preview-table-body').innerHTML = rows;

        if (res.already_exists) {
            document.getElementById('warning-exists').style.display = '';
            document.getElementById('lbl-exists-bulan').textContent = res.bulan_tujuan_label;
            document.getElementById('btn-generate').style.display = 'none';
        } else if (res.data.length > 0) {
            document.getElementById('warning-exists').style.display = 'none';
            document.getElementById('btn-generate').style.display = '';
            document.getElementById('btn-bulan-label').textContent = res.bulan_tujuan_label;
        }
    }

    function confirmGenerate() {
        var label = document.getElementById('lbl-bulan-tujuan').textContent;
        var wilayah = document.getElementById('lbl-wilayah').textContent.replace('— ', '');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'question',
                title: 'Konfirmasi Generate',
                html: 'Yakin ingin men-generate stok awal <strong>' + label + '</strong> untuk wilayah <strong>' + wilayah + '</strong>?<br><br>Proses ini tidak dapat dibatalkan.',
                showCancelButton: true,
                confirmButtonColor: '#A51616',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Generate!',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    document.getElementById('form-generate').submit();
                }
            });
        } else {
            if (confirm('Yakin ingin men-generate stok awal ' + label + ' untuk ' + wilayah + '?')) {
                document.getElementById('form-generate').submit();
            }
        }
    }
</script>

@endsection
