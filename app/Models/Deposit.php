<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "deposit";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_deposit';
    protected $fillable = [
        'no_deposit',
        'no_rawat',
        'tgl_deposit',
        'nama_bayar',
        'besarppn',
        'besar_deposit',
        'nip',
        'keterangan'
    ];
}