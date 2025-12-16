<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\ResepObat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResepController extends Controller
{
    public function resepGetdata(Request $request)
    {
        $rules = [
            'tanggalawal'   => 'required|string',
            'tanggalakhir'   => 'required|string',
        ];

        $messages = [
            'required'  => ':attribute tidak boleh kosong',
            'string'    => ':attribute harus berupa string',
            'int'       => ':attribute harus berupa integer',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $tglAwal = $request->tanggalawal;
        $tglAkhir = $request->tanggalakhir;

        $find = ResepObat::whereBetween('tgl_perawatan', [$tglAkhir, $tglAwal])->get();

        return $find;
    }
}
