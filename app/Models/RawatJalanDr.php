<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawatJalanDr extends Model
{
    public $timestamps = false;
    // protected $connection = "second_db";
    protected $table = "rawat_jl_dr";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['no_rawat', 'kd_jenis_prw', 'kd_dokter', 'tgl_perawatan', 'jam_rawat'];
    protected $fillable = [
        'no_rawat',
        'kd_jenis_prw',
        'kd_dokter',
        'tgl_perawatan',
        'jam_rawat',
        'material',
        'bhp',
        'tarif_tindakandr',
        'kso',
        'menejemen',
        'biaya_rawat',
        'stts_bayar'
    ];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

    public function jnsprw()
    {
        return $this->belongsTo(JnsPerawatanModel::class, 'kd_jenis_prw', 'kd_jenis_prw');
    }
}
