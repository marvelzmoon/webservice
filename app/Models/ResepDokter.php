<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResepDokter extends Model
{
    public $timestamps = false;
    protected $table = "resep_dokter";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['no_resep'];
    protected $fillable = [
        'no_resep',
        'tgl_perawatan',
        'jam',
        'no_rawat',
        'kd_dokter',
        'tgl_peresepan',
        'jam_peresepan',
        'status',
        'tgl_penyerahan',
        'jam_penyerahan'
    ];
}
