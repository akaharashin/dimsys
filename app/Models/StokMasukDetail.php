<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StokMasukDetail extends Model
{
    use HasUuids;
    protected $table = 'stok_masuk_details';
    protected $fillable = ['stok_masuk_id', 'produk_id', 'jumlah', 'hpp'];

    public function stokMasuk()
    {
        return $this->belongsTo(StokMasuk::class, 'stok_masuk_id');
    }
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}