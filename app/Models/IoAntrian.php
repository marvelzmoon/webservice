<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoAntrian extends Model
{
    public $timestamps = false;
    protected $table = "io_antrian";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_referensi';
    protected $fillable = [
        'no_referensi',
        'no_antrian',
        'status_panggil',
        'status_antrian',
        'calltime',
        'status_pasien'
    ];
}
