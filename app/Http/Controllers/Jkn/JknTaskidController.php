<?php

namespace App\Http\Controllers\Jkn;

use App\Helpers\AuthHelper;
use App\Http\Controllers\Controller;
use App\Models\IoAntrianTaskid;
use App\Models\IoReferensiAntrianFarmasi;
use App\Models\ReferensiMobilejknBpjs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JknTaskidController extends Controller
{
    public function post(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'nobooking' => 'required',
                'taskid'    => 'required|in:1,2,3,4,5,6,7,99',
                'waktu'     => 'required',
            ],
            [
                'nobooking.required' => 'Nobooking tidak boleh kosong',
                'taskid.required'    => 'Taskid tidak boleh kosong',
                'taskid.in'          => 'Taskid tidak berlaku',
                'waktu.required'     => 'Waktu tidak boleh kosong',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'code'    => 204,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $taskid = (int) $request->taskid;
        $kolom = "taskid_{$taskid}";
        $kolomSend = "taskid_{$taskid}_send";

        // Validasi dasar
        $validator = Validator::make($request->all(), [
            'nobooking' => 'required',
            'taskid'    => 'required|in:3,4,5,6,7,99',
            'waktu'     => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 204,
                'message' => $validator->errors()->first()
            ]);
        }

        // Ambil/insert data dasar
        $task = IoAntrianTaskid::firstOrCreate([
            'nobooking' => $request->nobooking
        ]);

        /* ============================================================
            RULE 0: Validasi perbedaan waktu untuk TaskID 4 - 7
            - Task berikutnya harus > task sebelumnya
            - Minimal beda 1 menit (Y-m-d H:i)
        ============================================================ */
        if ($taskid > 3 && $taskid <= 7) {

            // Nama field task sebelumnya, contoh: taskid_3, taskid_4
            $prevField = 'taskid_' . ($taskid - 1);

            // Ambil waktu task sebelumnya
            $prevTime = $task->$prevField ?? null;

            if ($prevTime) {

                // Normalisasi format ke Y-m-d H:i
                $prevTimeFmt = date('Y-m-d H:i', strtotime($prevTime));
                $currTimeFmt = date('Y-m-d H:i', strtotime($request->waktu));

                // Task berikutnya harus lebih besar minimal 1 menit
                if (strtotime($currTimeFmt) <= strtotime($prevTimeFmt)) {
                    return response()->json([
                        'code'    => 201,
                        'message' => "Waktu TaskID {$taskid} harus lebih besar dari TaskID " . ($taskid - 1)
                    ]);
                }
            }
        }

        /* ============================================================
            RULE 1: Jika taskid_99 sudah terisi → semuanya diblokir
        ============================================================= */
        if ($taskid !== 99 && !empty($task->taskid_99)) {
            return response()->json([
                'code'    => 201,
                'message' => "Tidak dapat memproses TaskID {$taskid}. TaskID 99 telah mengunci proses."
            ]);
        }

        /* ============================================================
            RULE 2: Validasi khusus TaskID 99
        ============================================================= */
        if ($taskid == 99) {
            // TaskID 3 harus sudah terisi
            if (empty($task->taskid_3)) {
                return response()->json([
                    'code'    => 201,
                    'message' => "TaskID 99 tidak dapat diproses sebelum TaskID 3 terisi."
                ]);
            }

            // TaskID 5 tidak boleh terisi
            if (!empty($task->taskid_5)) {
                return response()->json([
                    'code'    => 201,
                    'message' => "TaskID 99 tidak dapat diproses karena TaskID 5 sudah terisi."
                ]);
            }

            // Tidak bisa update jika sudah terkirim
            if (!empty($task->taskid_99_send)) {
                return response()->json([
                    'code'    => 201,
                    'message' => "TaskID 99 tidak dapat diupdate karena sudah terkirim."
                ]);
            }

            // Update taskid_99
            $task->update([$kolom => $request->waktu]);

            return response()->json([
                'code'    => 200,
                'message' => "TaskID 99 berhasil disimpan.",
                'token'   => AuthHelper::genToken()
            ]);
        }

        /* ============================================================
            RULE 3: Validasi berurutan untuk taskid 3–7
        ============================================================= */
        $flow = [3, 4, 5, 6, 7];
        $pos  = array_search($taskid, $flow);

        // Jika bukan task pertama (3), harus ada task sebelumnya
        if ($taskid !== 3) {
            $prevTask = $flow[$pos - 1];
            $kolomPrev = "taskid_{$prevTask}";

            // Harus berurutan
            if (empty($task->$kolomPrev)) {
                return response()->json([
                    'code'    => 201,
                    'message' => "TaskID {$taskid} tidak dapat diproses sebelum TaskID {$prevTask} terisi."
                ]);
            }

            // Waktu tidak boleh mundur
            if (strtotime($request->waktu) < strtotime($task->$kolomPrev)) {
                return response()->json([
                    'code'    => 201,
                    'message' => "Waktu TaskID {$taskid} tidak boleh lebih kecil dari TaskID {$prevTask}."
                ]);
            }
        }

        /* ============================================================
            RULE 4: Tidak boleh update jika taskid_x_send sudah terisi
        ============================================================= */
        if (!empty($task->$kolomSend)) {
            return response()->json([
                'code'    => 201,
                'message' => "TaskID {$taskid} tidak dapat diupdate karena sudah terkirim."
            ]);
        }

        /* ============================================================
            RULE 5: TaskID_x boleh diupdate selama belum terkirim
        ============================================================= */
        $task->update([
            $kolom => $request->waktu
        ]);

        return response()->json([
            'code'    => 200,
            'message' => "TaskID {$taskid} berhasil disimpan.",
            'token'   => AuthHelper::genToken()
        ]);
    }

    public function getdata(Request $request)
    {
        if (config('confsistem.add_farmasi') == 'YA') {
            $task = ReferensiMobilejknBpjs::where('tanggalperiksa', $request->tanggal)
                ->join('io_antrian_taskid', 'io_antrian_taskid.nobooking', '=', 'referensi_mobilejkn_bpjs.nobooking')
                ->join('io_referensi_antrian_farmasi', 'referensi_mobilejkn_bpjs.nobooking', '=', 'io_referensi_antrian_farmasi.nobooking')
                ->select('io_antrian_taskid.*', 'io_referensi_antrian_farmasi.jenisresep', 'referensi_mobilejkn_bpjs.nobooking')
                ->get();
        } else {
            $task = ReferensiMobilejknBpjs::where('tanggalperiksa', $request->tanggal)
                ->join('io_antrian_taskid', 'io_antrian_taskid.nobooking', '=', 'referensi_mobilejkn_bpjs.nobooking')
                ->select('io_antrian_taskid.*', 'referensi_mobilejkn_bpjs.nobooking')
                ->get();
        }

        if (!$task) {
            return response()->json([
                'code' => 204,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Data ada',
            'data' => $task,
            'token' => AuthHelper::genToken(),
        ]);
    }

    public function send(Request $request)
    {
        $rules = [
            'nobooking' => 'required',
            'taskid'    => 'required',
            'waktu'     => 'required',
        ];

        $data = [
            'nobooking' => $request->nobooking,
            'taskid' => $request->taskid,
            'waktu' => $request->waktu,
        ];

        if (config('confsistem.add_farmasi') == 'YA') {
            $rules['jenisresep'] = 'required';

            $data['jenisresep'] = $request->jenisresep;
        }

        $request->validate($rules, [
            'nobooking.required' => 'Nobooking tidak boleh kosong',
            'taskid.required'    => 'Taskid tidak boleh kosong',
            'waktu.required'     => 'Waktu tidak boleh kosong',
            'jenisresep.required' => 'Jenis resep tidak boleh kosong',
        ]);

        $cek = IoAntrianTaskid::find($request->nobooking);
        $tid = "taskid_{$request->taskid}";
        $tidsend = "taskid_{$request->taskid}_send";

        if (!$cek->$tid) {
            return response()->json([
                'code' => 201,
                'message' => "Taskid " . $request->taskid . " masih kosong"
            ]);
        }

        if ($cek->$tidsend != NULL) {
            return response()->json([
                'code' => 201,
                'message' => 'Taskid ' . $request->taskid . ' sudah terkirim'
            ]);
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.addapi_url') . '/antrol/taskid-kirim.php', // your preferred url/
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $dResponse = json_decode($response, true);

        // Jika sukses
        if ($dResponse['metadata']['code'] == 200) {
            IoAntrianTaskid::where('nobooking', $request->nobooking)->update(['taskid_' . $request->taskid  . '_send' => Carbon::now()]);

            return response()->json([
                'code' => 200,
                'message' => 'Taskid ' . $request->taskid . ' berhasil terkirim',
                'token'   => AuthHelper::genToken()
            ]);
        } else {
            return $response;
        }
    }
}
