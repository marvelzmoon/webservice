<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bangsal extends Model
{
    public $timestamps = false;
    protected $table = "bangsal";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kd_bangsal';
    protected $fillable = [
        'kd_bangsal',
        'nm_bangsal',
        'status'
    ];
}
