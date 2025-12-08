<?php

namespace App\Http\Controllers\IntegratedService;

use App\Helpers\AuthHelper;
use App\Helpers\BPer;
use App\Http\Controllers\Controller;
use App\Models\IoAntrian;
use App\Models\Jadwal;
use App\Models\Pasien;
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
            ->where('kd_dokter', $dokter)
            ->where('kd_poli', $poli)
            ->get();

        if ($cari->isEmpty()) {
            return response()->json([
                'code' => 204,
                'message' => 'Tidak ada antrian pemeriksaan'
            ]);
        }

        $dataAntrian = IoAntrian::where('no_referensi', 'LIKE', $prefixTgl . '%')
            ->where('no_antrian', 'LIKE', $poli . '-%')
            ->get();

        $antrianPanggil = IoAntrian::where('no_referensi', 'LIKE', $prefixTgl . '%')
            ->where('no_antrian', 'LIKE', $poli . '-%')
            ->where('status_pasien', '1')  // sedang dipanggil
            ->orderBy('no_antrian', 'ASC')
            ->first();

        $lastCall = IoAntrian::where('no_referensi', 'LIKE', $prefixTgl . '%')
            ->where('no_antrian', 'LIKE', $poli . '-%')
            ->orderBy('calltime', 'DESC')
            ->value('no_antrian');

        if ($lastCall) {
            $exp = explode('-', $lastCall);

            // $nextCall = $cari[0]->kd_poli . '-' . ($exp[1] < $cari->count()) ? sprintf('%03d', $exp[1] + 1) : null;
            if ($exp[1] < $cari->count()) {
                $nextCall = $cari[0]->kd_poli . '-' . sprintf('%03d', $exp[1] + 1);
            } else {
                $nextCall = null;
            }
        } else {
            $nextCall = $cari[0]->kd_poli . '-' . $cari[0]->no_reg;
        }

        return $nextCall;

        $data = [];
        $counter = [
            'total' => $cari->count(),
            'sisa' => ($cari->count()) - ($dataAntrian->count()),
            'antrian_panggil' => $antrianPanggil ? $antrianPanggil->no_antrian : null,
            'antrian_lanjut' => $nextCall,
        ];

        foreach ($cari as $v) {
            // $cariRef = ReferensiMobilejknBpjs::where('no_rawat', $v->no_rawat)->where('status', '!=', 'Batal')->first();
            // $noref = ($cariRef) ? $cariRef->nobooking : $v->no_referensi;
            // $noref = $v->no_referensi;
            // $cekAktif = IoAntrian::where('no_referensi', $noref)->first();

            $data[] = [
                'no_referensi' => $v->no_rawat,
                'pasien' => Pasien::where('no_rkm_medis', $v->no_rkm_medis)->first(['no_rkm_medis', 'nm_pasien']),
                'no_antrian' => $v->kd_poli . '-' . $v->no_reg,
                // 'btn_panggil' => ($cekAktif->status_panggil == '1' || $cekAktif->status_antrian == '1' || $cekAktif->status_pasien == '2') ? false : true,
            ];
        }

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => [
                'counter' => $counter,
                'list' => $data,
            ],
            'token' => AuthHelper::genToken(),
        ]);
    }
}
