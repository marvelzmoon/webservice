<?php

namespace App\Http\Controllers\Master;

use App\Helpers\AuthHelper;
use App\Helpers\BPer;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\IoDashboard;
use App\Models\IoDashboardActive;
use App\Models\IoDashboardDetail;
use App\Models\IoJenisAntrian;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function refJenisAntrian(Request $request) {
        $data = IoJenisAntrian::all();

        return $data;
    }
    
    public function refParent(Request $request) {
        $data = IoDashboard::where('dash_status', '1')->get();

        return $data;
    }

    public function getdata() {
        $find = IoDashboard::all();

        $data = [];

        foreach ($find as $v) {
            $data[] = [
                'id' => $v->dash_id,
                'nama' => $v->dash_name,
                'type' => IoJenisAntrian::find($v->dash_type),
                'status' => $v->dash_status
            ];
        }

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    public function postdata(Request $request)
    {
        $rules = [
            'nama' => 'required|string',
            'type' => 'required|string',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'string'   => ':attribute harus berupa string',
            'int'      => ':attribute harus berupa integer',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $nama = $request->nama;
        $type = $request->type;

        $existing = IoDashboard::where('dash_name', $nama)
                    ->where('dash_type', $type)
                    ->first();

        if ($existing) {
            return response()->json([
                'code' => 204,
                'message' => 'Nama dan type sudah ada!, tentukan yang lain'
            ]);
        }

        IoDashboard::create([
            'dash_name' => $nama,
            'dash_type' => $type,
            'dash_status' => '1'
        ]);

        return response()->json([
            'code' => 200,
            'message' => 'Data berhasil disimpan',
            'token' => AuthHelper::genToken(),
        ]);  
    }

    public function updatedata(Request $request) {
        $rules = [
            'id'     => 'required|int',
            'nama'   => 'required|string',
            'type'   => 'required|int',
            'status' => 'required|string'
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'string'   => ':attribute harus berupa string',
            'int'      => ':attribute harus berupa integer',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $id     = $request->id;
        $nama   = $request->nama;
        $type   = $request->type;
        $status = $request->status;

        $find = IoDashboard::find($id);

        if (!$find) {
            return response()->json([
                'code' => 404,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        // CEK APAKAH NAMA ATAU TYPE DIUBAH
        $isNameChanged = $find->dash_name != $nama;
        $isTypeChanged = $find->dash_type != $type;

        if ($isNameChanged || $isTypeChanged) {

            // CEK UNIQUE (tidak boleh ada data lain dengan nama+type sama)
            $duplicate = IoDashboard::where('dash_name', $nama)
                        ->where('dash_type', $type)
                        ->where('dash_id', '!=', $id)
                        ->exists();

            if ($duplicate) {
                return response()->json([
                    'code' => 201,
                    'message' => 'Nama dan type tersebut sudah digunakan!',
                ]);
            }
        }

        // UPDATE DATA (nama & type & status)
        IoDashboard::where('dash_id', $id)->update([
            'dash_name'   => $nama,
            'dash_type'   => $type,
            'dash_status' => $status
        ]);

        return response()->json([
            'code'    => 200,
            'message' => 'Data berhasil diubah',
            'token'   => AuthHelper::genToken(),
        ]);
    }

    public function hapusdata(Request $request)
    {
        $rules = [
            'id' => 'required|int'
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'int'      => ':attribute harus berupa integer'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $id = $request->id;

        // cari data
        $find = IoDashboard::find($id);

        if (!$find) {
            return response()->json([
                'code'    => 204,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        // hapus data
        $find->delete();

        return response()->json([
            'code'    => 200,
            'message' => 'Data berhasil dihapus',
            'token'   => AuthHelper::genToken(),
        ]);
    }

    public function getdetail() {
        $find = IoDashboardDetail::all();

        $data = [];
        $parentCache = [];
        $dokterCache = [];
        $poliCache = [];

        foreach ($find as $v) {
            if (!isset($parentCache[$v->ddash_parent])) {
                $parentCache[$v->ddash_parent] =
                    $v->parent ? $v->parent->only(['dash_id', 'dash_name']) : null;
            }
            
            if (!isset($dokterCache[$v->ddash_dokter])) {
                $dokterCache[$v->ddash_dokter] =
                    $v->dokter ? $v->dokter->only(['kd_dokter', 'nm_dokter']) : null;
            }

            if (!isset($poliCache[$v->ddash_poli])) {
                $poliCache[$v->ddash_poli] =
                    $v->poli ? $v->poli->only(['kd_poli', 'nm_poli']) : null;
            }

            $data[] = [
                'id' => $v->ddash_id,
                'parent' => $parentCache[$v->ddash_parent],
                'poli' => $poliCache[$v->ddash_poli],
                'dokter' => $dokterCache[$v->ddash_dokter],
                'status' => $v->ddash_status
            ];
        }

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    public function postdetail(Request $request){
        $rules = [
            'parent' => 'required|int',
            'poli' => 'required|string',
            'dokter' => 'required|string',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'string'   => ':attribute harus berupa string',
            'int'      => ':attribute harus berupa integer',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $parent = $request->parent;
        $poli = $request->poli;
        $dokter = $request->dokter;

        $existing = IoDashboardDetail::where('ddash_poli', $poli)
                    ->where('ddash_dokter', $dokter)
                    ->where('ddash_parent', $parent)
                    ->first();

        if ($existing) {
            return response()->json([
                'code' => 204,
                'message' => 'Nama dan type sudah ada!, tentukan yang lain'
            ]);
        }

        IoDashboardDetail::create([
            'ddash_parent' => $parent,
            'ddash_poli' => $poli,
            'ddash_dokter' => $dokter,
            'ddash_status' => '1'
        ]);

        return response()->json([
            'code' => 200,
            'message' => 'Data berhasil disimpan',
            'token' => AuthHelper::genToken(),
        ]);
    }

    public function updatedetail(Request $request) {
        $rules = [
            'id'     => 'required|int',
            'poli'   => 'required|string',
            'dokter' => 'required|string',
            'status' => 'required|string',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'string'   => ':attribute harus berupa string',
            'int'      => ':attribute harus berupa integer',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $id     = $request->id;
        $poli   = $request->poli;
        $dokter = $request->dokter;
        $status = $request->status;

        $find = IoDashboardDetail::find($id);

        if (!$find) {
            return response()->json([
                'code' => 404,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        // CEK APAKAH POLI ATAU DOKTER DIUBAH
        $isPoliChanged   = $find->ddash_poli != $poli;
        $isDokterChanged = $find->ddash_dokter != $dokter;

        if ($isPoliChanged || $isDokterChanged) {

            // CEK DUPLIKASI poli + dokter
            $duplicate = IoDashboardDetail::where('ddash_poli', $poli)
                        ->where('ddash_dokter', $dokter)
                        ->where('ddash_id', '!=', $id)
                        ->exists();

            if ($duplicate) {
                return response()->json([
                    'code'    => 201,
                    'message' => 'Data dengan poli dan dokter tersebut sudah ada!',
                ]);
            }
        }

        // LAKUKAN UPDATE
        IoDashboardDetail::where('ddash_id', $id)->update([
            'ddash_poli'   => $poli,
            'ddash_dokter' => $dokter,
            'ddash_status' => $status
        ]);

        return response()->json([
            'code'    => 200,
            'message' => 'Data berhasil diubah',
            'token'   => AuthHelper::genToken(),
        ]);
    }

    public function hapusdetail(Request $request)
    {
        $rules = [
            'id' => 'required|int'
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'int'      => ':attribute harus berupa integer'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $id = $request->id;

        // Cek apakah data ada
        $find = IoDashboardDetail::find($id);

        if (!$find) {
            return response()->json([
                'code' => 204,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        // Hapus data
        $find->delete();

        return response()->json([
            'code'    => 200,
            'message' => 'Data berhasil dihapus',
            'token'   => AuthHelper::genToken(),
        ]);
    }

    public function datacontrol() {
        $tgl = date('Y-m-d');
        $hari = BPer::tebakHari($tgl);

        $find = Jadwal::where('hari_kerja', $hari)
                    ->join('dokter', 'dokter.kd_dokter', '=', 'jadwal.kd_dokter')
                    ->join('poliklinik', 'poliklinik.kd_poli', '=', 'jadwal.kd_poli')
                    ->where('dokter.status', '1')
                    ->join('io_dashboard_detail', 'io_dashboard_detail.ddash_dokter', '=', 'jadwal.kd_dokter')
                    ->join('io_dashboard', 'io_dashboard.dash_id', '=', 'io_dashboard_detail.ddash_parent')
                    ->select(
                        'dokter.kd_dokter', 'dokter.nm_dokter',
                        'poliklinik.kd_poli', 'poliklinik.nm_poli',
                        'hari_kerja',
                        'ddash_id', 'dash_name'
                    )
                    ->get();
        
        $data = [];

        foreach ($find as $v) {
            $dasAcv = IoDashboardActive::where('dashac_tgl', $tgl)->where('dashac_idddash', $v->ddash_id)->first();

            $data[] = [
                'hari' => $v->hari_kerja,
                'tanggal' => $tgl,
                'dashboard_id' => $v->ddash_id,
                'dashboard_nama' => $v->dash_name,
                'kddokter' => $v->kd_dokter,
                'nmdokter' => $v->nm_dokter,
                'kdpoli' => $v->kd_poli,
                'nmpoli' => $v->nm_poli,
                'status' => (isset($dasAcv) && $dasAcv->dashac_status == '1') ? 'active' : 'non-active'
            ];
        }

        return response()->json([
            'code'=> 200,
            'message'=> 'ok',
            'data' => $data,
            'token'=> AuthHelper::genToken(),
        ]);
    }

    public function updatecontrol(Request $request) {
        $rules = [
            'id'     => 'required|int',
            'tanggal'     => 'required|string',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'string'   => ':attribute harus berupa string',
            'int'      => ':attribute harus berupa integer',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $tgl = $request->tanggal;
        $id = $request->id;

        DB::table('io_dashboard_active')->whereRaw("DATE(TRIM(dashac_tgl)) < ?", [$tgl])->delete();
        $find = IoDashboardActive::where('dashac_tgl', $tgl)->where('dashac_idddash', $id)->first();

        if (!$find) {
            IoDashboardActive::create([
                'dashac_tgl' => date('Y-m-d', strtotime($tgl)),
                'dashac_idddash' => $id,
                'dashac_status' => 1
            ]);

            return response()->json([
                'code' => 200,
                'message' => 'Dashboard content Active',
                'token' => AuthHelper::genToken(),
            ]);
        } else {
            if ($find->dashac_status == '1') {
                $status = '0';
            }

            $status = '1';
            
            IoDashboardActive::where('dashac_tgl', $id)->where('dashac_id', $id)->update(['dashac_status' => $status]);

            return response()->json([
                'code' => 200,
                'message' => ($status == '1'? 'Dashboard content active' : 'Dashboard content non-active'),
                'token' => AuthHelper::genToken(),
            ]);
        }
    }
}
