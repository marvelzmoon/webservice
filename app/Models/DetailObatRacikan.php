<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailObatRacikan extends Model
{
    public $timestamps = false;
    protected $table = "detail_obat_racikan";
    // public $incrementing = false;
    // protected $keyType = 'string';
    // protected $primaryKey = ['tgl_perawatan', 'jam', 'no_rawat', 'kode_brng', 'no_batch', 'no_faktur'];
    protected $fillable = [
        `tgl_perawatan`,
        `jam`,
        `no_rawat`,
        `no_racik`,
        `kode_brng`
    ];
}
