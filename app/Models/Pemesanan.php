<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class Pemesanan extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "pemesanan";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_faktur';
    protected $fillable = [
        'no_faktur',
        'kd_suplier',
        'nip',
        'tgl_pesan',
        'tgl_faktur',
        'tgl_tempo',
        'total1',
        'potongan',
        'total2',
        'ppn',
        'tagihan',
        'kd_bangsal',
        'materai'
    ];
    public function DetailPesan()
    {
        return $this->hasMany('App\Models\Khanza\DetailPesan','no_faktur','no_faktur');
    }
}