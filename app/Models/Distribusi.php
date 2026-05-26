<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Distribusi extends Model
{
    use HasUuids, SoftDeletes;
    protected $fillable = ['outlet_id', 'tanggal', 'keterangan', 'created_by', 'updated_by', 'deleted_by'];

    protected $table = 'distribusi';
    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }
    public function details()
    {
        return $this->hasMany(DistribusiDetail::class, 'distribusi_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}