<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kas extends Model
{
    use HasUuids;
    use SoftDeletes;
    protected $table = 'kas';
    protected $fillable = [
        'rekening_id',
        'outlet_id',
        'tanggal',
        'tipe',
        'kategori',
        'sub_kategori',
        'keterangan',
        'penerima',
        'jumlah',
        'saldo',
        'created_by'
    ];


    public function rekening()
    {
        return $this->belongsTo(Rekening::class, 'rekening_id');
    }
    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


}