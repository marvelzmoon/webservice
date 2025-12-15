<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianMedisRalanTht extends Model
{
    public $timestamps = false;
    protected $table = "penilaian_medis_ralan_tht";
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
        'rpo',
        'alergi',
        'td',
        'nadi',
        'rr',
        'suhu',
        'bb',
        'tb',
        'nyeri',
        'status_nutrisi',
        'kondisi',
        'ket_lokalis',
        'lab',
        'rad',
        'tes_pendengaran',
        'penunjang',
        'diagnosis',
        'diagnosisbanding',
        'permasalahan',
        'terapi',
        'tindakan',
        'tatalaksana',
        'edukasi'
    ];
}
