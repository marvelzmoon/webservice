<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianMedisRalanPenyakitDalam extends Model
{
    public $timestamps = false;
    protected $table = "penilaian_medis_ralan_penyakit_dalam";
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
        'kondisi',
        'status',
        'td',
        'nadi',
        'suhu',
        'rr',
        'bb',
        'nyeri',
        'gcs',
        'kepala',
        'keterangan_kepala',
        'thoraks',
        'keterangan_thorak',
        'abdomen',
        'keterangan_abdomen',
        'ekstremitas',
        'keterangan_ekstremitas',
        'lainnya',
        'lab',
        'rad',
        'penunjanglain',
        'diagnosis',
        'diagnosis2',
        'permasalahan',
        'terapi',
        'tindakan',
        'edukasi'
    ];
}
