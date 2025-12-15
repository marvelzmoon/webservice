<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    public $timestamps = false;
    protected $table = "kelurahan";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kd_kel';
    protected $fillable = [
        'kd_kel',
        'nm_kel'
    ];
}
