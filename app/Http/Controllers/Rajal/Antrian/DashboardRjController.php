<?php

namespace App\Http\Controllers\Rajal\Antrian;

use App\Helpers\AuthHelper;
use App\Helpers\BPer;
use App\Http\Controllers\Controller;
use App\Models\IoAntrianPanggil;
use App\Models\IoDashboard;
use App\Models\IoDashboardDetail;

class DashboardRjController extends Controller
{
    public function view($id)
    {
        $tgl = date('Y-m-d');
        $hari = BPer::tebakHari($tgl);
        $findDashboard = IoDashboard::find($id);

        if (!$findDashboard) {
            return response()->json([
                'code' => 204,
                'message' => 'Dashboard tidak ditemukan'
            ]);
        }

        $child = IoDashboardDetail::join('io_dashboard_active', 'io_dashboard_active.dashac_idddash', '=', 'io_dashboard_detail.ddash_id')
            ->where('ddash_parent', $findDashboard->dash_id)
            ->where('dashac_status', '1')
            ->join('jadwal', 'jadwal.kd_dokter', '=', 'io_dashboard_detail.ddash_dokter')
            ->join('dokter', 'dokter.kd_dokter', '=', 'jadwal.kd_dokter')
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'jadwal.kd_poli')
            ->where('hari_kerja', $hari)
            ->orderBy('jam_mulai', 'asc')
            ->get();

        if ($child->isEmpty()) {
            return response()->json([
                'code' => 204,
                'message' => 'Tidak ada data'
            ]);
        }

        $data = [];

        foreach ($child as $v) {
            $data[] = [
                'idDash' => $v->ddash_id,
                'dokter' => $v->nm_dokter,
                'poli' => $v->nm_poli
            ];
        }

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken()
        ]);
    }

    public function panggil($id)
    {
        $findDashboard = IoDashboard::find($id);

        if (!$findDashboard) {
            return response()->json([
                'code' => 204,
                'message' => 'Dashboard tidak ditemukan'
            ]);
        }

        $cari = IoAntrianPanggil::where('dashboard_id', $findDashboard->dash_id)
            ->join('io_antrian', 'io_antrian.no_referensi', '=', 'io_antrian_panggil.no_referensi')
            ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'io_antrian.no_referensi')
            ->join('dokter', 'dokter.kd_dokter', '=', 'reg_periksa.kd_dokter')
            ->join('poliklinik', 'poliklinik.kd_poli', '=', 'reg_periksa.kd_poli')
            ->first();

        if (!$cari) {
            return response()->json([
                'code' => 204,
                'message' => 'Tidak ada data panggilan'
            ]);
        }

        $data = [];
        $data[] = 'antrian.wav';

        $antrian = explode('-', $cari->no_antrian);
        $pecahHuruf = str_split($antrian[0]);

        foreach ($pecahHuruf as $pc) {
            $data[] = $pc . '.wav';
        }

        $pecahAngka = str_split((int)$antrian[1]);

        foreach ($pecahAngka as $pc) {
            $data[] = $pc . '.wav';
        }

        $data[] = 'menuju_poli.wav';

        IoAntrianPanggil::where('no_referensi', $cari->no_referensi)->delete();

        return response()->json([
            'code' => 200,
            'message' => 'Sukses, ' . $cari->no_antrian . ' Sedang dipanggil',
            'data' => [
                'popup' => [
                    'noantri' => $cari->no_antrian,
                    'dokter' => $cari->nm_dokter,
                    'poli' => $cari->nm_poli,
                ],
                'list' => $data
            ],
            'token' => AuthHelper::genToken()
        ]);
    }
}
