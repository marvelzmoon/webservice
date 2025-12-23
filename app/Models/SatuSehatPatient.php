<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatuSehatPatient extends Model
{
    public $timestamps = false;
    protected $table = "io_satu_sehat_patient";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_ktp';

    protected $fillable = [
        'no_ktp',
        'patient_id',
    ];
}
