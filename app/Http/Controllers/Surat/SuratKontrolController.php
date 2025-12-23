<?php

namespace App\Http\Controllers\Surat;

use App\Helpers\AuthHelper;
use App\Http\Controllers\Controller;
use App\Models\IoSuratKontrol;
use App\Models\Poliklinik;
use Illuminate\Http\Request;

class SuratKontrolController extends Controller
{
    public function getData(){
        $request = request();
        $tanggal_surat = $request->tanggal_surat;
        $tanggal_kontrol = $request->tanggal_kontrol;
        if(!$tanggal_surat && !$tanggal_kontrol){
            return response()->json([
                'code' => 201,
                'message' => 'Tanggal kontrol atau tanggl surat harus diisi!',
            ], 200);
        }
        $data = IoSuratKontrol::with('bridgingSuratKontrol');
        ($tanggal_surat)?$data = $data->where('created_at','like', date('Y-m-d', strtotime($tanggal_surat)).'%'): null;
        ($tanggal_kontrol)?$data = $data->where('created_at','like', date('Y-m-d', strtotime($tanggal_kontrol)).'%'): null;
        $data = $data->get();
        return response()->json([
            'code' => ($data) ? 200 : 204,
            'message' => ($data) ? 'Ok' : 'Data tidak ditemukan!',
            'data' => ($data) ? $data : null,
            'token' => AuthHelper::genToken(),
        ]);
    }
}
