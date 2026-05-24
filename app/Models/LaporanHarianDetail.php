<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LaporanHarianDetail extends Model
{
    use HasUuids;
    protected $table = 'laporan_harian_details';
    protected $fillable = ['laporan_id', 'produk_id', 'sisa', 'terjual', 'omset', 'modal', 'komisi'];

    public function laporan() { return $this->belongsTo(LaporanHarian::class, 'laporan_id'); }
    public function produk() { return $this->belongsTo(Produk::class, 'produk_id'); }
}