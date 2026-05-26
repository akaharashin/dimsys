<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PenjualanWilayah extends Model implements HasMedia
{
    use HasUuids;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $table = 'penjualan_wilayah';
    protected $fillable = ['tipe', 'wilayah_asal_id', 'wilayah_tujuan_id', 'tanggal', 'total', 'status_bayar', 'keterangan', 'status', 'transfer_stok_masuk_id', 'created_by', 'deleted_by'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('foto_real')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('berita_acara')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('video')
            ->acceptsMimeTypes(['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm']);
    }

    public function wilayahAsal()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_asal_id');
    }

    public function wilayahTujuan()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_tujuan_id');
    }

    public function details()
    {
        return $this->hasMany(PenjualanWilayahDetail::class, 'penjualan_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
