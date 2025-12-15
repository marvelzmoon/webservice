<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class NotaInap extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "nota_inap";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['no_rawat'];
    protected $fillable = [
        "no_rawat",
        "no_nota",
        "tanggal",
        "jam",
        "Uang_Muka"
    ];
}
