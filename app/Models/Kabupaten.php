<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    public $timestamps = false;
    protected $table = "kabupaten";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kd_kab';
    protected $fillable = [
        'kd_kab',
        'nm_kab'
    ];
}
