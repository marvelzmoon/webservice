<?php

namespace App\Models\Khanza;

use Illuminate\Database\Eloquent\Model;

class TemplateLaboratorium extends Model
{
    protected $connection = "second_db";
    protected $table = "template_laboratorium";
    public $incrementing = true;
    public $timestamps = false;
    protected $primaryKey = 'id_template';
    protected $fillable = [
        "kd_jenis_prw",
        "id_template",
        "Pemeriksaan",
        "satuan",
        "nilai_rujukan_ld",
        "nilai_rujukan_la",
        "nilai_rujukan_pd",
        "nilai_rujukan_pa",
        'bagian_rs',
        'bhp',
        'bagian_perujuk',
        'bagian_dokter',
        'bagian_laborat',
        'kso',
        'menejemen',
        'biaya_item',
        'urut',
    ];
}
