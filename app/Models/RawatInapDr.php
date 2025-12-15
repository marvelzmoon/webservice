<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class RawatInapDr extends Model
{
    public $timestamps = false;
    protected $connection = "second_db";
    protected $table = "rawat_inap_dr";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['no_rawat','kd_jenis_prw','kd_dokter','tgl_perawatan','jam_rawat'];
    protected $fillable = [
        'no_rawat',
        'kd_jenis_prw',
        'kd_dokter',
        'tgl_perawatan',
        'jam_rawat',
        'status_bayar',
        'biaya_rawat'
    ];
}
