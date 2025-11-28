<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    public $timestamps = false;
    protected $table = "wilayah";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['kode'];
    protected $fillable = [
        'kode',
        'nama',
    ];
}
