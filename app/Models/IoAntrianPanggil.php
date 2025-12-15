<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoAntrianPanggil extends Model
{
    public $timestamps = false;
    protected $table = "io_antrian_panggil";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_referensi';
    protected $fillable = [
        'no_referensi',
        'dashboard_id',
        'type',
        'counter'
    ];
}
