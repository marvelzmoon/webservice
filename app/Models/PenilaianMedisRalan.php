<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianMedisRalan extends Model
{
    public $timestamps = false;
    protected $table = "penilaian_medis_ralan";
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
        'gigi',
        'tht',
        'thoraks',
        'abdomen',
        'genital',
        'ekstremitas',
        'kulit',
        'ket_fisik',
        'ket_lokalis',
        'penunjang',
        'diagnosis',
        'tata',
        'konsulrujuk'
    ];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }
}
