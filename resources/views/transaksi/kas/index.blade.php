@extends('layouts.app')
@section('title', 'Kas Harian')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Kas Harian</h2>
        @if(!auth()->user()->hasRole('owner'))
        <a href="{{ route('transaksi.kas.create') }}"
            class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded-lg">
            + Catat Transaksi
        </a>
        @endif
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('transaksi.kas.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Rekening</label>
                <select name="rekening_id" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300"
                    style="min-width:180px">
                    @foreach($rekeningList as $r)
                        <option value="{{ $r->id }}" {{ $selectedRekening == $r->id ? 'selected' : '' }}>
                            {{ $r->nama }} ({{ ucfirst($r->tipe) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ request('dari') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ request('sampai') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Kategori</label>
                <select name="kategori"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                    <option value="">Semua</option>
                    <option value="debit" {{ request('kategori') == 'debit' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="kredit" {{ request('kategori') == 'kredit' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
            </div>
            <div class="flex-1" style="min-width:160px">
                <label class="block text-xs text-gray-500 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Keterangan, penerima..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Per Halaman</label>
                <select name="per_page"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300" style="min-width:60px">
                    @foreach([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" {{ request('per_page', 25) == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-lg">Filter</button>
                <a href="{{ route('transaksi.kas.index', ['rekening_id' => $selectedRekening]) }}"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">Reset</a>
                <a href="{{ route('transaksi.kas.export', request()->all()) }}"
                    class="px-4 py-2 text-sm bg-green-500 hover:bg-green-600 text-white rounded-lg"><i class="fa-solid fa-file-excel mr-1"></i> Export</a>
            </div>
        </form>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-green-400">
            <p class="text-xs text-gray-400 uppercase">Total Debit (Masuk)</p>
            <p class="text-xl font-bold text-green-600 mt-1">Rp {{ number_format($totalDebit) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-red-400">
            <p class="text-xs text-gray-400 uppercase">Total Kredit (Keluar)</p>
            <p class="text-xl font-bold text-red-500 mt-1">Rp {{ number_format($totalKredit) }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border-l-4 border-blue-400">
            <p class="text-xs text-gray-400 uppercase">Saldo Akhir</p>
            <p class="text-xl font-bold text-blue-600 mt-1">Rp {{ number_format($saldoAkhir) }}</p>
        </div>
    </div>

    {{-- Info --}}
    <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
        <span>Menampilkan {{ $paginated->firstItem() ?? 0 }}-{{ $paginated->lastItem() ?? 0 }} dari
            {{ $paginated->total() }} transaksi</span>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-gray-500 uppercase text-xs" style="position:sticky;top:0;background:#f9fafb;z-index:10;">
                <tr>
                    <th class="px-4 py-3 text-center w-12">No</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Kategori</th>
                    <th class="px-4 py-3 text-left">Sub Kategori</th>
                    <th class="px-4 py-3 text-left">Outlet</th>
                    <th class="px-4 py-3 text-left">Keterangan</th>
                    <th class="px-4 py-3 text-left">Penerima</th>
                    <th class="px-4 py-3 text-right">Debit</th>
                    <th class="px-4 py-3 text-right">Kredit</th>
                    <th class="px-4 py-3 text-right">Saldo</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($paginated as $k)
                    <tr class="hover:bg-gray-50 {{ $k->tipe === 'debit' ? '' : 'bg-red-50/30' }}">
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $paginated->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($k->tanggal)->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="px-2 py-1 rounded-full text-xs {{ $k->tipe === 'debit' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-500' }}">
                                {{ $k->tipe === 'debit' ? 'Pemasukan' : 'Pengeluaran' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $k->kategori ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $k->outlet?->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $k->keterangan ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $k->penerima ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium text-green-600">
                            {{ $k->tipe === 'debit' ? 'Rp ' . number_format($k->jumlah) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-red-500">
                            {{ $k->tipe === 'kredit' ? 'Rp ' . number_format($k->jumlah) : '-' }}
                        </td>
                        <td
                            class="px-4 py-3 text-right font-bold {{ $k->saldo_berjalan < 0 ? 'text-red-600' : 'text-gray-700' }}">
                            Rp {{ number_format($k->saldo_berjalan) }}
                        </td>
                        <td class="px-4 py-3">
                            @if(!auth()->user()->hasRole('owner') && \Carbon\Carbon::parse($k->tanggal)->isToday())
                            <form method="POST" action="{{ route('transaksi.kas.destroy', $k) }}"
                                data-confirm="Yakin ingin membatalkan transaksi kas ini?">
                                @csrf @method('DELETE')
                                <button
                                    class="text-xs px-3 py-1 bg-red-50 hover:bg-red-100 rounded-lg text-red-500">Batalkan</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi kas.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($kasWithSaldo->count())
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td colspan="7" class="px-4 py-3 text-gray-600">Total</td>
                        <td class="px-4 py-3 text-right text-green-600">Rp {{ number_format($totalDebit) }}</td>
                        <td class="px-4 py-3 text-right text-red-500">Rp {{ number_format($totalKredit) }}</td>
                        <td class="px-4 py-3 text-right text-blue-600">Rp {{ number_format($saldoAkhir) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    @if($paginated->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">Halaman {{ $paginated->currentPage() }} dari {{ $paginated->lastPage() }}</div>
            <div>{{ $paginated->links() }}</div>
        </div>
    @endif

@endsection