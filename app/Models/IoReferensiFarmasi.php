<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoReferensiFarmasi extends Model
{
    public $timestamps = false;
    protected $table = "io_referensi_farmasi";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_resep';
    protected $fillable = [
        'no_resep',
        'kodebooking',
        'tanggal',
        'prefix',
        'no_antrian',
        'jenis_resep',
        'calltime',
        'status',
        'status_send',
        'json'
    ];
}
