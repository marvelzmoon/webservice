<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class MutasiBarang extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "mutasibarang";
    public $incrementing = false;
    protected $keyType = 'string';
}
