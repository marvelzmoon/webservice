<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class KodeSatuan extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "kodesatuan";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kode_sat';
    protected $fillable = [
        "kode_sat",
        "satuan",
    ];
}
