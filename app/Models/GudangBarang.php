<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class GudangBarang extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "databarang";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['kode_brng','kd_bangsal','no_faktur','no_batch'];
    protected $fillable = [
        "kode_brng",
        "kd_bangsal",
        "stok",
        "no_batch",
        "no_faktur",
    ];
}
