<?php

namespace App\Http\Controllers;

use App\Models\ReferensiMobilejknBpjs;
use DB;
use Illuminate\Http\Request;

class Auth extends Controller
{
    public function index() {
        $res = [
            'code'=>200,
            'status'=>"ok",
            'data'=>[]
        ];
        return response()->json($res);
    }
    function test() {
        $last=ReferensiMobilejknBpjs::where('nobooking','like',date('Ymd').'%')->orderBy('nobooking','desc')->first();
        $last?$nobooking=$last->nobooking+1: $nobooking=date('Ymd').'000001';
        $item = new ReferensiMobilejknBpjs();
        $item->nobooking = $nobooking;
        $item->no_rawat = date('Y/m/d')."/000001";
        $item->nomorkartu = "1234567890123";
        $item->nik = "3216549870123456";
        $item->nohp = "081234567890";
        $item->kodepoli = "PD001";
        $item->pasienbaru = "1";
        $item->norm = "123456";
        $item->tanggalperiksa = date('Y-m-d');
        $item->kodedokter = "D001";
        $item->jampraktek = "08:00";
        $item->jeniskunjungan = "3 (Kontrol)";
        $item->nomorreferensi = "0";
        $item->nomorantrean = "PK-001";
        $item->angkaantrean = "001";
        $item->estimasidilayani =  strtotime('+5 minutes')*1000;
        $item->sisakuotajkn = "10";
        $item->kuotajkn = "10";
        $item->sisakuotanonjkn = "5";
        $item->kuotanonjkn = "5";
        $item->status = "Belum";
        $item->validasi = '0000-00-00 00:00:00';
        $item->statuskirim = "Belum";
        $item->save();
        // Update validasi field after initial save
        // ReferensiMobilejknBpjs::where('nobooking', $nobooking)->update(['validasi' => '0000-00-00 00:00:00']);
        
        // $item->refresh();
        $res = [
            'code'=>200,
            'status'=>"ok",
            'data'=>[]
        ];
        return response()->json($res);
    }
}
