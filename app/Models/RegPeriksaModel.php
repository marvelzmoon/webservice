<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegPeriksaModel extends Model
{
    protected $table = "reg_periksa";
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $primaryKey = 'no_rawat';

    protected $fillable = [
        'no_reg',
        'no_rawat',
        'tgl_registrasi',
        'jam_reg',
        'kd_dokter',
        'no_rkm_medis',
        'kd_poli',
        'p_jawab',
        'almt_pj',
        'hubunganpj',
        'biaya_reg',
        'stts',
        'stts_daftar',
        'status_lanjut',
        'kd_pj',
        'umurdaftar',
        'sttsumur',
        'status_bayar',
        'status_poli'
    ];
    public function pasien()
    {
        return $this->hasOne('App\Models\Pasien','no_rkm_medis','no_rkm_medis');
    }
    public function pasien_compact()
    {
        return $this->hasOne('App\Models\Pasien','no_rkm_medis','no_rkm_medis')->select(['no_rkm_medis','nm_pasien','jk','tgl_lahir','alamat','no_ktp'])->with(['patient_id']);
    }
    public function doctor()
    {
        return $this->hasOne('App\Models\Dokter','kd_dokter','kd_dokter')->with(['pegawai_compact']);
    }
    public function policlinic()
    {
        return $this->hasOne('App\Models\Poliklinik','kd_poli','kd_poli');
    }
    public function asuransi()
    {
        return $this->hasOne('App\Models\Penjab','kd_pj','kd_pj');
    }
    public function satusehatlokasi()
    {
        return $this->hasOne('App\Models\SatuSehatMappingLokasiRalan','kd_poli','kd_poli')->select(['kd_poli','id_organisasi_satusehat','id_lokasi_satusehat']);
    }
}
