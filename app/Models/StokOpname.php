<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class StokOpname extends Model implements HasMedia
{
    use HasUuids, SoftDeletes, InteractsWithMedia;

    protected $table = 'stok_opname';

    protected $fillable = [
        'wilayah_id',
        'tanggal',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('foto_real')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('berita_acara')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }
    public function details()
    {
        return $this->hasMany(StokOpnameDetail::class, 'stok_opname_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}