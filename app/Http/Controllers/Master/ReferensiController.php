<?php

namespace App\Http\Controllers\Master;

use App\Helpers\AuthHelper;
use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Penjab;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferensiController extends Controller
{
    public function penjab()
    {
        $data = Penjab::all();
        return response()->json([
            'code' => 200, 
            'data' => $data,
            'message' => 'Success',
            // 'token' => AuthHelper::genToken(),
        ]);
        // return response()->json($data);
    }

    public function kelurahan(Request $request)
    {
        $data = Kelurahan::select('kd_kel', 'nm_kel')
            ->where('nm_kel', 'LIKE', $request->key . '%')
            ->limit(100)
            ->get();

        return response()->json($data);
    }

    public function kecamatan(Request $request)
    {
        $data = Kecamatan::select('kd_kec', 'nm_kec')
            ->where('nm_kec', 'LIKE', $request->key . '%')
            ->limit(100)
            ->get();

        return response()->json($data);
    }

    public function kabupaten(Request $request)
    {
        $data = Kabupaten::select('kd_kab', 'nm_kab')
            ->where('nm_kab', 'LIKE', $request->key . '%')
            ->limit(100)
            ->get();

        return response()->json($data);
    }

    public function perusahaanpasien()
    {
        $data = DB::table('perusahaan_pasien')->get();
        return response()->json($data);
    }

    public function sukubangsa()
    {
        $data = DB::table('suku_bangsa')->get();
        return response()->json($data);
    }

    public function bahasapasien()
    {
        $data = DB::table('bahasa_pasien')->get();
        return response()->json($data);
    }

    public function cacatfisik()
    {
        $data = DB::table('cacat_fisik')->get();
        return response()->json($data);
    }

    public function propinsi()
    {
        $data = DB::table('propinsi')->get();
        return response()->json($data);
    }

    public function provinsi()
    {
        $data = Wilayah::select('kode', 'nama')
            ->whereRaw('CHAR_LENGTH(kode) = 2')
            ->orderBy('kode', 'asc')
            ->get();

        return response()->json($data);
    }

    public function getWilayah(Request $request)
    {
        $wil = [
            2 => [5],
            5 => [8],
            8 => [13],
        ];

        $id = $request->kode;
        $n  = strlen($id);

        if (!isset($wil[$n])) {
            return response()->json([
                'code' => 201,
                'message' => 'Kode wilayah tidak sesuai'
            ]);
        }

        $m = $wil[$n][0]; // target char length

        $data = Wilayah::whereRaw("LEFT(kode, ?) = ?", [$n, $id])
            ->whereRaw("CHAR_LENGTH(kode) = ?", [$m])
            ->orderBy('nama')
            ->get();

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
        ]);
    }
}
