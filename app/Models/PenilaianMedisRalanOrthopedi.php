<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianMedisRalanOrthopedi extends Model
{
    public $timestamps = false;
    protected $table = "penilaian_medis_ralan_orthopedi";
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
        'kesadaran',
        'status',
        'td',
        'nadi',
        'suhu',
        'rr',
        'bb',
        'nyeri',
        'gcs',
        'kepala',
        'thoraks',
        'abdomen',
        'ekstremitas',
        'genetalia',
        'columna',
        'muskulos',
        'lainnya',
        'ket_lokalis',
        'lab',
        'rad',
        'pemeriksaan',
        'diagnosis',
        'diagnosis2',
        'permasalahan',
        'terapi',
        'tindakan',
        'edukasi'
    ];
}
