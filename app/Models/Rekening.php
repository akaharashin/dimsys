<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Rekening extends Model
{
    use HasUuids;
    protected $fillable = ['wilayah_id', 'nama', 'tipe', 'saldo_awal', 'aktif'];
    protected $table = 'rekening';
    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }
    public function kas()
    {
        return $this->hasMany(Kas::class, 'rekening_id');
    }
}