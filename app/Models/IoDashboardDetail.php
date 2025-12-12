<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoDashboardDetail extends Model
{
    public $timestamps = false;
    protected $table = "io_dashboard_detail";
    public $incrementing = true;
    protected $keyType = 'int';
    protected $primaryKey = 'ddash_id';
    protected $fillable = [
        'ddash_parent',
        'ddash_poli',
        'ddash_dokter',
        'ddash_status'
    ];

    public function parent()
    {
        return $this->belongsTo(IoDashboard::class, 'ddash_parent', 'dash_id');
    }
    
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'ddash_dokter', 'kd_dokter');
    }

    public function poli()
    {
        return $this->belongsTo(Poliklinik::class, 'ddash_poli', 'kd_poli');
    }
}
