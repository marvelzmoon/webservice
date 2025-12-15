<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JnsPerawatanInapModel extends Model
{
    public $timestamps = false;
    protected $table = "jns_perawatan_inap";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kd_jenis_prw';
    protected $fillable = [
        "kd_jenis_prw",
        "nm_perawatan",
        "kd_kategori",
        'bhp',
        'tarif_tindakandr',
        'tarif_tindakanpr',
        'kso',
        'menejemen',
        'kd_pj',
        'kd_bangsal',
        'kelas',
        'status',
        "total_byrdr",
        "total_byrpr",
        "total_byrdrpr",
    ];
}
