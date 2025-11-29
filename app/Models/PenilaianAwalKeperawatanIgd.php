<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianAwalKeperawatanIgd extends Model
{
    public $timestamps = false;
    protected $table = "penilaian_awal_keperawatan_igd";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_rawat';
    protected $fillable = [
        'no_rawat',
        'tanggal',
        'informasi',
        'keluhan_utama',
        'rpd',
        'rpo',
        'status_kehamilan',
        'gravida',
        'para',
        'abortus',
        'hpht',
        'tekanan',
        'pupil',
        'neurosensorik',
        'integumen',
        'turgor',
        'edema',
        'mukosa',
        'perdarahan',
        'jumlah_perdarahan',
        'warna_perdarahan',
        'intoksikasi',
        'bab',
        'xbab',
        'kbab',
        'wbab',
        'bak',
        'xbak',
        'wbak',
        'lbak',
        'psikologis',
        'jiwa',
        'perilaku',
        'dilaporkan',
        'sebutkan',
        'hubungan',
        'tinggal_dengan',
        'ket_tinggal',
        'budaya',
        'ket_budaya',
        'pendidikan_pj',
        'ket_pendidikan_pj',
        'edukasi',
        'ket_edukasi',
        'kemampuan',
        'aktifitas',
        'alat_bantu',
        'ket_bantu',
        'nyeri',
        'provokes',
        'ket_provokes',
        'quality',
        'ket_quality',
        'lokasi',
        'menyebar',
        'skala_nyeri',
        'durasi',
        'nyeri_hilang',
        'ket_nyeri',
        'pada_dokter',
        'ket_dokter',
        'berjalan_a',
        'berjalan_b',
        'berjalan_c',
        'hasil',
        'lapor',
        'ket_lapor',
        'rencana',
        'nip'
    ];
}
