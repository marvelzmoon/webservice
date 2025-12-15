<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class NotaJalan extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "nota_jalan";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['no_rawat'];
    protected $fillable = [
        "no_rawat",
        "no_nota",
        "tanggal",
        "jam"
    ];
}
