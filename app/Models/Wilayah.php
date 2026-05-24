<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Wilayah extends Model
{
    use HasUuids;
    protected $table = 'wilayah';
    protected $fillable = ['nama', 'tipe', 'aktif'];

    public function users() { return $this->hasMany(User::class, 'wilayah_id'); }
    public function outlet() { return $this->hasMany(Outlet::class, 'wilayah_id'); }
    public function stokMasuk() { return $this->hasMany(StokMasuk::class, 'wilayah_id'); }
    public function rekening() { return $this->hasMany(Rekening::class, 'wilayah_id'); }
    public function penjualanAsal() { return $this->hasMany(PenjualanWilayah::class, 'wilayah_asal_id'); }
    public function penjualanTujuan() { return $this->hasMany(PenjualanWilayah::class, 'wilayah_tujuan_id'); }
}