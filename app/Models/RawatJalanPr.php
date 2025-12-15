<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawatJalanPr extends Model
{
    public $timestamps = false;
    // protected $connection = "second_db";
    protected $table = "rawat_jl_pr";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['no_rawat', 'kd_jenis_prw', 'nip', 'tgl_perawatan', 'jam_rawat'];
    protected $fillable = [
        'no_rawat',
        'kd_jenis_prw',
        'nip',
        'tgl_perawatan',
        'jam_rawat',
        'status_bayar',
        'biaya_rawat'
    ];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }
}
