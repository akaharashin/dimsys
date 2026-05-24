<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StokOpnameDetail extends Model
{
    use HasUuids;

    protected $table = 'stok_opname_details';

    protected $fillable = [
        'stok_opname_id',
        'produk_id',
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'hpp_snapshot',
        'nilai_selisih'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
    public function stokOpname()
    {
        return $this->belongsTo(StokOpname::class, 'stok_opname_id');
    }
}