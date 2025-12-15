<?php

namespace App\Http\Controllers\Master;

use App\Helpers\AuthHelper;
use App\Http\Controllers\Controller;
use App\Models\Dokter;
use Illuminate\Http\Request;

class DokterController extends Controller
{
    public function index()
    {
        $data = Dokter::where('status','=','1')->get();
        return response()->json([
            'code' => 200, 
            'data' => $data,
            'message' => 'Success',
            'token' => AuthHelper::genToken(),
        ]);
    }
    public function store(Request $request)
    {
        $model = new Dokter();
        $model->kd_dokter = $request->input('kd_poli');
        $model->nm_dokter = $request->input('nm_poli');
        $model->save();
        return response()->json([
            'code' => 200, 
            'data' => $model,
            'message' => 'Success',
            'token' => AuthHelper::genToken(),
        ]);
    }
}
