<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatuSehatMappingLokasiRalan extends Model
{
    public $timestamps = false;
    protected $table = "satu_sehat_mapping_lokasi_ralan";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kd_poli';

    protected $fillable = [
        'kd_poli',
        'id_organisasi_satusehat',
        'id_lokasi_satusehat',
        'longitude',
        'latitude',
        'altittude',
    ];
}
