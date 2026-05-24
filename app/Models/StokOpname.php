<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StokOpname extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'stok_opname';

    protected $fillable = [
        'wilayah_id',
        'tanggal',
        'keterangan',
        'status',
        'created_by',
        'deleted_by'
    ];

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }
    public function details()
    {
        return $this->hasMany(StokOpnameDetail::class, 'stok_opname_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}