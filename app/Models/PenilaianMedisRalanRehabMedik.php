<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianMedisRalanRehabMedik extends Model
{
    public $timestamps = false;
    protected $table = "penilaian_medis_ralan_rehab_medik";
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
        'alergi',
        'kesadaran',
        'nyeri',
        'skala_nyeri',
        'td',
        'nadi',
        'suhu',
        'rr',
        'bb',
        'kepala',
        'keterangan_kepala',
        'thoraks',
        'keterangan_thoraks',
        'abdomen',
        'keterangan_abdomen',
        'ekstremitas',
        'keterangan_ekstremitas',
        'columna',
        'keterangan_columna',
        'muskulos',
        'keterangan_muskulos',
        'lainnya',
        'resiko_jatuh',
        'resiko_nutrisional',
        'kebutuhan_fungsional',
        'diagnosa_medis',
        'diagnosa_fungsi',
        'penunjang_lain',
        'fisio',
        'okupasi',
        'wicara',
        'akupuntur',
        'tatalain',
        'frekuensi_terapi',
        'fisioterapi',
        'terapi_okupasi',
        'terapi_wicara',
        'terapi_akupuntur',
        'terapi_lainnya',
        'edukasi'
    ];
}
