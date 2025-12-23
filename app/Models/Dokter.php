<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    public $timestamps = false;
    protected $table = "dokter";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kd_dokter';
    protected $fillable = [
        'kd_dokter',
        'nm_dokter',
        'jk',
        'tmp_lahir',
        'tgl_lahir',
        'gol_drh',
        'agama',
        'almt_tgl',
        'no_telp',
        'email',
        'stts_nikah',
        'kd_sps',
        'alumni',
        'no_ijn_praktek',
        'status',
    ];
    public function pegawai()
    {
        return $this->hasOne('App\Models\Pegawai','nik','kd_dokter');
    }
    public function pegawai_compact()
    {
        return $this->hasOne('App\Models\Pegawai','nik','kd_dokter')->select(['nik','nama','no_ktp'])->with('practitioner');
    }
}
