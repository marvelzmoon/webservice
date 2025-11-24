<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoReferensiAntrianFarmasi extends Model
{
    public $timestamps = false;
    protected $table = "io_referensi_antrian_farmasi";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'nobooking';
    protected $fillable = [
        'nobooking',
        'jenisresep',
        'nomorantrean',
        'keterangan',
        'tgl',
        'validasi'
    ];
}
