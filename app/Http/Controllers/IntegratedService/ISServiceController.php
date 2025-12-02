<?php

namespace App\Http\Controllers\IntegratedService;

use App\Http\Controllers\Controller;
use App\Models\IoAntrian;
use App\Models\ReferensiMobilejknBpjs;
use App\Models\RegPeriksaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ISServiceController extends Controller
{
    public function index()
    {
        return 1;
    }

    public function poliAntrianPost(Request $request)
    {
        $rules = [
            'norawat' => 'required|string',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'string'   => ':attribute harus berupa string',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $noRawat = $request->norawat;
        $cari = RegPeriksaModel::where('no_rawat', $noRawat)->first();

        if (!$cari) {
            return response()->json([
                'code' => 204,
                'message' => 'Data registrasi tidak ditemukan'
            ]);
        }

        $nobooking = ReferensiMobilejknBpjs::where('no_rawat', $noRawat)->first();

        if (!$nobooking) {
            return response()->json([
                'code' => 204,
                'message' => 'Belum didaftarkan antrian'
            ]);
        }

        $cek = IoAntrian::find($noRawat);

        if ($cek) {
            return response()->json([
                'code' => 204,
                'message' => 'Antrian sudah ada'
            ]);
        }

        $data = [
            'no_referensi' => $noRawat,
            'no_antrian' => $nobooking->nomorantrean,
            'status_panggil' => 0,
            'status_antrian' => 0,
            'calltime' => null,
            'status_pasien' => 0
        ];

        return $data;
    }
}
