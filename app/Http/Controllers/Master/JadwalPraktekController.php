<?php

namespace App\Http\Controllers\Master;

use App\Helpers\AuthHelper;
use App\Helpers\BPer;
use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use Illuminate\Http\Request;

class JadwalPraktekController extends Controller
{
    public function index()
    {
        $data = Jadwal::all();
        return response()->json([
            'code' => 200, 
            'data' => $data,
            'message' => 'Success',
            'token' => AuthHelper::genToken(),
        ]);
    }
    public function getByDate($date='now'){
        $data = Jadwal::
        with(['poli','dokter'])->
        where('hari_kerja','=',BPer::tebakHari(date('Y-m-d',strtotime($date))))->
        orderBy('jam_mulai','asc')->
        orderBy('jam_selesai','asc')->
        get();
        return response()->json([
            'code' => 200, 
            'data' => $data,
            'message' => 'Success',
            'token' => AuthHelper::genToken(),
        ]);
    }
    public function store(Request $request)
    {
        $model = new Jadwal();
        $model->save();
        return response()->json([
            'code' => 200, 
            'data' => $model,
            'message' => 'Success',
            'token' => AuthHelper::genToken(),
        ]);
    }
}
