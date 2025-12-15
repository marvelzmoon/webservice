<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operasi extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "operasi";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['no_rawat','tgl_operasi'];
    protected $fillable = [
        'no_rawat',
        'tgl_operasi',
        "kode_paket",
        "kategori",
        'operator1',
        'operator2',
        'operator3',
        'asisten_operator1',
        'asisten_operator2',
        'asisten_operator3',
        'instrumen',
        'dokter_anak',
        'perawaat_resusitas',
        'dokter_anestesi',
        'asisten_anestesi',
        'asisten_anestesi2',
        'bidan',
        'bidan2',
        'bidan3',
        'perawat_luar',
        'sewa_ok',
        'alat',
        'akomodasi',
        'bagian_rs',
        'omloop',
        'omloop2',
        'omloop3',
        'omloop4',
        'omloop5',
        'sarpras',
        'dokter_pjanak',
        'dokter_umum',
        'status',
        'kd_pj',
        'kelas'
    ];
}
