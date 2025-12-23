<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoJenisAntrian extends Model
{
    public $timestamps = false;
    protected $table = "io_jenis_antrian";
    public $incrementing = true;
    protected $keyType = 'int';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'jenis_antrian',
        'prefix'
    ];
}
