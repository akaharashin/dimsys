<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaporanHarian extends Model
{
    use HasUuids;
    use SoftDeletes;
    protected $table = 'laporan_harian';
    protected $fillable = ['outlet_id', 'tanggal', 'total_setor', 'total_pengeluaran', 'talangan', 'status', 'created_by', 'updated_by', 'deleted_by'];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }
    public function details()
    {
        return $this->hasMany(LaporanHarianDetail::class, 'laporan_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pengeluaran()
    {
        return $this->hasMany(LaporanPengeluaran::class, 'laporan_id');
    }
}