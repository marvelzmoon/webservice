<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianMedisIgd extends Model
{
    public $timestamps = false;
    protected $table = "penilaian_medis_igd";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_rawat';
    protected $fillable = [
        'no_rawat',
        'tanggal',
        'kd_dokter',
        'anamnesis',
        'hubungan',
        'keluhan_utama',
        'rps',
        'rpd',
        'rpk',
        'rpo',
        'alergi',
        'keadaan',
        'gcs',
        'kesadaran',
        'td',
        'nadi',
        'rr',
        'suhu',
        'spo',
        'bb',
        'tb',
        'kepala',
        'mata',
        'gigi',
        'leher',
        'thoraks',
        'abdomen',
        'genital',
        'ekstremitas',
        'ket_fisik',
        'ket_lokalis',
        'ekg',
        'rad',
        'lab',
        'diagnosis',
        'tata'
    ];
}
