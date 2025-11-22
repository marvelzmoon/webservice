<?php

namespace App\Http\Controllers\Master;

use App\Helpers\AuthHelper;
use App\Helpers\BPer;
use App\Http\Controllers\Controller;
use App\Models\IoUser;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PasienController extends Controller
{
    public function getdata()
    {
        $query = Pasien::orderBy('no_rkm_medis', 'DESC')
            ->limit(2000);

        // Validasi query kosong
        if (!$query->exists()) {
            return response()->json([
                'code'    => 204,
                'message' => 'Data tidak ditemukan',
                'data'    => [],
            ]);
        }

        // Jika ada data → paginate
        $data = $query->paginate(100);

        return response()->json([
            'code' => 200,
            'message' => 'Data ada',
            'data' => $data,
        ]);
    }

    public function searchPasien(Request $request)
    {
        $norm = $request->norm;
        $nama = $request->nama;
        $dob = $request->dob;
        $alamat = $request->alamat;

        // search by norm
        if ($request->norm != "") {
            $search = Pasien::where('no_rkm_medis', 'like', '%' . $norm . '%')->get();
        }

        // search by nama
        if ($request->nama != "") {
            $search = Pasien::where('nm_pasien', 'like', '%' . $nama . '%')->limit(100)->get();
        }

        // search by nama and dob
        if ($request->nama != "" && $request->dob != "") {
            $search = Pasien::where('nm_pasien', 'like', '%' . $nama . '%')
                ->where('tgl_lahir', $dob)
                ->limit(100)
                ->get();
        }

        // search by nama and alamat
        if ($request->nama != '' && $request->alamat) {
            $search = Pasien::where('nm_pasien', 'like', '%' . $nama . '%')
                ->where('alamat', 'like', '%' . $alamat . '%')
                ->limit(100)
                ->get();
        }

        // search by nama, alamat and dob
        if ($request->nama != '' && $request->alamat && $request->dob != '') {
            $search = Pasien::where('nm_pasien', 'like', '%' . $nama . '%')
                ->where('alamat', 'like', '%' . $alamat . '%')
                ->where('tgl_lahir', $dob)
                ->limit(100)
                ->get();
        }

        return response()->json([
            'code' => 200,
            'message' => ($search->isEmpty()) ? 'Data tidak ditemukan' : 'Success',
            'count' => ($search->isEmpty()) ? 0 : $search->count(),
            'data' => ($search->isEmpty()) ? null : $search,
        ]);
    }

    public function createPasien(Request $request)
    {
        $rules = [
            'nama'          => 'required',
            'jk'            => 'required|in:L,P',

            'goldarah'      => 'required|in:A,B,O,AB,-',

            'pob'           => 'required',
            'dob'           => 'required|date_format:Y-m-d',

            'pendidikan'    => 'required|in:TS,TK,SD,SMP,SMA,SLTA/SEDERAJAT,D1,D2,D3,D4,S1,S2,S3,-',

            'agama'         => 'required',

            'nikah'         => 'required|in:BELUM MENIKAH,MENIKAH,JANDA,DUDHA,JOMBLO',

            'askes'         => 'required|exists:penjab,kd_pj',
            'nopeserta'     => 'required',
            'notelp'        => 'required',
            'ktpsim'        => 'required',
            'alamat'        => 'required',

            'kelurahan'     => 'required|exists:kelurahan,kd_kel',
            'kecamatan'     => 'required|exists:kecamatan,kd_kec',
            'kabupaten'     => 'required|exists:kabupaten,kd_kab',
            'propinsi'      => 'required|exists:propinsi,kd_prop',

            'namaibu'       => 'required',

            'pngjawab'      => 'required|in:AYAH,IBU,ISTRI,SUAMI,SAUDARA,ANAK,DIRI SENDIRI,LAIN-LAIN',

            'namapj'        => 'required',
            'pekerjaanpj'   => 'required',
            'alamatpj'      => 'required',

            'kelurahanpj'   => 'required|exists:kelurahan,kd_kel',
            'kecamatanpj'   => 'required|exists:kecamatan,kd_kec',
            'kabupatenpj'   => 'required|exists:kabupaten,kd_kab',
            'propinsipj'    => 'required|exists:propinsi,kd_prop',

            'sukubangsa'    => 'required',
            'bahasa'        => 'required',
            'cacat'         => 'required',
            'instpasien'    => 'required',
            'nipnrp'        => 'required',

            'tgldaftar'     => 'required|date_format:Y-m-d',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',

            // JK
            'jk.in' => 'Jenis kelamin hanya boleh L atau P',

            // Goldarah
            'goldarah.in' => 'Golongan darah hanya boleh A, B, O, AB, atau -',

            // Pendidikan
            'pendidikan.in' => 'Pendidikan tidak valid, hanya boleh TS, TK, SD, SMP, SMA, SLTA/SEDERAJAT, D1-D4, S1-S3 atau -',

            // Nikah
            'nikah.in' => 'Status nikah hanya boleh BELUM MENIKAH, MENIKAH, JANDA, DUDHA, atau JOMBLO',

            // Penanggung jawab
            'pngjawab.in' => 'Penanggung jawab hanya boleh AYAH, IBU, ISTRI, SUAMI, SAUDARA, ANAK, DIRI SENDIRI, atau LAIN-LAIN',

            // DOB
            'dob.date_format' => 'Format tanggal lahir harus Y-m-d',

            // Exists
            'askes.exists' => 'Asuransi tidak ditemukan',
            'kelurahan.exists' => 'Kelurahan tidak ditemukan',
            'kecamatan.exists' => 'Kecamatan tidak ditemukan',
            'kabupaten.exists' => 'Kabupaten tidak ditemukan',
            'propinsi.exists' => 'Propinsi tidak ditemukan',

            'kelurahanpj.exists' => 'Kelurahan penanggungjawab tidak ditemukan',
            'kecamatanpj.exists' => 'Kecamatan penanggungjawab tidak ditemukan',
            'kabupatenpj.exists' => 'Kabupaten penanggungjawab tidak ditemukan',
            'propinsipj.exists'  => 'Propinsi penanggungjawab tidak ditemukan',

            'tgldaftar.date_format' => 'Format tanggal daftar harus Y-m-d',
        ];

        $customNames = [
            'nama' => 'Nama pasien',
            'jk' => 'Jenis kelamin',
            'goldarah' => 'Golongan darah',
            'pob' => 'Tempat lahir',
            'dob' => 'Tanggal lahir',
            'pendidikan' => 'Pendidikan',
            'agama' => 'Agama',
            'nikah' => 'Status nikah',
            'askes' => 'Asuransi',
            'nopeserta' => 'Nomor peserta',
            'notelp' => 'Nomor telepon',
            'ktpsim' => 'Nomor identitas',
            'alamat' => 'Alamat pasien',

            'kelurahan' => 'Kelurahan',
            'kecamatan' => 'Kecamatan',
            'kabupaten' => 'Kabupaten',
            'propinsi' => 'Propinsi',

            'namaibu' => 'Nama ibu',

            'pngjawab' => 'Penanggung jawab',
            'namapj' => 'Nama penanggungjawab',
            'pekerjaanpj' => 'Pekerjaan penanggungjawab',
            'alamatpj' => 'Alamat penanggungjawab',

            'kelurahanpj' => 'Kelurahan penanggungjawab',
            'kecamatanpj' => 'Kecamatan penanggungjawab',
            'kabupatenpj' => 'Kabupaten penanggungjawab',
            'propinsipj' => 'Propinsi penanggungjawab',

            'sukubangsa' => 'Suku bangsa',
            'bahasa' => 'Bahasa pasien',
            'cacat' => 'Cacat fisik',
            'instpasien' => 'Instansi pasien',
            'nipnrp' => 'NIP/NRP',

            'tgldaftar' => 'Tanggal daftar',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->setAttributeNames($customNames);

        if ($validator->fails()) {
            return response()->json([
                'code' => 201,
                'message' => $validator->errors()->first(),
            ]);
        }

        if (isset($request->no_rkm_medis)) {
            return $this->post($request->except('confirmed'));
        }

        $validPasien = Pasien::where('no_ktp', $request->ktpsim)->first();

        if ($validPasien) {
            if (!$request->confirmed) {
                return response()->json([
                    'code' => 201,
                    'message' => 'NIK pasien ' . $request->ktpsim . ' sudah terdaftar di NORM ' . $validPasien->no_rkm_medis,
                    'data' => [
                        'norm' => $validPasien->no_rkm_medis,
                        // 'pasien' => $request->except('confirmed'),
                        'pasien' => $validPasien
                    ],
                    'token' => AuthHelper::genToken(),
                ]);
            } else {
                if ($request->confirmed != 'force') {
                    return response()->json([
                        'code' => 201,
                        'message' => 'Proses ditangguhkan'
                    ]);
                } else {
                    return $this->post($request->except('confirmed'));
                }
            }
        } else {
            return $this->post($request->except('confirmed'));
        }
    }

    public function post($data)
    {
        $data = (object) $data;

        DB::beginTransaction();

        try {
            $pasien = isset($data->no_rkm_medis) ? Pasien::find($data->no_rkm_medis) : null;
            $pasien = $pasien ?: new Pasien();

            if (!$pasien->no_rkm_medis) {
                // Create NORM
                $norm = DB::table('set_no_rkm_medis')->lockForUpdate()->value('no_rkm_medis');

                $normNew = (int)$norm + 1;

                DB::table('set_no_rkm_medis')->update(['no_rkm_medis' => $normNew]);
                $pasien->no_rkm_medis      = str_pad($normNew, 6, '0', STR_PAD_LEFT);
                // End Create NORM
            }

            $pasien->tgl_daftar        = $data->tgldaftar;

            $pasien->nm_pasien         = $data->nama;
            $pasien->jk                = $data->jk;
            $pasien->gol_darah         = $data->goldarah;

            $pasien->tmp_lahir         = $data->pob;
            $pasien->tgl_lahir         = $data->dob;

            $pasien->pnd               = $data->pendidikan;
            $pasien->agama             = $data->agama;
            $pasien->stts_nikah        = $data->nikah;

            $pasien->kd_pj             = $data->askes;
            $pasien->no_peserta        = $data->nopeserta;
            $pasien->email             = $data->email;

            $pasien->no_tlp            = $data->notelp;
            $pasien->no_ktp            = $data->ktpsim;

            $pasien->alamat            = $data->alamat;
            $pasien->kd_kel            = $data->kelurahan;
            $pasien->kd_kec            = $data->kecamatan;
            $pasien->kd_kab            = $data->kabupaten;
            $pasien->kd_prop           = $data->propinsi;

            $pasien->nm_ibu            = $data->namaibu;

            $pasien->keluarga          = $data->pngjawab;
            $pasien->namakeluarga      = $data->namapj;
            $pasien->pekerjaanpj       = $data->pekerjaanpj;
            $pasien->alamatpj          = $data->alamatpj;

            $pasien->kelurahanpj       = $data->kelurahanpj;
            $pasien->kecamatanpj       = $data->kecamatanpj;
            $pasien->kabupatenpj       = $data->kabupatenpj;
            $pasien->propinsipj        = $data->propinsipj;

            $pasien->suku_bangsa       = $data->sukubangsa;
            $pasien->bahasa_pasien     = $data->bahasa;
            $pasien->cacat_fisik       = $data->cacat;

            $pasien->perusahaan_pasien = $data->instpasien;
            $pasien->nip               = $data->nipnrp;
            $pasien->save();

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => 'Berhasil membuat data pasien',
                'data' => [
                    'norm' => $pasien->no_rkm_medis
                ],
                'token' => AuthHelper::genToken(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 400,
                'success' => "Error transaction",
                'message' => $e->getMessage()
            ]);
        }
    }

    public function destroy(Request $request)
    {
        $head = Crypt::decrypt(request()->header('Authorization'));

        $iouser = IoUser::find($head['username']);

        if ($request->password !== $iouser->password) {
            return response()->json([
                'code' => 400,
                'success' => 'Proses dibatalkan, akses tidak sesuai'
            ]);
        }

        $data = Pasien::find($request->no_rkm_medis);

        if (!$data) {
            return response()->json([
                'code' => 201,
                'message' => 'Pasien tidak ditemukan'
            ]);
        }

        // $data->delete();
        return response()->json([
            'code' => 200,
            'message' => 'Pasien dengan NORM ' . $request->no_rkm_medis . ' berhasil dihapus',
            'token' => AuthHelper::genToken(),
        ]);
    }
}
