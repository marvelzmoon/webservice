<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianMedisRalanObgyn extends Model
{
    public $timestamps = false;
    protected $table = "penilaian_medis_ralan_kandungan";
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
        'tht',
        'thoraks',
        'abdomen',
        'genital',
        'ekstremitas',
        'kulit',
        'ket_fisik',
        'tfu',
        'tbj',
        'his',
        'kontraksi',
        'djj',
        'inspeksi',
        'inspekulo',
        'vt',
        'rt',
        'ultra',
        'kardio',
        'lab',
        'diagnosis',
        'tata',
        'konsul'
    ];
}
