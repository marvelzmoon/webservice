<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianAwalKeperawatanRalan extends Model
{
    public $timestamps = false;
    protected $table = "penilaian_awal_keperawatan_ralan";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_rawat';
    protected $fillable = [
        'no_rawat',
        'tanggal',
        'informasi',
        'td',
        'nadi',
        'rr',
        'suhu',
        'gcs',
        'bb',
        'tb',
        'bmi',
        'keluhan_utama',
        'rpd',
        'rpk',
        'rpo',
        'alergi',
        'alat_bantu',
        'ket_bantu',
        'prothesa',
        'ket_pro',
        'adl',
        'status_psiko',
        'ket_psiko',
        'hub_keluarga',
        'tinggal_dengan',
        'ket_tinggal',
        'ekonomi',
        'budaya',
        'ket_budaya',
        'edukasi',
        'ket_edukasi',
        'berjalan_a',
        'berjalan_b',
        'berjalan_c',
        'hasil',
        'lapor',
        'ket_lapor',
        'sg1',
        'nilai1',
        'sg2',
        'nilai2',
        'total_hasil',
        'nyeri',
        'provokes',
        'ket_provokes',
        'quality',
        'ket_quality',
        'lokasi',
        'menyebar',
        'skala_nyeri',
        'durasi',
        'nyeri_hilang',
        'ket_nyeri',
        'pada_dokter',
        'ket_dokter',
        'rencana',
        'nip'
    ];

    protected $hidden = ['nip'];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nik');
    }
}
