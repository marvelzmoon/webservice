<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penyakit extends Model
{
    public $timestamps = false;
    protected $table = "penyakit";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_rawat';
    protected $fillable = [
        'kd_penyakit',
        'nm_penyakit',
        'ciri_ciri',
        'keterangan',
        'kd_ktg',
        'status'
    ];
}
