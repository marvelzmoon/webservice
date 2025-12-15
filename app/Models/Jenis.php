<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class Jenis extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "jenis";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kdjns';
    protected $fillable = [
        "kdjns",
        "nama",
    ];
}
