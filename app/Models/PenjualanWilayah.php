<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenjualanWilayah extends Model
{
    use HasUuids;
    use SoftDeletes;
    protected $table = 'penjualan_wilayah';
    protected $fillable = ['tipe', 'wilayah_asal_id', 'wilayah_tujuan_id', 'tanggal', 'total', 'status_bayar', 'keterangan', 'transfer_stok_masuk_id', 'created_by', 'deleted_by'];

    public function wilayahAsal()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_asal_id');
    }
    public function wilayahTujuan()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_tujuan_id');
    }
    public function details()
    {
        return $this->hasMany(PenjualanWilayahDetail::class, 'penjualan_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}