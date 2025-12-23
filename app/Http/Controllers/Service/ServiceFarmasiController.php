<?php

namespace App\Http\Controllers\Service;

use App\Helpers\AuthHelper;
use App\Helpers\BPer;
use App\Http\Controllers\Controller;
use App\Models\IoAntrian;
use App\Models\IoAntrianFarmasi;
use App\Models\IoAntrianTaskid;
use App\Models\IoJenisAntrian;
use App\Models\IoReferensiFarmasi;
use App\Models\RegPeriksaModel;
use App\Models\ResepDokter;
use App\Models\ResepDokterRacikan;
use App\Models\ResepObat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ServiceFarmasiController extends Controller
{
    public function antrianTambah() {
        $date = "2025-12-10";
        $timenow = date('H:i:s');
        $msgkirim = [];

        $find = RegPeriksaModel::where('tgl_registrasi', $date)
                    ->join('resep_obat', 'reg_periksa.no_rawat', '=', 'resep_obat.no_rawat')
                    ->leftJoin('io_referensi_farmasi', 'resep_obat.no_resep', '=', 'io_referensi_farmasi.no_resep')
                    ->whereNull('io_referensi_farmasi.no_resep')
                    ->leftJoin('referensi_mobilejkn_bpjs', 'reg_periksa.no_rawat', '=', 'referensi_mobilejkn_bpjs.no_rawat')
                    ->leftJoin('io_antrian_taskid', DB::raw('io_antrian_taskid.nobooking'), '=', DB::raw('COALESCE(referensi_mobilejkn_bpjs.nobooking, reg_periksa.no_rawat)'))
                    ->whereNotNull('io_antrian_taskid.nobooking')
                    ->where('resep_obat.status', 'ralan')
                    ->select('resep_obat.no_resep', 'reg_periksa.no_rawat')
                    ->orderBy('resep_obat.no_resep', 'ASC')
                    ->first();
                    // ->get();

        if (!$find) {
            return response()->json([
                'code' => 204,
                'message' => 'No Content'
            ]);
        }

        // jenis resep
        $resepDokter = ResepDokter::where('no_resep', $find->no_resep)->exists();
        $resepDokterRacikan = ResepDokterRacikan::where('no_resep', $find->no_resep)->exists();
        $klasifikasi = 'tidak ada';
        if ($resepDokterRacikan) {
            $klasifikasi = 'racikan';
        } elseif ($resepDokter) {
            $klasifikasi = 'non racikan';
        }

        $input = [
            'no_resep' => $find->no_resep,
            'kodebooking' => BPer::cekNoRef($find->no_rawat),
            'tanggal' => $date,
        ];

        // START TIDAK ADA RESEP OBAT
        if ($klasifikasi == 'tidak ada') {
            $kirimTaskid = $this->kirimTaskidBPJS(BPer::cekNoRef($find->no_rawat));
            // $kirimTaskid = [];
            
            $input['status'] = 'tidak ada resep';
            IoReferensiFarmasi::create($input);

            return response()->json([
                'code' => 200,
                'message' => 'Berhasil memproses ' . $find->no_rawat . ' - ' . $find->no_resep . ' tidak ada resep obat',
                'details' => $kirimTaskid
            ]);
        }
        // END TIDAK ADA RESEP OBAT

        // START ADA RESEP OBAT
        if ($klasifikasi != 'tidak ada') {
            $kirimTaskid = $this->kirimTaskidBPJS(BPer::cekNoRef($find->no_rawat));
            // $kirimTaskid = [];

            $input['prefix'] = IoJenisAntrian::where('jenis_antrian', 'like', '%' . substr($klasifikasi, 0, -2) . '%')->value('prefix');
            $input['no_antrian'] = IoReferensiFarmasi::where('tanggal', $date)
                                        ->where('prefix', $input['prefix'])
                                        ->max('no_antrian') + 1;
            $input['jenis_resep'] = $klasifikasi;
            $input['calltime'] = null;
            $input['status'] = 'Belum';

            $json = [
                'kodebooking' => $input['kodebooking'],
                'jenisresep' => $input['jenis_resep'],
                'nomorantrean' => $input['no_antrian'],
                'keterangan' => 'Resep obat ' . $input['jenis_resep'] . ' sedang diproses di farmasi',
            ];

            $input['json'] = json_encode($json);
            IoReferensiFarmasi::create($input);

            $sendTaskid = new Request($json);
            $apiBPJSSend = App::call(
                'App\Http\Controllers\Jkn\JknApiAntrolController@daftarAntrianFarmasi',
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
                    IoReferensiFarmasi::where('no_resep', $input['no_resep'])->update(['validasi_send' => $date . ' ' . $timenow]);             
                    $msgkirim[] = [
                        'code' => $code,
                        'message' => 'Sukses mengirim antrian farmasi ke BPJS'
                    ];
                }

                $msgkirim[] = [
                    'code' => $code,
                    'message' => 'Antrian Farmasi Gagal : ' . $message
                ];
            }

            $taskid6 = $this->kirimTaskid6(BPer::cekNoRef($find->no_rawat));
            $msgkirim[] = $taskid6->getData(true);
            // return response()->json($apiBPJSSend);

            return response()->json([
                'code' => 200,
                'message' => 'Ada resep obat, berhasil memproses ' . $find->no_rawat . ' - ' . $find->no_resep,
                'details' => array_merge($kirimTaskid, $msgkirim)
            ]);
        }
        // END ADA RESEP OBAT

        return response()->json([
            'code' => 200,
            'message' => 'Proses terlewati, aksi tidak sesuai',
        ]);
    }

    private function kirimTaskidBPJS($nobooking) {
        $taskid = IoAntrianTaskid::where('nobooking', BPer::cekNoRef($nobooking))->first();
        $kirimTaskid = [];

        if ($taskid && $taskid->taskid_3_send == null) {
            $sendTaskid = new Request([
                'kodebooking' => $taskid->nobooking,
                'taskid' => 3,
                'waktu' => strtotime($taskid->taskid_3) * 1000
            ]);

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
                    IoAntrianTaskid::where('nobooking', $taskid->nobooking)->update(['taskid_3_send' => date('Y-m-d H:i:s')]);
                    $kirimTaskid[] = [
                        'code' => $code,
                        'message' => 'TSukses mengirim waktu antrian ke BPJS'
                    ];
                }

                $kirimTaskid[] = [
                    'code' => $code,
                    'message' => 'TASKID 3: ' . $message
                ];
            }

            // return response()->json($apiBPJSSend);
        }
        
        if ($taskid && $taskid->taskid_4_send == null) {
            $sendTaskid = new Request([
                'kodebooking' => $taskid->nobooking,
                'taskid' => 4,
                'waktu' => strtotime($taskid->taskid_4) * 1000
            ]);

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
                    IoAntrianTaskid::where('nobooking', $taskid->nobooking)->update(['taskid_4_send' => date('Y-m-d H:i:s')]);

                    $kirimTaskid[] = [
                        'code' => $code,
                        'message' => 'TASKID 4: Sukses mengirim waktu antrian ke BPJS'
                    ];
                }

                $kirimTaskid[] = [
                    'code' => $code,
                    'message' => 'TASKID 4: ' . $message
                ];
            }

            // return response()->json($apiBPJSSend);
        }

        if ($taskid && $taskid->taskid_5_send == null) {
            $sendTaskid = new Request([
                'kodebooking' => $taskid->nobooking,
                'taskid' => 5,
                'waktu' => strtotime($taskid->taskid_5) * 1000
            ]);

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
                    IoAntrianTaskid::where('nobooking', $taskid->nobooking)->update(['taskid_5_send' => date('Y-m-d H:i:s')]);

                    $kirimTaskid[] = [
                        'code' => $code,
                        'message' => 'TASKID 5: Sukses mengirim waktu antrian ke BPJS'
                    ];
                }

                $kirimTaskid[] = [
                    'code' => $code,
                    'message' => 'TASKID 5: ' . $message
                ];
            }

            // return response()->json($apiBPJSSend);
        }

        return $kirimTaskid;
    }

    private function kirimTaskid6($nobooking) {
        date_default_timezone_set('Asia/Jakarta');
        $taskid = IoAntrianTaskid::where('nobooking', BPer::cekNoRef($nobooking))->first();
        $timenow = date('Y-m-d H:i:s');

        $input = [
            'kodebooking' => $taskid->nobooking,
            'taskid' => 6,
            'waktu' => strtotime($timenow) * 1000
        ];

        IoAntrianTaskid::where('nobooking', $taskid->nobooking)->update(['taskid_6' => $timenow]);

        $taskidnew = IoAntrianTaskid::where('nobooking', BPer::cekNoRef($nobooking))->first();

        if ($taskidnew->taskid_5_send != null) {
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
                    IoAntrianTaskid::where('nobooking', $taskid->nobooking)->update(['taskid_6_send' => date('Y-m-d H:i:s')]);

                    return response()->json([
                        'code' => $code,
                        'message' => 'TASKID 6: Sukses mengirim waktu antrian ke BPJS'
                    ]);
                }

                return response()->json([
                    'code' => $code,
                    'message' => 'TASKID 6: ' . $message,
                ]);
            }

            // return response()->json($apiBPJSSend);
        }

        return response()->json([
            'code' => 200,
            'message' => 'TASKID 6 berhasil dibuat disimpan Temp!'
        ]);
    }
}