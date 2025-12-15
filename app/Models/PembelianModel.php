<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianModel extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "pembelian";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_faktur';
    protected $fillable = [
        'no_faktur',
        'kd_suplier',
        'nip',
        'tgl_beli',
        'total1',
        'potongan',
        'total2',
        'ppn',
        'tagihan',
        'kd_bangsal',
        'kd_rek'
    ];
    public function DetailBeli()
    {
        return $this->hasMany('App\Models\Khanza\DetailBeli','no_faktur','no_faktur');
    }
}