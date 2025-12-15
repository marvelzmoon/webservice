<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poliklinik extends Model
{
    public $timestamps = false;
    protected $table = "poliklinik";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['kd_poli'];
    protected $fillable = [
        'kd_poli',
        'nm_poli',
        'registrasi',
        'registrasilama',
        'status',
    ];
}
