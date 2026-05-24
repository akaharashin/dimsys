<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Produk extends Model
{
    use HasUuids;
    protected $table = 'produk';
    protected $fillable = ['nama', 'hpp', 'harga_mitra', 'harga_jual', 'harga_umum', 'harga_agen', 'komisi', 'aktif'];
}