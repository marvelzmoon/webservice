<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoAntrianFarmasi extends Model
{
    public $timestamps = false;
    protected $table = "io_antrian_farmasi";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_referensi';
    protected $fillable = [
        'no_referensi',
        'no_antrian',
        'status_antrian',
        'calltime',
        'status_pasien',
        'status_panggil',
        'kategori_antrian',
        'tgl'
    ];
}
