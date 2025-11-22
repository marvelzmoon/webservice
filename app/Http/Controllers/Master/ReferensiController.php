<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Penjab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferensiController extends Controller
{
    public function penjab()
    {
        $data = Penjab::all();
        return response()->json($data);
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
}
