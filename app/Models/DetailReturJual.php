<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class DetailReturJual extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "detreturjual";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['no_retur_jual','kode_brng'];
    // protected $fillable = [
    //     "no_rawat",
    //     "nama_bayar",
    //     "besarppn",
    //     "besar_bayar"
    // ];
}
