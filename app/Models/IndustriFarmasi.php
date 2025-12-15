<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class IndustriFarmasi extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "industrifarmasi";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kode_industri';
    protected $fillable = [
        "kode_industri",
        "nama_industri",
        "alamat",
        "kota",
        "no_telp",
    ];
}
