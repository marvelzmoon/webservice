<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class KamarInap extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "kamar_inap";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['no_rawat','tgl_masuk','jam_masuk'];
    protected $fillable = [
        'no_rawat',
        'tgl_masuk',
        'jam_masuk'
    ];
}
