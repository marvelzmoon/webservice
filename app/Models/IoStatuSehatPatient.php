<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoStatuSehatPatient extends Model
{
    protected $table = 'io_satu_sehat_patient';
    public $incrementing = false;
    protected $keyType = 'no_ktp';

    protected $fillable = [
        'no_ktp',
        'patient_id',
    ];
}
