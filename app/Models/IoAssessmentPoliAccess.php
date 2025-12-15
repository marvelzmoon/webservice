<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoAssessmentPoliAccess extends Model
{
    public $timestamps = false;
    protected $table = "io_assessment_poli_access";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kd_poli';
    protected $fillable = [
        'kd_poli',
        'asessment',
        'assessment_nurse'
    ];
}
