<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Erm\DataKlinis\RiwayatController;
use App\Http\Controllers\IntegratedService\ISServiceController;
use App\Http\Controllers\Jkn\JknApiAntrolController;
use App\Http\Controllers\Jkn\JknSuratkontrolController;
use App\Http\Controllers\Jkn\JknTaskidController;
use App\Http\Controllers\Master\PasienController;
use App\Http\Controllers\Master\DokterController;
use App\Http\Controllers\Master\PoliklinikController;
use App\Http\Controllers\Master\ReferensiController;
use App\Http\Controllers\Registrasi\RegistrasiController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/login', action: [AuthController::class, 'login']);
Route::post('/auth/check-username', action: [AuthController::class, 'checkUsername']);
Route::post('/check', action: [AuthController::class, 'check']);
Route::get('/auth/login-data', action: [AuthController::class, 'loginData']);

Route::middleware(['api_token'])->group(function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('/user', 'index');
    });

    Route::controller(JknSuratkontrolController::class)->group(function () {
        Route::post('/jkn/surkon/getdata', 'getdata');
    });

    Route::controller(JknTaskidController::class)->group(function () {
        Route::post('/jkn/taskid/post', 'post');
        Route::post('/jkn/taskid/data', 'getdata');
        Route::post('/jkn/taskid/send', 'send');
    });

    Route::controller(RegistrasiController::class)->group(function () {
        Route::post('/registrasi/getdata', 'getdata');
        Route::post('/registrasi/post', 'post');
        Route::post('/registrasi/add-antrol', 'addantrian');
        Route::post('/registrasi/batal-periksa', 'batalPeriksa');
        Route::post('/registrasi/add-antrol-farmasi', 'addAntrianFarmasi');
    });

    Route::controller(ReferensiController::class)->group(function () {
        Route::get('/ref/penjab', 'penjab');
        Route::post('/ref/kelurahan', 'kelurahan');
        Route::post('/ref/kecamatan', 'kecamatan');
        Route::post('/ref/kabupaten', 'kabupaten');
        Route::get('/ref/perusahaan-pasien', 'perusahaanpasien');
        Route::get('/ref/suku-bangsa', 'sukubangsa');
        Route::get('/ref/bahasa-pasien', 'bahasapasien');
        Route::get('/ref/cacat-fisik', 'cacatfisik');
        Route::get('/ref/propinsi', 'propinsi');
        Route::get('/ref/provinsi', 'provinsi');
        Route::post('/ref/ambil-wilayah', 'getWilayah');
    });

    Route::controller(PasienController::class)->group(function () {
        Route::get('/pasien/getdata', 'getdata');
        Route::post('/pasien/search', 'searchPasien');
        Route::post('/pasien/create', 'createPasien');
        Route::post('/pasien/destroy', 'destroy');
    });

    Route::controller(RiwayatController::class)->group(function () {
        Route::post('/erm/data-klinis/riwayat/getdata', 'getdata');
        Route::post('/erm/data-klinis/riwayat/soap', 'soapie');
        Route::post('/erm/data-klinis/riwayat/soap/multi', 'soapiemulti');
        Route::post('/erm/data-klinis/riwayat/sepbpjs', 'datasep');
        Route::post('/erm/data-klinis/riwayat/awal/medis', 'awalMedis');
        Route::post('/erm/data-klinis/riwayat/awal/keperawatan', 'awalKeperawatan');
        Route::post('/erm/data-klinis/riwayat/diagnosa-icd10', 'diagnosaIcd10');
        Route::post('/erm/data-klinis/riwayat/tindakan/dokter/rajal', 'tindakanDokterRajal');
        Route::post('/erm/data-klinis/riwayat/detail-pemberian-obat', 'detailPemberianObat');
    });

    Route::controller(ISServiceController::class)->group(function () {
        Route::get('is/service/jadwal/poli', 'jadwalPoli');
        Route::post('is/service/antrian/periksa', 'antrianPeriksa');
        Route::post('is/service/antrian/skip', 'antrianSkip');
    });

    Route::get('/master/poliklinik', action: [PoliklinikController::class, 'index']);
    Route::post('/master/poliklinik', action: [PoliklinikController::class, 'store']);
    Route::get('/master/dokter', action: [DokterController::class, 'index']);
    Route::post('/master/dokter', action: [DokterController::class, 'store']);
});

Route::controller(JknApiAntrolController::class)->group(function () {
    Route::get('/api-antrol/ref/poli', 'refPoli');
    Route::get('/api-antrol/ref/dokter', 'refDokter');
    Route::post('/api-antrol/ref/jadwal-dokter', 'refJadwalDokter');
    Route::get('/api-antrol/ref/poli-fp', 'refPoliFP');
    Route::post('/api-antrol/ref/pasien-fp', 'refPasienFP');
    Route::post('/api-antrol/antrian-tanggal', 'antrianPerTgl');
    Route::post('/api-antrol/antrian-nobooking', 'antrianPerKbo');
    Route::get('/api-antrol/antrian-aktif', 'antrianAktif');
    Route::post('/api-antrol/antrian-nobooking-detail', 'antrianAktifDetail');
    Route::post('/api-antrol/antrian/taskid', 'listTaskid');
    Route::post('/api-antrol/antrian/updatewaktu', 'updateWaktuAntrian');
    Route::post('/api-antrol/daftar/antrian', 'daftarAntrian');
    Route::post('/api-antrol/daftar/antrian/farmasi', 'daftarAntrianFarmasi');
    Route::post('/api-antrol/batal/antrian', 'batalAntrean');
});
