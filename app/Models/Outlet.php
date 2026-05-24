<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Outlet extends Model
{
    use HasUuids;
    protected $fillable = ['nama', 'wilayah_id', 'tipe', 'aktif'];
    protected $table = 'outlet';
    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }
    public function distribusi()
    {
        return $this->hasMany(Distribusi::class, 'outlet_id');
    }
    public function laporanHarian()
    {
        return $this->hasMany(LaporanHarian::class, 'outlet_id');
    }
}