<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaporanPengeluaran extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'laporan_pengeluaran';
    protected $fillable = ['laporan_id', 'keterangan', 'jumlah'];

    public function laporan()
    {
        return $this->belongsTo(LaporanHarian::class, 'laporan_id');
    }
}