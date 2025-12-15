<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    public $timestamps = false;
    protected $table = "kecamatan";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kd_kec';
    protected $fillable = [
        'kd_kec',
        'nm_kec'
    ];
}
