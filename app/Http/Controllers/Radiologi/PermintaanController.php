<?php

namespace App\Http\Controllers\Radiologi;

use App\Helpers\AuthHelper;
use App\Http\Controllers\Controller;
use App\Models\PermintaanRadiologi;

class PermintaanController extends Controller
{
    public function getData(){
        $request = request();
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
        if (!$tanggalAwal || $tanggalAwal == "") {
            return response()->json([
                'code' => 201,
                'message' => 'Tanggal permintaan kontrol harus diisi!',
            ], 201);
        }
        if (strtotime($tanggalAwal) > strtotime($tanggalAkhir)) {
            return response()->json([
                'code' => 201,
                'message' => 'Tanggal permintaan kontrol harus diisi!',
            ], 201);
        }
        $data = PermintaanRadiologi::with(['register'=>function($child){
            $child->with(['pasien_compact','doctor','satusehatlokasi','policlinic']);
        },'encounter','request','pemeriksaan','perujuk','imaging'])
        ->whereBetween('tgl_permintaan',[date('Y-m-d',strtotime($tanggalAwal)),date('Y-m-d',strtotime($tanggalAkhir))])
        ->get();

        return response()->json([
            'code' => ($data) ? 200 : 204,
            'message' => ($data) ? 'Ok' : 'Data tidak ditemukan!',
            'data' => ($data) ? $data : null,
            'token' => AuthHelper::genToken(),
        ]);
    }
    public function getEncounter($no_rawat=''){
        
    }
}