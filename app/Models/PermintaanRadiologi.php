<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanRadiologi extends Model
{
    public $timestamps = false;
    protected $table = "permintaan_radiologi";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['noorder'];
    protected $fillable = [
        'noorder',
        'no_rawat',
        'tgl_permintaan',
        'jam_permintaan',
        'tgl_sampel',
        'jam_sampel',
        'tgl_hasil',
        'jam_hasil',
        'dokter_perujuk',
        'status',
        'informasi_tambahan',
        'diagnosa_klinis',
    ];
    public function register()
    {
        return $this->hasOne('App\Models\RegPeriksaModel','no_rawat','no_rawat')->select(['no_rawat','no_rkm_medis','kd_dokter','kd_poli','tgl_registrasi','jam_reg'])->with('pasien_compact');
    }
    public function encounter(){
        return $this->hasOne('App\Models\SatuSehatEncounter','no_rawat','no_rawat');
    }
    public function request(){
        return $this->hasMany('App\Models\SatuSehatServiceRequestRadiologi','noorder','noorder');
    }
    public function pemeriksaan(){
        return $this->hasMany('App\Models\PermintaanPemeriksaanRadiologi','noorder','noorder')->with(['map','tindakan']);
    }
    public function perujuk(){
        return $this->hasOne('App\Models\Pegawai','nik','dokter_perujuk')->select(['nik','nama','no_ktp'])->with(['practitioner']);
    }
    public function imaging(){
        return $this->hasMany('App\Models\IoStatuSehatImagingStudy','noorder','noorder')->with(['instance']);
    }
}
