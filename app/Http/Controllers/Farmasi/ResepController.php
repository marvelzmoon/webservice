<?php

namespace App\Http\Controllers\Farmasi;

use App\Helpers\AuthHelper;
use App\Helpers\BPer;
use App\Http\Controllers\Controller;
use App\Models\IoAntrianTaskid;
use App\Models\IoReferensiFarmasi;
use App\Models\ResepObat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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

        $find = ResepObat::whereBetween('tgl_perawatan', [$tglAkhir, $tglAwal])
                    ->where('resep_obat.status', 'ralan')
                    ->join('io_referensi_farmasi', 'resep_obat.no_resep', '=', 'io_referensi_farmasi.no_resep')
                    ->where('calltime', null)
                    ->orderBy('resep_obat.no_resep', 'ASC')
                    ->get();

        if ($find && $find->isEmpty()) {
            return response()->json([
                'code'    => 201,
                'message' => 'Data resep kosong'
            ]);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Data resep ditemukan',
            'data'    => $find,
            'token'   => AuthHelper::genToken()
        ]);
    }

    public function resepSelesai(Request $request)
    {
        $rules = [
            'noresep'   => 'required|string',
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

        $noresep = $request->noresep;
        $datetimenow = date('Y-m-d H:i:s');
        $msgApi = null;

        $find = IoReferensiFarmasi::where('no_resep', $noresep)->first();

        if (!$find) {
            return response()->json([
                'code'    => 404,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        if (!is_null($find->calltime)) {
            return response()->json([
                'code'    => 208,
                'message' => 'Resep obat sudah selesai dibuat!'
            ]);
        }

        if ($find->status == 'Tidak ada resep') {
            IoReferensiFarmasi::where('no_resep', $noresep)->update([
                'calltime' => $datetimenow,
            ]);
        }

        if ($find->status != 'Tidak ada resep') {
            IoReferensiFarmasi::where('no_resep', $noresep)->update([
                'calltime'  => $datetimenow,
                'status'    => 'Sudah'
            ]);

            $taskid = IoAntrianTaskid::where('nobooking', $find->kodebooking)->first();
            
            if ($taskid) {
                $input = [
                    'kodebooking' => $taskid->nobooking,
                    'taskid' => 6,
                    'waktu' => strtotime($datetimenow) * 1000
                ];

                IoAntrianTaskid::where('nobooking', $taskid->nobooking)->update(['taskid_7' => $datetimenow]);

                $sendTaskid = new Request($input);

                $apiBPJSSend = App::call(
                    'App\Http\Controllers\Jkn\JknApiAntrolController@updateWaktuAntrian',
                    ['request' => $sendTaskid]
                );

                if ($apiBPJSSend instanceof JsonResponse) {
                    $dResponse = $apiBPJSSend->getData(true);

                    if (isset($dResponse['metadata'])) {
                        $code = $dResponse['metadata']['code'];
                        $message = $dResponse['metadata']['message'];
                    } else {
                        $code = $dResponse['code'];
                        $message = $dResponse['message'];
                    }

                    if ($code == 200) {
                        date_default_timezone_set('Asia/Jakarta');
                        IoAntrianTaskid::where('nobooking', $taskid->nobooking)->update(['taskid_7_send' => date('Y-m-d H:i:s')]);

                        $msgApi = 'TASKID 7: Sukses mengirim waktu antrian ke BPJS';
                    }

                    $msgApi = 'TASKID 7: ' . $message;
                }
            }
        }
        
        return response()->json([
            'code'      => 200,
            'message'   => 'Resep ' . $noresep . ' selesai dibuat! ' . $msgApi
        ]);
    }
}
