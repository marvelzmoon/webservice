<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoRadiologyDicomInstance extends Model
{
    public $timestamps = false;
    protected $table = 'io_radiology_dicom_instance';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'instance_id';

    protected $fillable = [
        'instance_id',
        'acsn',
    ];
}
