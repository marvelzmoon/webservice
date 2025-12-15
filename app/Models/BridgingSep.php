<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BridgingSep extends Model
{
    public $timestamps = false;
    protected $table = "bridging_sep";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_sep';

    protected $fillable = [
        'no_sep',
        'no_rawat',
        'tglsep',
        'tglrujukan',
        'no_rujukan',
        'kdppkrujukan',
        'nmppkrujukan',
        'kdppkpelayanan',
        'nmppkpelayanan',
        'jnspelayanan',
        'catatan',
        'diagawal',
        'nmdiagnosaawal',
        'kdpolitujuan',
        'nmpolitujuan',
        'klsrawat',
        'klsnaik',
        'pembiayaan',
        'pjnaikkelas',
        'lakalantas',
        'user',
        'nomr',
        'nama_pasien',
        'tanggal_lahir',
        'peserta',
        'jkel',
        'no_kartu',
        'tglpulang',
        'asal_rujukan',
        'eksekutif',
        'cob',
        'notelep',
        'katarak',
        'tglkkl',
        'keterangankkl',
        'suplesi',
        'no_sep_suplesi',
        'kdprop',
        'nmprop',
        'kdkab',
        'nmkab',
        'kdkec',
        'nmkec',
        'noskdp',
        'kddpjp',
        'nmdpdjp',
        'tujuankunjungan',
        'flagprosedur',
        'penunjang',
        'asesmenpelayanan',
        'kddpjplayanan',
        'nmdpjplayanan'
    ];
}
