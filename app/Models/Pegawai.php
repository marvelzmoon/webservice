<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    public $timestamps = false; // Menonaktifkan timestamp (created_at dan updated_at)
    protected $table = 'pegawai'; // Menentukan nama tabel
    protected $keyType = 'int'; // Menentukan tipe data primary key
    protected $primaryKey = 'id'; // Menentukan primary key tabel
    protected $fillable = [
        'id',
        'nik',
        'nama',
        'jk',
        'jbtn',
        'jnj_jabatan',
        'kode_kelompok',
        'kode_resiko',
        'kode_emergency',
        'departemen',
        'bidang',
        'stts_wp',
        'stts_kerja',
        'npwp',
        'pendidikan',
        'gapok',
        'tmp_lahir',
        'tgl_lahir',
        'alamat',
        'kota',
        'mulai_kerja',
        'ms_kerja',
        'indexins',
        'bpd',
        'rekening',
        'stts_aktif',
        'wajibmasuk',
        'pengurang',
        'indek',
        'mulai_kontrak',
        'cuti_diambil',
        'dankes',
        'photo',
        'no_ktp'
    ];

    // Jika ada kolom yang berhubungan dengan tanggal, seperti tgl_lahir, mulai_kerja, etc.
    protected $dates = [
        'tgl_lahir',
        'mulai_kerja',
        'mulai_kontrak'
    ];
    public function practitioner()
    {
        return $this->hasOne('App\Models\SatuSehatPractitioner','no_ktp','no_ktp');
    }
}
