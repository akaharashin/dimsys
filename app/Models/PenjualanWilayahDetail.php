<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PenjualanWilayahDetail extends Model
{
    use HasUuids;
    protected $table = 'penjualan_wilayah_details';
    protected $fillable = ['penjualan_id', 'produk_id', 'jumlah', 'harga_agen', 'subtotal'];

    public function penjualan() { return $this->belongsTo(PenjualanWilayah::class, 'penjualan_id'); }
    public function produk() { return $this->belongsTo(Produk::class, 'produk_id'); }
}