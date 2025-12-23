<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanPemeriksaanRadiologi extends Model
{
    public $timestamps = false;
    protected $table = "permintaan_pemeriksaan_radiologi";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['noorder','kd_jenis_prw'];
    protected $fillable = [
        'noorder',
        'kd_jenis_prw',
        'status',
    ];
    public function map(){
        return $this->hasOne('App\Models\SatuSehatMappingRadiologi','kd_jenis_prw','kd_jenis_prw');
    }
    public function tindakan(){
        return $this->hasOne('App\Models\JnsPerawatanRadiologiModel','kd_jenis_prw','kd_jenis_prw');
    }
}
