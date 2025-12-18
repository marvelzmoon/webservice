<?php

namespace App\Http\Controllers\Service;

use App\Helpers\AuthHelper;
use App\Http\Controllers\Controller;
use App\Models\IoAntrian;
use App\Models\IoAntrianFarmasi;
use App\Models\RegPeriksaModel;
use App\Models\ResepObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceFarmasiController extends Controller
{
    public function antrianTambah() {
        $date = "2025-12-13";
        // $date = date("Y-m-d");

        $dataRef = RegPeriksaModel::where("tgl_registrasi", $date)
        ->join("io_antrian", "io_antrian.no_referensi", "=", "reg_periksa.no_rawat")
        // ->join("resep_obat", "resep_obat.no_rawat", "=", "reg_periksa.no_rawat")
        // ->where("status", "ralan")
        ->where("kd_poli", "!=", "IGDK")
        // ->where("status_pasien", "2")
        // ->where("status_send", "0")
        // ->where("reg_periksa.no_rawat", "2025/12/13/000025")
        // ->get();
        ->first();

        return $dataRef;

        // $cekAntrianFarmasi = IoAntrianFarmasi::where("no_referensi", $dataRef->no_rawat)->first();

        // if ($cekAntrianFarmasi) {
        //     return response()->json([
        //         "code" => 204,
        //         "message" => "Antrian Farmasi sudah ada"
        //     ]);
        // }

        // $cekResepObat = ResepObat::where("no_rawat", $dataRef->no_rawat)->exists();
        // $cekResepDokter = ResepObat::where("no_rawat", $dataRef->no_rawat)
        //                                 ->join("resep_dokter", "resep_dokter.no_resep", "=", "resep_obat.no_resep")
        //                                 ->exists();
        // $cekResepDokterRacikan = ResepObat::where("no_rawat", $dataRef->no_rawat)
        //                                 ->join("resep_dokter_racikan", "resep_dokter_racikan.no_resep", "=", "resep_obat.no_resep")
        //                                 ->exists();

        // // Tipe Antrian
        // $kategori = DB::table('resep_obat as ro')
        //                     ->select(
        //                         'ro.no_resep',
        //                         'ro.no_rawat',
        //                         'ro.tgl_perawatan',
        //                         'ro.jam',
        //                         DB::raw("
        //                             CASE
        //                                 WHEN EXISTS (
        //                                     SELECT 1 
        //                                     FROM resep_dokter_racikan rdr 
        //                                     WHERE rdr.no_resep = ro.no_resep
        //                                 ) THEN 'Farmasi Racik'
        //                                 WHEN EXISTS (
        //                                     SELECT 1 
        //                                     FROM resep_dokter rd 
        //                                     WHERE rd.no_resep = ro.no_resep
        //                                 ) THEN 'Farmasi Non Racik'
        //                                 ELSE 'tidak ada detail'
        //                             END AS jenis_resep
        //                         ")
        //                     )
        //                     ->where('ro.no_rawat', $dataRef->no_rawat)
        //                     ->first();

        // return [$cekResepObat, $cekResepDokter, $cekResepDokterRacikan, $kategori];
    }
}
