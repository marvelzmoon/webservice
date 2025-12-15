<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class DetailNotaInap extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "detail_nota_inap";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['no_rawat','nama_bayar'];
    protected $fillable = [
        "no_rawat",
        "nama_bayar",
        "besarppn",
        "besar_bayar"
    ];
}
