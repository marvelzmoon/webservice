<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosaPasien extends Model
{
    public $timestamps = false;
    protected $table = "diagnosa_pasien";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_rawat';
    protected $fillable = [
        'no_rawat',
        'kd_penyakit',
        'status',
        'prioritas',
        'status_penyakit'
    ];

    public function penyakit()
    {
        return $this->belongsTo(Penyakit::class, 'kd_penyakit', 'kd_penyakit');
    }
}
