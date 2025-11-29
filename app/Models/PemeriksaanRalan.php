<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PemeriksaanRalan extends Model
{
    public $timestamps = false;
    protected $table = "pemeriksaan_ralan";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_rawat';
    protected $fillable = [
        'no_rawat',
        'tgl_perawatan',
        'jam_rawat',
        'suhu_tubuh',
        'tensi',
        'nadi',
        'respirasi',
        'tinggi',
        'berat',
        'spo2',
        'gcs',
        'kesadaran',
        'keluhan',
        'pemeriksaan',
        'alergi',
        'lingkar_perut',
        'rtl',
        'penilaian',
        'instruksi',
        'evaluasi',
        'nip'
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nik');
    }
}
