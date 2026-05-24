<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Supplier extends Model
{
    use HasUuids;
    protected $fillable = ['nama', 'keterangan', 'aktif'];
    protected $table = 'supplier'; // tambah ini
    public function stokMasuk()
    {
        return $this->hasMany(StokMasuk::class, 'supplier_id');
    }
}