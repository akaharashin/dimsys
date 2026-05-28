<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;


class StokMasuk extends Model
{
    use HasUuids, SoftDeletes;
    protected $table = 'stok_masuk';
    protected $fillable = [
        'wilayah_id', 'supplier_id', 'tanggal', 'jenis', 'keterangan',
        'stok_opname_id', 'created_by', 'updated_by', 'deleted_by',
    ];


    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function details()
    {
        return $this->hasMany(StokMasukDetail::class, 'stok_masuk_id');
    }
    public function stokOpname()
    {
        return $this->belongsTo(StokOpname::class, 'stok_opname_id');
    }
}
