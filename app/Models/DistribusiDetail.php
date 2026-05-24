<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DistribusiDetail extends Model
{
    use HasUuids;
    protected $table = 'distribusi_details';
    protected $fillable = ['distribusi_id', 'produk_id', 'jumlah_out'];

    public function distribusi() { return $this->belongsTo(Distribusi::class, 'distribusi_id'); }
    public function produk() { return $this->belongsTo(Produk::class, 'produk_id'); }
}