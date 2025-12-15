<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AkunBayarModel extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "akun_bayar";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'nama_bayar';
    protected $fillable = [
        "nama_bayar",
        "kd_rek",
        "ppn"
    ];
}
