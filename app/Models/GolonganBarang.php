<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class GolonganBarang extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "golongan_barang";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kode';
    protected $fillable = [
        "kode",
        "nama",
    ];
}
