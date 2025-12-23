<?php

namespace App\Http\Controllers\Monev;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class MonevController extends Controller
{
    public function index() {
        return view("pages/monev/home");

        // $result = DB::connection('second')->select('SELECT * FROM io_jenis_antrian');

        // return $result;
    }

    public function antrolTerdaftar() {
        return view("pages/monev/antrol_terdaftar");
    }

    public function antrolTerdaftarApi(Request $request) {
        $data = [
            'tanggal' => $request->tgl,
        ];

        $apiSend = new Request($data);

        $apiResponse = App::call(
            'App\Http\Controllers\Jkn\JknApiAntrolController@antrianPerTgl',
            ['request' => $apiSend]
        );

        if ($apiResponse instanceof JsonResponse) {
            $decodeResponse = $apiResponse->getData(true);

            // if ($decodeResponse['metadata']['code'] == 200) {
            //     return response()->json([
            //         'code' => 200,
            //         'message' => 'Taskid ' . $request->taskid . ' berhasil terkirim',
            //         'token'   => AuthHelper::genToken()
            //     ]);
            // }

            return response()->json($decodeResponse);
        }

        return response()->json($apiResponse);
    }

    public function antrolTerdaftarBatal(Request $request) {
        $data = [
            'kodebooking' => $request->kode,
            'keterangan' => $request->ket
        ];

        $apiSend = new Request($data);

        $apiResponse = App::call(
            'App\Http\Controllers\Jkn\JknApiAntrolController@batalAntrean',
            ['request' => $apiSend]
        );

        if ($apiResponse instanceof JsonResponse) {
            $decodeResponse = $apiResponse->getData(true);

            // Cek apakah metadata & code ada
            if (isset($decodeResponse['code']) && $decodeResponse['code'] == 200) {
                if (preg_match("/^\d{4}\/\d{2}\/\d{2}\/\d{6}$/", $data['kodebooking'])) {
                    $find = DB::connection('second')
                                    ->table('reg_periksa')
                                    ->select('no_rawat', 'stts')
                                    ->where('no_rawat', $data['kodebooking'])
                                    ->first();

                    DB::connection("second")
                            ->table("reg_periksa")
                            ->where("no_rawat", $find->no_rawat)
                            ->update([
                                "stts" => "Batal"
                            ]);
                } else {
                    $find = DB::connection('second')
                                    ->table('referensi_mobilejkn_bpjs')
                                    ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'referensi_mobilejkn_bpjs.no_rawat')
                                    ->select(
                                        'referensi_mobilejkn_bpjs.nobooking',
                                        'reg_periksa.no_rawat',
                                        'reg_periksa.stts'
                                    )
                                    ->where('referensi_mobilejkn_bpjs.nobooking', $data['kodebooking'])
                                    ->first();

                    DB::connection("second")
                            ->table("reg_periksa")
                            ->where("no_rawat", $find->no_rawat)
                            ->update([
                                "stts" => "Batal"
                            ]);

                    DB::connection("second")
                            ->table("referensi_mobilejkn_bpjs")
                            ->where("nobooking", $find->nobooking)
                            ->update([
                                "status" => "Batal",
                                "validasi" => now(),
                                "statuskirim" => "Sudah"
                            ]);
                }

                return response()->json([
                    "code" => 200,
                    "message" => $decodeResponse["message"]
                ]);
            }

            return response()->json([
                "code" => 200,
                "message" => "Antrian " . $data['kodebooking'] . " gagal dibatalkan. Message : " . $decodeResponse["message"]
            ]);
        }

        return response()->json($apiResponse);
    }
}
