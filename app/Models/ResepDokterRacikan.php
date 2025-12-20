<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResepDokterRacikan extends Model
{
    public $timestamps = false;
    protected $table = "resep_dokter_racikan";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['no_resep'];
    protected $fillable = [
        'no_resep',
        'no_racik',
        'nama_racik',
        'kd_racik',
        'jml_dr',
        'aturan_pakai',
        'keterangan'
    ];
}
