<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class PeriksaRadiologi extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "periksa_radiologi";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['no_rawat','kd_jenis_prw','tgl_periksa','jam'];
    protected $fillable = [
    ];
}
