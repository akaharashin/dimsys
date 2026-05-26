@extends('layouts.app')
@section('title', 'Activity Log')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-700">Activity Log</h2>
        <span class="text-sm text-gray-400">{{ $logs->total() }} entri</span>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <form method="GET" action="{{ route('admin.activity-log') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Dari</label>
                <input type="date" name="dari" value="{{ request('dari') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Sampai</label>
                <input type="date" name="sampai" value="{{ request('sampai') }}"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">User</label>
                <select name="user_id"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                    <option value="">Semua User</option>
                    @foreach($userList as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Modul</label>
                <select name="module"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" style="min-width:130px;">
                    <option value="">Semua Modul</option>
                    @foreach($moduleList as $m)
                        <option value="{{ $m }}" {{ request('module') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Aksi</label>
                <select name="action"
                    class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" style="min-width:120px;">
                    <option value="">Semua Aksi</option>
                    @foreach(['create','update','delete','restore','login','logout','approve','reject','upload'] as $act)
                        <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1" style="min-width:180px">
                <label class="block text-xs text-gray-500 mb-1">Cari Record</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama record..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 text-sm bg-red-700 hover:bg-red-800 text-white rounded-lg">Filter</button>
                <a href="{{ route('admin.activity-log') }}"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg">Reset</a>
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mb-3 text-sm text-gray-500">
        <span>Menampilkan {{ $logs->firstItem() ?? 0 }}&ndash;{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} entri</span>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-gray-500 uppercase text-xs" style="position:sticky;top:0;background:#f9fafb;z-index:10;">
                <tr>
                    <th class="px-4 py-3 text-left whitespace-nowrap">Waktu</th>
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                    <th class="px-4 py-3 text-left">Modul</th>
                    <th class="px-4 py-3 text-left">Record</th>
                    <th class="px-4 py-3 text-center w-16">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs">
                            {{ $log->created_at->format('d M Y') }}<br>
                            <span class="text-gray-400">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-700 text-xs">{{ $log->user_name ?? '-' }}</div>
                            @php
                                $roleBadge = match($log->user_role) {
                                    'admin_pusat' => 'bg-red-100 text-red-700',
                                    'koordinator' => 'bg-blue-100 text-blue-700',
                                    'owner'       => 'bg-purple-100 text-purple-700',
                                    default       => 'bg-gray-100 text-gray-600',
                                };
                                $roleLabel = ucwords(str_replace('_', ' ', $log->user_role ?? ''));
                            @endphp
                            <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-xs {{ $roleBadge }}">{{ $roleLabel }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $actionBadge = match($log->action) {
                                    'create'  => 'bg-green-100 text-green-700',
                                    'update'  => 'bg-blue-100 text-blue-700',
                                    'delete'  => 'bg-red-100 text-red-700',
                                    'restore' => 'bg-teal-100 text-teal-700',
                                    'approve' => 'bg-emerald-100 text-emerald-700',
                                    'reject'  => 'bg-rose-100 text-rose-700',
                                    'upload'  => 'bg-purple-100 text-purple-700',
                                    'login'   => 'bg-gray-100 text-gray-600',
                                    'logout'  => 'bg-gray-100 text-gray-500',
                                    default   => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $actionBadge }}">
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $log->module }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs max-w-xs truncate" title="{{ $log->record_label }}">
                            {{ $log->record_label ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($log->before || $log->after)
                                <button type="button"
                                    class="btn-detail inline-flex items-center justify-center w-7 h-7 bg-gray-50 hover:bg-gray-100 rounded text-gray-500 hover:text-gray-700"
                                    data-id="{{ $log->id }}">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </button>
                                @php
                                    $logData = json_encode([
                                        'action' => $log->action,
                                        'module' => $log->module,
                                        'label'  => $log->record_label ?? '-',
                                        'before' => $log->before ? json_decode(json_encode($log->before)) : null,
                                        'after'  => $log->after  ? json_decode(json_encode($log->after))  : null,
                                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                                @endphp
                                <script type="application/json" id="log-data-{{ $log->id }}">{!! $logData !!}</script>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            @if(request()->anyFilled(['dari','sampai','user_id','module','action','search']))
                                Tidak ada log yang cocok dengan filter yang dipilih.
                            @else
                                Belum ada activity log.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</div>
            <div>{{ $logs->links() }}</div>
        </div>
    @endif

    {{-- Modal Detail --}}
    <div id="modal-detail"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);align-items:center;justify-content:center;z-index:9999;">
        <div style="background:white;border-radius:12px;width:100%;max-width:700px;max-height:85vh;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.25);display:flex;flex-direction:column;">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
                <div>
                    <h3 class="text-base font-semibold text-gray-700">Detail Perubahan</h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <span id="modal-action" class="font-medium text-gray-600"></span>
                        &mdash;
                        <span id="modal-label"></span>
                    </p>
                </div>
                <button type="button" id="modal-close"
                    class="text-gray-400 hover:text-gray-600 text-2xl leading-none px-1">&times;</button>
            </div>
            <div class="overflow-y-auto flex-1 p-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-2">Sebelum</p>
                        <pre id="modal-before"
                            class="bg-red-50 border border-red-200 text-red-800 text-xs rounded-lg p-3 overflow-x-auto whitespace-pre-wrap break-words min-h-16">(tidak ada data)</pre>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-2">Sesudah</p>
                        <pre id="modal-after"
                            class="bg-green-50 border border-green-200 text-green-800 text-xs rounded-lg p-3 overflow-x-auto whitespace-pre-wrap break-words min-h-16">(tidak ada data)</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    var modal = document.getElementById('modal-detail');

    function openModal() { modal.style.display = 'flex'; }
    function closeModal() { modal.style.display = 'none'; }

    document.querySelectorAll('.btn-detail').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.dataset.id;
            var scriptEl = document.getElementById('log-data-' + id);
            if (!scriptEl) return;

            var data;
            try {
                data = JSON.parse(scriptEl.textContent);
            } catch (e) {
                console.error('Gagal parse data log:', e);
                return;
            }

            document.getElementById('modal-action').textContent = data.action + ' · ' + data.module;
            document.getElementById('modal-label').textContent  = data.label || '-';
            document.getElementById('modal-before').textContent = data.before
                ? JSON.stringify(data.before, null, 2)
                : '(tidak ada data)';
            document.getElementById('modal-after').textContent  = data.after
                ? JSON.stringify(data.after,  null, 2)
                : '(tidak ada data)';

            openModal();
        });
    });

    document.getElementById('modal-close').addEventListener('click', closeModal);

    modal.addEventListener('click', function (e) {
        if (e.target === this) closeModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal();
    });

});
</script>
@endpush
