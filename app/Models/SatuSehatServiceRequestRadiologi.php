<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatuSehatServiceRequestRadiologi extends Model
{
    public $timestamps = false;
    protected $table = "satu_sehat_servicerequest_radiologi";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'noorder';

    protected $fillable = [
        'noorder',
        'kd_jenis_prw',
        'id_servicerequest'
    ];
}
