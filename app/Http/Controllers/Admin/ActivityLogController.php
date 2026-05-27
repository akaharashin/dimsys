<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $sort = in_array($request->sort, ['created_at', 'action', 'module']) ? $request->sort : 'created_at';
        $dir  = $request->direction === 'asc' ? 'asc' : 'desc';

        $query = ActivityLog::with('user')->orderBy($sort, $dir);

        if ($request->filled('dari')) {
            $query->whereDate('created_at', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('created_at', '<=', $request->sampai);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('search')) {
            $query->where('record_label', 'like', '%' . $request->search . '%');
        }

        $logs = $query->paginate(50)->withQueryString();

        $userList   = User::orderBy('name')->get();
        $moduleList = ActivityLog::distinct()->orderBy('module')->pluck('module');

        return view('admin.activity-log.index', compact('logs', 'userList', 'moduleList'));
    }
}
