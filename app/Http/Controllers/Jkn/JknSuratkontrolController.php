<?php

namespace App\Http\Controllers\Jkn;

use App\Helpers\AuthHelper;
use App\Helpers\BPer;
use App\Http\Controllers\Controller;
use App\Models\BridgingSuratKontrolBpjs;
use Illuminate\Http\Request;

class JknSuratkontrolController extends Controller
{
    public function getdata(Request $request)
    {
        $tanggal = $request->tanggal;

        if (!$tanggal || $tanggal == "") {
            return response()->json([
                'code' => 201,
                'message' => 'Tanggal rencana kontrol harus diisi!',
            ], 200);
        }

        if (!BPer::validTanggal($tanggal)) {
            return response()->json([
                'code' => 201,
                'message' => 'Format tanggal harus Y-m-d',
            ], 400);
        }

        $carisurkon = BridgingSuratKontrolBpjs::
            with(['sepAsal'])->
            where('tgl_rencana', $tanggal)
            ->leftJoin('bridging_sep', 'bridging_surat_kontrol_bpjs.no_surat', '=', 'bridging_sep.noskdp')
            ->select([
                // 'bridging_sep.no_sep',
                'bridging_surat_kontrol_bpjs.no_sep',
                'bridging_surat_kontrol_bpjs.no_surat',
                'bridging_surat_kontrol_bpjs.tgl_surat',
                'bridging_surat_kontrol_bpjs.tgl_rencana',
                'bridging_surat_kontrol_bpjs.kd_dokter_bpjs',
                'bridging_surat_kontrol_bpjs.nm_dokter_bpjs',
                'bridging_surat_kontrol_bpjs.kd_poli_bpjs',
                'bridging_surat_kontrol_bpjs.nm_poli_bpjs',
                'bridging_sep.noskdp'
            ])
            ->get();

        return response()->json([
            'code' => ($carisurkon) ? 200 : 204,
            'message' => ($carisurkon) ? 'Ok' : 'Data tidak ditemukan!',
            'data' => ($carisurkon) ? $carisurkon : null,
            'token' => AuthHelper::genToken(),
        ]);
    }
}
