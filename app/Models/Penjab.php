<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjab extends Model
{
    public $timestamps = false;
    protected $table = "penjab";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kj_pj';
    protected $fillable = [
        'kd_pj',
        'png_jawab',
        'nama_perusahaan',
        'alamat_asuransi',
        'no_telp',
        'attn',
        'status'
    ];
}
