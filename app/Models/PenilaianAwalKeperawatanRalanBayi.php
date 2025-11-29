<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianAwalKeperawatanRalanBayi extends Model
{
    public $timestamps = false;
    protected $table = "penilaian_awal_keperawatan_ralan_bayi";
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'no_rawat';
    protected $fillable = [
        'no_rawat',
        'tanggal',
        'informasi',
        'td',
        'nadi',
        'rr',
        'suhu',
        'gcs',
        'bb',
        'tb',
        'lp',
        'lk',
        'ld',
        'keluhan_utama',
        'rpd',
        'rpk',
        'rpo',
        'alergi',
        'anakke',
        'darisaudara',
        'caralahir',
        'ket_caralahir',
        'umurkelahiran',
        'kelainanbawaan',
        'ket_kelainan_bawaan',
        'usiatengkurap',
        'usiaduduk',
        'usiaberdiri',
        'usiagigipertama',
        'usiaberjalan',
        'usiabicara',
        'usiamembaca',
        'usiamenulis',
        'gangguanemosi',
        'alat_bantu',
        'ket_bantu',
        'prothesa',
        'ket_pro',
        'adl',
        'status_psiko',
        'ket_psiko',
        'hub_keluarga',
        'pengasuh',
        'ket_pengasuh',
        'ekonomi',
        'budaya',
        'ket_budaya',
        'edukasi',
        'ket_edukasi',
        'berjalan_a',
        'berjalan_b',
        'berjalan_c',
        'hasil',
        'lapor',
        'ket_lapor',
        'sg1',
        'nilai1',
        'sg2',
        'nilai2',
        'sg3',
        'nilai3',
        'sg4',
        'nilai4',
        'total_hasil',
        'wajah',
        'nilaiwajah',
        'kaki',
        'nilaikaki',
        'aktifitas',
        'nilaiaktifitas',
        'menangis',
        'nilaimenangis',
        'bersuara',
        'nilaibersuara',
        'hasilnyeri',
        'nyeri',
        'lokasi',
        'durasi',
        'frekuensi',
        'nyeri_hilang',
        'ket_nyeri',
        'pada_dokter',
        'ket_dokter',
        'rencana',
        'nip'
    ];

    protected $hidden = ['nip'];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nip', 'nik');
    }
}
