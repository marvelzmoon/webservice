<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class IoSuratKontrol extends Model
{
    use SoftDeletes;
    protected $table = 'io_surat_kontrol';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'no_surat',
        'no_rkm_medis',
        'alasan',
        'tanggal_kontrol',
        'kd_dokter',
        'kd_poli',
        'status',
    ];
}
