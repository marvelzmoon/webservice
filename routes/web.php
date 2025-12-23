<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Jkn\JknApiAntrolController;
use App\Http\Controllers\Jkn\JknSuratkontrolController;
use App\Http\Controllers\Jkn\JknTaskidController;
use App\Http\Controllers\Master\JadwalPraktekController;
use App\Http\Controllers\Master\PasienController;
use App\Http\Controllers\Master\DokterController;
use App\Http\Controllers\Master\PoliklinikController;
use App\Http\Controllers\Master\ReferensiController;
use App\Http\Controllers\Radiologi\PermintaanController;
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

    Route::prefix('jkn')->group(function () {
        Route::controller(JknSuratkontrolController::class)->group(function () {
            Route::post('/surkon/getdata', 'getdata');
        });

        Route::prefix('taskid')->group(function () {
            Route::controller(JknTaskidController::class)->group(function () {
                Route::post('/post', 'post');
                Route::post('/data', 'getdata');
                Route::post('/send', 'send');
            });
        });
    });

    Route::prefix('registrasi')->group(function () {
        Route::controller(RegistrasiController::class)->group(function () {
            Route::post('/getdata', 'getdata');
            Route::post('/post', 'post');
            Route::post('/add-antrol', 'addantrian');
            Route::post('/batal-periksa', 'batalPeriksa');
            Route::post('/add-antrol-farmasi', 'addAntrianFarmasi');
        });
    });

    Route::prefix('ref')->group(function () {
        Route::controller(ReferensiController::class)->group(function () {
            Route::get('/penjab', 'penjab');
            Route::post('/kelurahan', 'kelurahan');
            Route::post('/kecamatan', 'kecamatan');
            Route::post('/kabupaten', 'kabupaten');
            Route::get('/perusahaan-pasien', 'perusahaanpasien');
            Route::get('/suku-bangsa', 'sukubangsa');
            Route::get('/bahasa-pasien', 'bahasapasien');
            Route::get('/cacat-fisik', 'cacatfisik');
            Route::get('/propinsi', 'propinsi');
            Route::get('/provinsi', 'provinsi');
            Route::post('/ambil-wilayah', 'getWilayah');
        });
    });

    Route::prefix('pasien')->group(function () {
        Route::controller(PasienController::class)->group(function () {
            Route::get('/getdata', 'getdata');
            Route::post('/search', 'searchPasien');
            Route::post('/create', 'createPasien');
            Route::post('/destroy', 'destroy');
        });
    });

    Route::prefix('radiologi')->group(function () {
        Route::controller(PermintaanController::class)->group(function () {
            Route::post('/permintaan', 'getdata');
        });
    });

    Route::get('/master/poliklinik', action: [PoliklinikController::class, 'index']);
    Route::post('/master/poliklinik', action: [PoliklinikController::class, 'store']);
    Route::get('/master/dokter', action: [DokterController::class, 'index']);
    Route::post('/master/dokter', action: [DokterController::class, 'store']);
});

Route::prefix('api-antrol')->group(function () {
    Route::controller(JknApiAntrolController::class)->group(function () {
        Route::get('/ref/poli', 'refPoli');
        Route::get('/ref/dokter', 'refDokter');
        Route::post('/ref/jadwal-dokter', 'refJadwalDokter');
        Route::get('/ref/poli-fp', 'refPoliFP');
        Route::post('/ref/pasien-fp', 'refPasienFP');
        Route::post('/antrian-tanggal', 'antrianPerTgl');
        Route::post('/antrian-nobooking', 'antrianPerKbo');
        Route::get('/antrian-aktif', 'antrianAktif');
        Route::post('/antrian-nobooking-detail', 'antrianAktifDetail');
        Route::post('/antrian/taskid', 'listTaskid');
        Route::post('/antrian/updatewaktu', 'updateWaktuAntrian');
        Route::post('/daftar/antrian', 'daftarAntrian');
        Route::post('/daftar/antrian/farmasi', 'daftarAntrianFarmasi');
        Route::post('/batal/antrian', 'batalAntrean');
    });
});
    Route::get('/master/jadwal-praktek/tanggal', action: [JadwalPraktekController::class, 'getByDate']);
    Route::get('/master/jadwal-praktek/tanggal/{date}', action: [JadwalPraktekController::class, 'getByDate']);
    Route::get('/radiologi/permintaan', action: [PermintaanController::class, 'getEncounter']);





use App\Http\Controllers\SatuSehat\AuthController as SatuSehatAuthController;
use App\Http\Controllers\SatuSehat\PatientController as SatuSehatPatientController;
use App\Http\Controllers\SatuSehat\PractitionerController as SatuSehatPractitionerController;
use App\Http\Controllers\SatuSehat\LokasiRalanController;
use App\Http\Controllers\SatuSehat\EncounterController;
use App\Http\Controllers\SatuSehat\RadiologiRequestController;
use App\Http\Controllers\SatuSehat\ImagingStudiesController;
use App\Http\Controllers\Radiologi\WorklistController;

    Route::get('/phpinfo', function () {phpinfo();});
    Route::get('/satu-sehat/auth', action: [SatuSehatAuthController::class, 'auth']);
    Route::get('/satu-sehat/patient/{nik}', action: [SatuSehatPatientController::class, 'getOne']);
    Route::get('/satu-sehat/practitioner/{nik}', action: [SatuSehatPractitionerController::class, 'getOne']);
    Route::get('/satu-sehat/lokasi-ralan/{nik}', action: [LokasiRalanController::class, 'getOne']);
    Route::post('/satu-sehat/encounter', action: [EncounterController::class, 'store']);
    Route::post('/satu-sehat/radiologi/service-request', action: [RadiologiRequestController::class, 'store']);
    Route::put('/satu-sehat/radiologi/service-request', action: [RadiologiRequestController::class, 'update']);
    Route::post('/satu-sehat/radiologi/imaging-study/upload', action: [ImagingStudiesController::class, 'upload']);
    Route::post('/satu-sehat/radiologi/imaging-study/upload/{acsn}', action: [ImagingStudiesController::class, 'upload']);
    Route::post('/satu-sehat/radiologi/imaging-study/send-to-modality/{modality}', action: [ImagingStudiesController::class, 'sendToModality']);
    Route::get('/satu-sehat/radiologi/imaging-study/retrieve/{acsn}', action: [ImagingStudiesController::class, 'getCloudData']);
    
    Route::get('/radiologi/worklist/generate', action: [WorklistController::class, 'generate']);
    Route::get('/radiologi/worklist/generate/{date}', action: [WorklistController::class, 'generate']);
