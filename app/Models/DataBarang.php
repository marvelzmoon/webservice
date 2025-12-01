<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataBarang extends Model
{
    public $timestamps = false;
    protected $table = "databarang";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kode_brng';
    protected $fillable = [
        "kode_brng",
        "nama_brng",
        "kode_satbesar",
        "kode_sat",
        "kode_kategori",
        "kode_industri",
        "kode_golongan",
        "kdjns",
        "letak_barang",
        "dasar",
        "h_beli",
        "ralan",
        "kelas1",
        "kelas2",
        "kelas3",
        "utama",
        "vip",
        "vvip",
        "beliluar",
        "jualbebas",
        "karyawan",
    ];
}
