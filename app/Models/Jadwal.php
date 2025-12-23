<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    public $timestamps = false;
    protected $table = "jadwal";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['kd_dokter','hari_kerja',['jam_mulai']];
    protected $fillable = [
        'kd_dokter',
        'hari_kerja',
        'jam_mulai',
        'jam_selesai',
        'kd_poli',
        'kuota',
    ];
    public function dokter()
    {
        return $this->hasOne('App\Models\Dokter','kd_dokter','kd_dokter')->select(['kd_dokter','nm_dokter'])->where('status','=','1');
    }
    public function poli()
    {
        return $this->hasOne('App\Models\Poliklinik','kd_poli','kd_poli')->select(['kd_poli','nm_poli'])->where('status','=','1');
    }
}
