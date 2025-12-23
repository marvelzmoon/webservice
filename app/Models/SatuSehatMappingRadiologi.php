<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatuSehatMappingRadiologi extends Model
{
    public $timestamps = false;
    protected $table = "satu_sehat_mapping_radiologi";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kd_jenis_prw';

    protected $fillable = [
        'kd_jenis_prw',
        'code',
        'system',
        'display',
        'sampel_code',
        'sampel_system',
        'sampel_display'
    ];
}
