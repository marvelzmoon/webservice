<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JnsPerawatanRadiologiModel extends Model
{
    public $timestamps = false;
    protected $table = "jns_perawatan_radiologi";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kd_jenis_prw';
    protected $fillable = [
        "kd_jenis_prw",
        "nm_perawatan",
        'bhp',
        'tarif_perujuk',
        'tarif_tindakan_dokter',
        'tarif_tindakan_petugas',
        'kso',
        'menejemen',
        "total_byr",
        'kd_pj',
        'status',
        'kelas',
    ];
}
