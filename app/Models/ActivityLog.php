<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'user_name', 'user_role', 'action', 'module',
        'record_id', 'record_label', 'before', 'after',
        'ip_address', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'before'     => 'array',
        'after'      => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByDateRange($query, ?string $dari, ?string $sampai)
    {
        if ($dari)   $query->whereDate('created_at', '>=', $dari);
        if ($sampai) $query->whereDate('created_at', '<=', $sampai);
        return $query;
    }

    public static function log(
        string  $action,
        string  $module,
        ?string $record_id    = null,
        ?string $record_label = null,
        ?array  $before       = null,
        ?array  $after        = null
    ): void {
        try {
            static::create([
                'user_id'      => auth()->id(),
                'user_name'    => auth()->check() ? auth()->user()->name : 'System',
                'user_role'    => auth()->check() ? (auth()->user()->getRoleNames()->first() ?? '-') : '-',
                'action'       => $action,
                'module'       => $module,
                'record_id'    => $record_id,
                'record_label' => $record_label,
                'before'       => $before,
                'after'        => $after,
                'ip_address'   => request()->ip(),
                'user_agent'   => request()->userAgent(),
                'created_at'   => now(),
            ]);
        } catch (\Exception $e) {
            // Logging tidak boleh mengganggu aplikasi
        }
    }
}
