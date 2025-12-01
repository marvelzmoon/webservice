<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPemberianObat extends Model
{
    public $timestamps = false;
    protected $table = "detail_pemberian_obat";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['tgl_perawatan', 'jam', 'no_rawat', 'kode_brng', 'no_batch', 'no_faktur'];
    protected $fillable = [
        'tgl_perawatan',
        'jam',
        'no_rawat',
        'kode_brng',
        'h_beli',
        'biaya_obat',
        'jml',
        'embalase',
        'tuslah',
        'total',
        'status',
        'kd_bangsal',
        'no_batch',
        'no_faktur'
    ];

    public function databarang()
    {
        return $this->belongsTo(DataBarang::class, 'kode_brng', 'kode_brng');
    }

    public function bangsal()
    {
        return $this->belongsTo(Bangsal::class, 'kd_bangsal', 'kd_bangsal');
    }
}
