<?php

namespace App\Http\Controllers\IntegratedService;

use App\Helpers\AuthHelper;
use App\Helpers\BPer;
use App\Http\Controllers\Controller;
use App\Models\IoAntrian;
use App\Models\Jadwal;
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

    public function jadwalPoli()
    {
        $hari = BPer::tebakHari(date('Y-m-d'));

        $caridata = Jadwal::where('hari_kerja', $hari)->get();

        $data = [];
        $dokterCache = [];
        $poliCache = [];

        foreach ($caridata as $v) {
            if (!isset($dokterCache[$v->kd_dokter])) {
                $dokterCache[$v->kd_dokter] =
                    $v->dokter ? $v->dokter->only(['kd_dokter', 'nm_dokter']) : null;
            }

            if (!isset($poliCache[$v->kd_poli])) {
                $poliCache[$v->kd_poli] =
                    $v->poli ? $v->poli->only(['kd_poli', 'nm_poli']) : null;
            }

            $data[] = [
                'hari' => $v->hari_kerja,
                'dokter' => $dokterCache[$v->kd_dokter],
                'poli' => $poliCache[$v->kd_poli],
                'tanggal' => date('Y-m-d'),
                'jam' => '(' . $v->jam_mulai . '-' . $v->jam_selesai . ')'
            ];
        }

        return response()->json([
            'code' => 200,
            'message' => 'ok',
            'counter' => $caridata->count(),
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    public function antrianPeriksa(Request $request)
    {
        $rules = [
            'dokter' => 'required|string',
            'poli'   => 'required|string',
            'tanggal'   => 'required|string',
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

        $tgl = $request->tanggal;
        $dokter = $request->dokter;
        $poli = $request->poli;

        $cari = RegPeriksaModel::where('tgl_registrasi', $tgl)
            ->where('kd_dokter', $dokter)
            ->where('kd_poli', $poli)
            ->join('io_antrian', 'io_antrian.no_referensi', '=', 'reg_periksa.no_rawat')
            ->where('status_panggil', '0')
            // ->where('status_antrian', '0')
            ->get();

        if ($cari->isEmpty()) {
            return response()->json([
                'code' => 204,
                'message' => 'Tidak ada antrian pemeriksaan'
            ]);
        }

        $data = [
            ' '
        ];

        // $data = [];

        // foreach ($cari as $v) {
        //     $data[] = [
        //         ''
        //     ];
        // }

        // return response()->json([
        //     'code' => 200,
        //     'message' => 'Ok',
        //     'data' => $data,
        //     'token' => AuthHelper::genToken(),
        // ]);
    }
}
