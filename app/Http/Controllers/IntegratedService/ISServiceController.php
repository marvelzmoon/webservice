<?php

namespace App\Http\Controllers\IntegratedService;

use App\Helpers\AuthHelper;
use App\Helpers\BPer;
use App\Http\Controllers\Controller;
use App\Models\IoAntrian;
use App\Models\IoAntrianPanggil;
use App\Models\IoDashboardDetail;
use App\Models\Jadwal;
use App\Models\RegPeriksaModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ISServiceController extends Controller
{
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
            'tanggal'   => 'required|string',
            'poli'   => 'required|string',
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
        $prefixTgl = str_replace('-', '/', $tgl);
        $dokter = $request->dokter;
        $poli = $request->poli;

        $cari = RegPeriksaModel::where('tgl_registrasi', $tgl)
            ->join('referensi_mobilejkn_bpjs', 'referensi_mobilejkn_bpjs.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('io_antrian', 'io_antrian.no_referensi', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->where('kd_dokter', $dokter)
            ->where('kd_poli', $poli)
            ->select(
                'reg_periksa.no_rawat',
                'no_antrian',
                'pasien.no_rkm_medis',
                'pasien.nm_pasien',
                'status_panggil',
                'status_antrian',
                'status_pasien',
                'order',
                'kd_poli',
                'no_reg'
            )
            ->orderBy('status_antrian', 'asc')
            ->orderBy('order', 'asc')
            ->get();

        // return $cari;

        $callFirst = $cari->where('status_pasien', '!=', 2)->first();
        $callProses = $cari->where('status_pasien', '!=', 2)->where('status_panggil', 1)->first();

        // return $callFirst;

        if ($callProses) {
            $exp = explode('-', $callProses->no_antrian);

            if ($exp[1] < $cari->count()) {
                $nextCall = $callProses->kd_poli . '-' . sprintf('%03d', $exp[1] + 1);
            } else {
                $nextCall = null;
            }
        } else {
            if (!$callFirst) {
                $nextCall = null;
            } else {
                $nextCall = $callFirst->kd_poli . '-' . $callFirst->no_reg;
            }
        }

        $viewData = $cari->where('status_panggil', 0);

        $data = [];
        $head = [
            'total' => $cari->count(),
            'sisa' => $cari->count() -  $cari->where('status_pasien', 2)->count(),
            'call' => (isset($callProses)) ? $callProses->kd_poli . '-' . $callProses->no_reg : null,
            'nextCall' => $nextCall,
        ];

        $disableButton = IoAntrian::where('no_referensi', 'LIKE', $prefixTgl . '%')
            ->where('no_antrian', 'LIKE', $poli . '-%')
            ->where('status_panggil', 1)
            ->count();

        foreach ($viewData as $v) {
            $data[] = [
                'nobooking' => $v->nobooking,
                'no_referensi' => $v->no_rawat,
                'no_antrian' => $v->no_antrian,
                'no_rkm_medis' => $v->no_rkm_medis,
                'nama' => $v->nm_pasien,
                'button' => ($disableButton > 0) ? false : true,
            ];
        }

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => [
                'head' => $head,
                'list' => [
                    'count' => $viewData->count(),
                    'data' => $data,
                ]
            ],
            'token' => AuthHelper::genToken()
        ]);
    }

    public function antrianSkip(Request $request)
    {
        $rules = [
            'noreferensi'   => 'required|string',
            'noantrian'     => 'required|string',
            'skip'          => 'required|int'
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

        $ref = $request->noreferensi;

        if (str_contains($ref, '/')) {
            $noref = Str::beforeLast($ref, '/');
        } else {
            $noref = substr($ref, 0, 8);
        }

        $exp = explode('-', $request->noantrian);

        $cari = IoAntrian::where('no_referensi', 'like', $noref . '%')
            ->where('no_antrian', 'like', $exp[0] . '-%')
            ->get();

        $nowOrder = $cari->where('no_referensi', $ref)->first();
        $lastOrder = $cari->last();
        $skiped = $lastOrder->order + $request->skip;

        IoAntrian::where('no_referensi', $ref)->update(['order' => $skiped]);

        return response()->json([
            'code' => 200,
            'message' => 'Antrian berhasil di lewati!',
            'token' => AuthHelper::genToken()
        ]);
    }

    public function antrianPanggil(Request $request)
    {
        $rules = [
            'noreferensi'   => 'required|string',
            'noantrian'     => 'required|string',
        ];

        $messages = [
            'required'  => ':attribute tidak boleh kosong',
            'string'    => ':attribute harus berupa string',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $ref = $request->noreferensi;
        $antrian = $request->noantrian;
        $exp = explode('-', $antrian);

        if (str_contains($ref, '/')) {
            $noref = Str::beforeLast($ref, '/');
        } else {
            $noref = Carbon::createFromFormat('Ymd', substr($ref, 0, 8))->format('Y/m/d');
        }

        $cari = RegPeriksaModel::join('io_antrian', 'io_antrian.no_referensi', '=', 'reg_periksa.no_rawat')
            ->where('no_referensi', 'like', $noref . '%')
            ->where('kd_poli', $exp[0])
            ->where('no_antrian', $antrian)
            ->first();

        if (!$cari) {
            return response()->json([
                'code' => 204,
                'message' => 'Data Antrian tidak ditemukan'
            ]);
        }

        $dashboard = IoDashboardDetail::where('ddash_poli', $cari->kd_poli)
            ->join('io_dashboard', 'io_dashboard.dash_id', '=', 'io_dashboard_detail.ddash_parent')
            ->first();
        $tempPanggil = IoAntrianPanggil::find($ref);

        if ($tempPanggil) {
            return response()->json([
                'code' => 204,
                'message' => 'Pasien sedang proses di panggil'
            ]);
        }

        // set status panggilan menjadi 0 semua
        IoAntrian::where('no_referensi', 'like', $noref . '%')
            ->where('no_antrian', 'like', $cari->kd_poli . '-%')
            ->update(['status_panggil' => 0]);

        $callPanggil = new IoAntrianPanggil();
        $callPanggil->no_referensi = $ref;
        $callPanggil->dashboard_id = $dashboard->dash_id;
        $callPanggil->type = $dashboard->dash_type;
        $callPanggil->counter = null;
        $callPanggil->save();

        if ($callPanggil) {
            IoAntrian::where('no_referensi', $ref)->update(['status_panggil' => 1]);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Antrian sedang dipanggil',
            'token' => AuthHelper::genToken()
        ]);
    }

    public function antrianMonitorView() {}

    public function antrianMonitorPanggil()
    {
        $cari = IoAntrianPanggil::join('io_antrian', 'io_antrian.no_referensi', '=', 'io_antrian_panggil.no_referensi')->first();

        if (!$cari) {
            return response()->json([
                'code' => 200,
                'message' => 'Data kosong'
            ]);
        }

        $data = [
            ''
        ];

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken()
        ]);
    }
}
