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

        $find = RegPeriksaModel::where('tgl_registrasi', $date)
                    ->join('resep_obat', 'reg_periksa.no_rawat', '=', 'resep_obat.no_rawat')
                    ->leftJoin('io_referensi_farmasi', 'resep_obat.no_resep', '=', 'io_referensi_farmasi.no_resep')
                    ->whereNull('io_referensi_farmasi.no_resep')
                    ->where('resep_obat.status', 'ralan')
                    ->select('resep_obat.no_resep', 'reg_periksa.no_rawat')
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

        // START TIDAK ADA RESEP
        if ($klasifikasi == 'tidak ada') {
            $input['status'] = 'tidak ada resep';
            IoReferensiFarmasi::create($input);

            $taskid = IoAntrianTaskid::where('nobooking', BPer::cekNoRef($find->no_rawat))->first();

            if ($taskid && isset($taskid->taskid_3_send) && isset($taskid->taskid_4_send)) {
                if ($taskid->taskid_5 != null) {
                    $settaskid5 = $taskid->taskid_5;
                } else {
                    $settaskid5 = date('Y-m-d H:i:s');
                    IoAntrianTaskid::where('nobooking', BPer::cekNoRef($find->no_rawat))->update(['taskid_5' => $settaskid5]);
                }

                $sendTaskid = new Request([
                    'kodebooking' => BPer::cekNoRef($find->no_rawat),
                    'taskid' => 5,
                    'waktu' => $settaskid5
                ]);
                $apiBPJSSend = App::call(
                    'App\Http\Controllers\Jkn\JknApiAntrolController@updateWaktuAntrian',
                    ['request' => $sendTaskid]
                );

                if ($apiBPJSSend instanceof JsonResponse) {
                    $dResponse = $apiBPJSSend->getData(true);

                    if ($dResponse['metadata']['code'] == 200) {
                        IoAntrianTaskid::where('nobooking', BPer::cekNoRef($find->no_rawat))->update(['taskid_5_send' => date('Y-m-d H:i:s')]);

                        return response()->json([
                            'code' => 200,
                            'message' => 'Antrian FARMASI TIDAK ADA RESEP, ' . $input['no_resep'] . ' TASKID 5 BERHASIL DIKIRIM',
                        ]);
                    }

                    return response()->json([
                        'code' => 200,
                        'message' => 'Antrian FARMASI TIDAK ADA RESEP, ' . $input['no_resep'] . ' TASKID 5 GAGAL DIKIRIM',
                        'message_bpjs' => $dResponse,
                    ]);
                }
            }

            if ($taskid->taskid_5 == null) {
                $settaskid5 = date('Y-m-d H:i:s');
                IoAntrianTaskid::where('nobooking', BPer::cekNoRef($find->no_rawat))->update(['taskid_5' => $settaskid5]);
            }

            $taskidnew = IoAntrianTaskid::where('nobooking', BPer::cekNoRef($find->no_rawat))->first();
            $msgkirim = [];
            $datakirim = [
                ['kodebooking' => BPer::cekNoRef($find->no_rawat), 'taskid' => 3, 'waktu' => $taskidnew->taskid_3],
                ['kodebooking' => BPer::cekNoRef($find->no_rawat), 'taskid' => 4, 'waktu' => $taskidnew->taskid_4],
                ['kodebooking' => BPer::cekNoRef($find->no_rawat), 'taskid' => 5, 'waktu' => $taskidnew->taskid_5],
            ];

            foreach ($datakirim as $value) {
                $sendTaskid = new Request([
                    'kodebooking' => $value['kodebooking'],
                    'taskid' => $value['taskid'],
                    'waktu' => strtotime($value['waktu']) * 1000
                ]);

                $apiBPJSSend = App::call(
                    'App\Http\Controllers\Jkn\JknApiAntrolController@updateWaktuAntrian',
                    ['request' => $sendTaskid]
                );

                if ($apiBPJSSend instanceof JsonResponse) {
                    $dResponse = $apiBPJSSend->getData(true);

                    if ($dResponse['metadata']['code'] == 200) {
                        IoAntrianTaskid::where('nobooking', $value['kodebooking'])->update(['taskid_' . $value['taskid'] . '_send' => date('Y-m-d H:i:s')]);

                        $msgkirim[] = [
                            'code' => 200,
                            'message' => 'taskid ' . $value['taskid'] . ' berhasil dikirim',
                        ];
                    }

                    $msgkirim[] = [
                        'code' => 204,
                        'message' => 'taskid ' . $value['taskid'] . ' gagal dikirim',
                        'message_bpjs' => $dResponse,
                    ];
                }

                return response()->json($apiBPJSSend);
            }

            return $msgkirim;
        }
        // END TIDAK ADA RESEP

        // // START ADA RESEP
        // $taskid = IoAntrianTaskid::where('nobooking', BPer::cekNoRef($find->no_rawat))->first();
        // if ($taskid->taskid_5 == null) {
        //     $settaskid5 = date('Y-m-d H:i:s');
        //     IoAntrianTaskid::where('nobooking', BPer::cekNoRef($find->no_rawat))->update(['taskid_5' => $settaskid5]);
        // }

        // $taskidnew = IoAntrianTaskid::where('nobooking', BPer::cekNoRef($find->no_rawat))->first();

        // $tasksendnull = [];
        // $msgkirim = [];

        // if ($taskidnew->taskid_3_send == null) {
        //     $tasksendnull[] = ['fields' => 'taskid_3', 'waktu' => $taskidnew->taskid_3];
        // }
        // if ($taskidnew->taskid_4_send == null) {
        //     $tasksendnull[] = ['fields' => 'taskid_4', 'waktu' => $taskidnew->taskid_4];
        // }
        // if ($taskidnew->taskid_5_send == null) {
        //     $tasksendnull[] = ['fields' => 'taskid_5', 'waktu' => $taskidnew->taskid_5];
        // }

        // foreach ($tasksendnull as $value) {
        //     $sendTaskid = new Request([
        //         'kodebooking' => BPer::cekNoRef($find->no_rawat),
        //         'taskid' => str_replace('taskid_', '', $value['fields']),
        //         'waktu' => strtotime($value['waktu']) * 1000
        //     ]);
        //     $apiBPJSSend = App::call(
        //         'App\Http\Controllers\Jkn\JknApiAntrolController@updateWaktuAntrian',
        //         ['request' => $sendTaskid]
        //     );

        //     if ($apiBPJSSend instanceof JsonResponse) {
        //         $dResponse = $apiBPJSSend->getData(true);

        //         if ($dResponse['metadata']['code'] == 200) {
        //             IoAntrianTaskid::where('nobooking', BPer::cekNoRef($find->no_rawat))->update([$value['fields'] . '_send' => date('Y-m-d H:i:s')]);
        //                 $msgkirim[] = [
        //                 'code' => 200,
        //                 'message' => 'taskid ' . str_replace('taskid_', '', $value['fields']) . ' berhasil dikirim',
        //             ];
        //         }

        //         $msgkirim[] = [
        //             'code' => 204,
        //             'message' => 'taskid ' . str_replace('taskid_', '', $value['fields']) . ' gagal dikirim',
        //             'message_bpjs' => $dResponse,
        //         ];

        //         return response()->json($apiBPJSSend);
        //     }
        // }

        // return $msgkirim;
        // $input['prefix'] = Io`JenisAntrian::where('jenis_antrian', 'like', '%' . substr($klasifikasi, 0, -2) . '%')->value('prefix');
        // $input['no_antrian'] = IoReferensiFarmasi::where('tanggal', $date)
        //                             ->where('prefix', $input['prefix'])
        //                             ->max('no_antrian') + 1;
        // $input['jenis_resep'] = $klasifikasi;
        // $input['calltime'] = null;
        // $input['status'] = 'Belum';
        // IoReferensiFarmasi::create($input);
        // END ADA RESEP
    }
}