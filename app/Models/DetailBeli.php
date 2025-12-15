<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class DetailBeli extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "detailbeli";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_faktur';
    protected $fillable = [
        'no_faktur',
        'kode_brng',
        'kode_sat',
        'jumlah',
        'h_beli',
        'subtotal',
        'dis',
        'besardis',
        'total',
        'no_batch',
        'jumlah2',
        'kadaluarsa'
    ];
    
    public function Pembelian()
    {
        return $this->belongsTo(PembelianModel::class,'no_faktur','no_faktur');
    }
}