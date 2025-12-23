<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IoStatuSehatImagingStudy extends Model
{
    public $timestamps = false;
    protected $table = 'io_satu_sehat_imaging_study';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'acsn';

    protected $fillable = [
        'acsn',
        'noorder',
        'ks_jenis_prw',
        'id_imaging_study',
        'study_id',
        'study_uuid',
        'patient_id'
    ];
    public function instance(){
        return $this->hasMany('App\Models\IoRadiologyDicomInstance','acsn','acsn');
    }
}
