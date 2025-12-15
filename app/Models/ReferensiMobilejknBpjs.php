<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferensiMobilejknBpjs extends Model
{
    public $timestamps = false;
    protected $table = "referensi_mobilejkn_bpjs";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = ['nobooking'];
    protected $fillable = [
        'nobooking', 
        'no_rawat', 
        'nomorkartu', 
        'nik', 
        'nohp', 
        'kodepoli', 
        'pasienbaru', 
        'norm', 
        'tanggalperiksa', 
        'kodedokter', 
        'jampraktek', 
        'jeniskunjungan', 
        'nomorreferensi', 
        'nomorantrean', 
        'angkaantrean', 
        'estimasidilayani', 
        'sisakuotajkn', 
        'kuotajkn', 
        'sisakuotanonjkn', 
        'kuotanonjkn', 
        'status', 
        'validasi', 
        'statuskirim'
    ];
    
}
