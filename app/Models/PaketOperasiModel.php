<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaketOperasiModel extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "paket_operasi";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'kode_paket';
    protected $fillable = [
        "kode_paket",
        "nm_perawatan",
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
