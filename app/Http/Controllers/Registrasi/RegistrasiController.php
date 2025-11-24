<?php

namespace App\Http\Controllers\Registrasi;

use App\Helpers\AuthHelper;
use App\Helpers\BPer;
use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\IoReferensiAntrianFarmasi;
use App\Models\Pasien;
use App\Models\Poliklinik;
use App\Models\ReferensiMobilejknBpjs;
use App\Models\RegPeriksaModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function Pest\Laravel\json;

class RegistrasiController extends Controller
{
    public function getdata(Request $request)
    {
        $query = RegPeriksaModel::where('tgl_registrasi', $request->tglperiksa)->limit(5000);

        // Validasi query kosong
        if (!$query->exists()) {
            return response()->json([
                'code'    => 204,
                'message' => 'Data tidak ditemukan',
                'data'    => [],
            ]);
        }

        // Jika ada data → paginate
        $data = $query->paginate(50);

        return response()->json([
            'code' => 200,
            'message' => 'Data ada',
            'data' => $data,
        ]);
    }

    public function post(Request $request)
    {
        $kddokter = $request->dokter;
        $kdpoli = $request->poli;
        $normedis = $request->norkmmedis;
        $tglperiksa = $request->tglperiksa;
        $jamperiksa = $request->jamperiksa;
        $hariperiksa = BPer::tebakHari($tglperiksa);
        $noref = $request->noreferensi;
        $jkunj = $request->jeniskunjungan;
        $cbayar = $request->carabayar;

        $validator = Validator::make(
            $request->all(),
            [
                'dokter'          => 'required',
                'poli'            => 'required',
                'norkmmedis'      => 'required',
                'tglperiksa'      => 'required|date_format:Y-m-d',
                'jamperiksa'      => 'required',
                'carabayar'       => 'required',
                'noreferensi'     => 'present|nullable',
                'jeniskunjungan'  => 'required|in:1,2,3,4',
            ],
            [
                'dokter.required'         => 'Dokter tujuan belum ditentukan!',
                'poli.required'           => 'Poliklinik tujuan belum ditentukan!',
                'norkmmedis.required'     => 'No rekam medis pasien belum ditentukan!',
                'tglperiksa.required'     => 'Tanggal periksa belum diisi!',
                'tglperiksa.date_format'  => 'Format tanggal harus Y-m-d',
                'jamperiksa.required'     => 'Jam periksa belum diisi!',
                'carabayar.required'      => 'Cara bayar belum diisi!',
                'noreferensi.present'     => 'noreferensi tidak ditemukan dalam request',
                'jeniskunjungan.required' => 'jeniskunjungan tidak ditemukan dalam request',
                'jeniskunjungan.in'       => 'jeniskunjungan tidak sesuai {1 (Rujukan FKTP), 2 (Rujukan Internal), 3 (Kontrol), 4 (Rujukan Antar RS)}',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'code'    => 204,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        DB::beginTransaction();

        try {
            $pasien = Pasien::find($normedis);

            $cPasienUmur = Bper::hitungUmur($pasien->tgl_lahir);
            $expCpasienumur = explode(' ', $cPasienUmur);

            //last noreg
            $lastNoReg = RegPeriksaModel::where('kd_dokter', $kddokter)
                ->where('kd_poli', $kdpoli)
                ->where('tgl_registrasi', $tglperiksa)
                ->orderBy('no_reg', 'DESC')
                ->value('no_reg');

            if (!$lastNoReg) {
                $lastNoRegNum = 1;
            } else {
                $lastNoRegNum = (int)$lastNoReg + 1;
            }
            //end last noreg

            //last no rawat
            $lastNoRawat = RegPeriksaModel::where('tgl_registrasi', $tglperiksa)
                ->orderBy('no_rawat', 'DESC')
                ->value('no_rawat');

            if (!$lastNoRawat) {
                $lastNoRawatNum = 1;
            } else {
                $lastNoRawatNum = (int)substr($lastNoRawat, -6) + 1;
            }
            //end last no rawat

            $regPeriksa = new RegPeriksaModel();
            $regPeriksa->no_reg = sprintf("%03d", $lastNoRegNum);
            $regPeriksa->no_rawat = date('Y/m/d', strtotime($tglperiksa)) . '/' . sprintf("%06d", $lastNoRawatNum);
            $regPeriksa->tgl_registrasi = $tglperiksa;
            $regPeriksa->jam_reg = $jamperiksa;
            $regPeriksa->kd_dokter = $kddokter;
            $regPeriksa->no_rkm_medis = $normedis;
            $regPeriksa->kd_poli = $kdpoli;
            $regPeriksa->p_jawab = $pasien->namakeluarga;
            $regPeriksa->almt_pj = $pasien->alamatpj;
            $regPeriksa->hubunganpj = $pasien->keluarga;
            $regPeriksa->biaya_reg = '0';
            $regPeriksa->stts = 'Belum';
            $regPeriksa->stts_daftar = (RegPeriksaModel::where('no_rkm_medis', $normedis)->count() < 1) ? 'Baru' : 'Lama';
            $regPeriksa->status_lanjut = 'Ralan';
            $regPeriksa->kd_pj = $cbayar;
            $regPeriksa->umurdaftar = $expCpasienumur[0];
            $regPeriksa->sttsumur = $expCpasienumur[1];
            $regPeriksa->status_bayar = 'Belum Bayar';
            $regPeriksa->status_poli = (RegPeriksaModel::where('no_rkm_medis', $normedis)->where('kd_poli', $kdpoli)->where('kd_dokter', $kddokter)->count() < 1) ? 'Baru' : 'Lama';
            $regPeriksa->save();

            $dData = DB::select("SELECT
                                    rp.no_rawat,
                                    p.no_peserta,
                                    p.no_ktp,
                                    p.no_tlp,
                                    mp.kd_poli_bpjs,

                                /* pasien baru atau bukan */
                                IF(
                                    (SELECT COUNT(*)
                                    FROM reg_periksa
                                    WHERE no_rkm_medis = p.no_rkm_medis
                                    ) = 0, '1','0'
                                ) AS pasienbaru,

                                    p.no_rkm_medis,
                                    rp.tgl_registrasi,
                                    md.kd_dokter_bpjs,
                                    j.jam_mulai AS jammulai,

                                CONCAT(
                                    DATE_FORMAT(j.jam_mulai, '%H:%i'),
                                    '-',
                                    DATE_FORMAT(j.jam_selesai, '%H:%i')
                                ) AS jampraktek,

                                j.kuota,

                                /* Hitung total kunjungan per tgl+poli+dokter */
                                (
                                    SELECT COUNT(*)
                                    FROM reg_periksa r2
                                    WHERE r2.tgl_registrasi = rp.tgl_registrasi
                                    AND r2.kd_poli        = rp.kd_poli
                                    AND r2.kd_dokter      = rp.kd_dokter
                                ) AS jumlah_kunjungan,

                                /* Hitung sisa kuota: kuota - jumlah kunjungan */
                                (
                                    j.kuota -
                                    (
                                    SELECT COUNT(*)
                                    FROM reg_periksa r2
                                    WHERE r2.tgl_registrasi = rp.tgl_registrasi
                                        AND r2.kd_poli        = rp.kd_poli
                                        AND r2.kd_dokter      = rp.kd_dokter
                                    )
                                ) AS sisa_kuota

                                FROM reg_periksa rp
                                JOIN pasien p
                                    ON rp.no_rkm_medis = p.no_rkm_medis
                                JOIN maping_dokter_dpjpvclaim md
                                    ON rp.kd_dokter = md.kd_dokter
                                JOIN maping_poli_bpjs mp
                                    ON rp.kd_poli = mp.kd_poli_rs
                                JOIN jadwal j
                                    ON j.kd_dokter = rp.kd_dokter
                                AND j.hari_kerja = '" . $hariperiksa . "'
                                WHERE
                                rp.no_rawat = '" . $regPeriksa->no_rawat . "'
                            ");

            //rp.no_rawat = '2025/12/15/000001'

            $jammulai = strtotime($regPeriksa->tgl_registrasi . ' ' . $dData[0]->jammulai);
            $eslayan = (int)config('confsistem.estimasi_layan') * (int)$regPeriksa->no_reg;
            $estimalayan = strtotime('+ ' . $eslayan . ' minutes', $jammulai) * 1000;

            $regAntrol = new ReferensiMobilejknBpjs();
            $regAntrol->nobooking = date('Ymd', strtotime($tglperiksa)) . sprintf("%06d", ReferensiMobilejknBpjs::where('tanggalperiksa', $tglperiksa)->count() + 1);
            $regAntrol->no_rawat = $regPeriksa->no_rawat;
            $regAntrol->nomorkartu = $dData[0]->no_peserta;
            $regAntrol->nik = $dData[0]->no_ktp;
            $regAntrol->nohp = $dData[0]->no_tlp;
            $regAntrol->kodepoli = $dData[0]->kd_poli_bpjs;
            $regAntrol->pasienbaru = $dData[0]->pasienbaru;
            $regAntrol->norm = $dData[0]->no_rkm_medis;
            $regAntrol->tanggalperiksa = $regPeriksa->tgl_registrasi;
            $regAntrol->kodedokter = $dData[0]->kd_dokter_bpjs;
            $regAntrol->jampraktek = $dData[0]->jampraktek;
            $regAntrol->jeniskunjungan = (int)$jkunj;
            $regAntrol->nomorreferensi = ($noref) ? $noref : '-';
            $regAntrol->nomorantrean = $regPeriksa->kd_poli . '-' . $regPeriksa->no_reg;
            $regAntrol->angkaantrean = (int)$regPeriksa->no_reg;
            $regAntrol->estimasidilayani = $estimalayan;
            $regAntrol->sisakuotajkn = $dData[0]->sisa_kuota;
            $regAntrol->kuotajkn = $dData[0]->kuota;
            $regAntrol->sisakuotanonjkn = $dData[0]->sisa_kuota;
            $regAntrol->kuotanonjkn = $dData[0]->kuota;
            $regAntrol->status = "Belum";
            $regAntrol->validasi = "0000-00-00 00:00:00";
            $regAntrol->statuskirim = "Belum";
            $regAntrol->save();

            DB::commit();

            $jsonAntrol = [
                "kodebooking" => $regAntrol->nobooking,
                "jenispasien" => $regPeriksa->kd_pj == 'BPJ' ? 'JKN' : 'NON JKN',
                "nomorkartu" => $regAntrol->nomorkartu,
                "nik" => $regAntrol->nik,
                "nohp" => $regAntrol->nohp,
                "kodepoli" => $regAntrol->kodepoli,
                "namapoli" => Poliklinik::join('maping_poli_bpjs', 'maping_poli_bpjs.kd_poli_rs', '=', 'poliklinik.kd_poli')->where('maping_poli_bpjs.kd_poli_bpjs', $regAntrol->kodepoli)->value('nm_poli'),
                "pasienbaru" => (int)$regAntrol->pasienbaru,
                "norm" => $regAntrol->norm,
                "tanggalperiksa" => $regAntrol->tanggalperiksa,
                "kodedokter" => (int)$regAntrol->kodedokter,
                "namadokter" => Dokter::join('maping_dokter_dpjpvclaim', 'maping_dokter_dpjpvclaim.kd_dokter', '=', 'dokter.kd_dokter')->where('maping_dokter_dpjpvclaim.kd_dokter_bpjs', $regAntrol->kodedokter)->value('nm_dokter'),
                "jampraktek" => $regAntrol->jampraktek,
                "jeniskunjungan" => (int)$regAntrol->jeniskunjungan,
                "nomorreferensi" => $regAntrol->nomorreferensi,
                "nomorantrean" => $regAntrol->nomorantrean,
                "angkaantrean" => (int)$regAntrol->angkaantrean,
                "estimasidilayani" => (int)$regAntrol->estimasidilayani,
                // "estimasidilayani" => (int)$regAntrol->estimasidilayani . ' | ' . date('Y-m-d H:i:s', $regAntrol->estimasidilayani / 1000),
                "sisakuotajkn" => $regAntrol->sisakuotajkn,
                "kuotajkn" => $regAntrol->kuotajkn,
                "sisakuotanonjkn" => $regAntrol->sisakuotanonjkn,
                "kuotanonjkn" => $regAntrol->kuotanonjkn,
                "keterangan" => "Peserta harap 30 menit lebih awal guna pencatatan administrasi."
            ];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => config('confsistem.addapi_url') . '/antrol/tambah-antrian.php', // your preferred url/
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($jsonAntrol),
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

            if ($dResponse['metadata']['code'] == 200) {
                ReferensiMobilejknBpjs::where('nobooking', $regAntrol->nobooking)->update([
                    'statuskirim' => 'Sudah'
                ]);

                return response()->json([
                    'code' => 200,
                    'message' => 'Data berhasil dibuat',
                    'data' => [
                        'nobooking' => $regAntrol->nobooking,
                        'norawat' => $regPeriksa->no_rawat,
                    ],
                    'token' => AuthHelper::genToken(),
                ]);
            } else {
                return response()->json([
                    'code' => 200,
                    'message' => 'Tersimpan, gagal kirim antrean ke BPJS No Rawat : ' . $regAntrol->no_rawat,
                    'data' => [
                        'norawat' => $regPeriksa->no_rawat,
                    ],
                    'token' => AuthHelper::genToken(),
                ]);
            }
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'code' => 400,
                'success' => "Error transaction",
                'message' => $e->getMessage()
            ]);
        }
    }

    public function addantrian(Request $request)
    {
        $regPeriksa = RegPeriksaModel::where('no_rawat', $request->norawat)->first();

        if (!$regPeriksa) {
            return response()->json([
                'code' => 208,
                'message' => 'No Rawat ' . $request->norawat . ' tidak ditemukan!',
                'token' => AuthHelper::genToken(),
            ]);
        } else {
            $regAntrol = ReferensiMobilejknBpjs::where('no_rawat', $regPeriksa->no_rawat)->first();

            $jsonAntrol = [
                "kodebooking" => $regAntrol->nobooking,
                "jenispasien" => $regPeriksa->kd_pj == 'BPJ' ? 'JKN' : 'NON JKN',
                "nomorkartu" => $regAntrol->nomorkartu,
                "nik" => $regAntrol->nik,
                "nohp" => $regAntrol->nohp,
                "kodepoli" => $regAntrol->kodepoli,
                "namapoli" => Poliklinik::join('maping_poli_bpjs', 'maping_poli_bpjs.kd_poli_rs', '=', 'poliklinik.kd_poli')->where('maping_poli_bpjs.kd_poli_bpjs', $regAntrol->kodepoli)->value('nm_poli'),
                "pasienbaru" => (int)$regAntrol->pasienbaru,
                "norm" => $regAntrol->norm,
                "tanggalperiksa" => $regAntrol->tanggalperiksa,
                "kodedokter" => (int)$regAntrol->kodedokter,
                "namadokter" => Dokter::join('maping_dokter_dpjpvclaim', 'maping_dokter_dpjpvclaim.kd_dokter', '=', 'dokter.kd_dokter')->where('maping_dokter_dpjpvclaim.kd_dokter_bpjs', $regAntrol->kodedokter)->value('nm_dokter'),
                "jampraktek" => $regAntrol->jampraktek,
                "jeniskunjungan" => (int)$regAntrol->jeniskunjungan,
                "nomorreferensi" => $regAntrol->nomorreferensi,
                "nomorantrean" => $regAntrol->nomorantrean,
                "angkaantrean" => (int)$regAntrol->angkaantrean,
                "estimasidilayani" => (int)$regAntrol->estimasidilayani,
                // "estimasidilayani" => (int)$regAntrol->estimasidilayani . ' | ' . date('Y-m-d H:i:s', $regAntrol->estimasidilayani / 1000),
                "sisakuotajkn" => $regAntrol->sisakuotajkn,
                "kuotajkn" => $regAntrol->kuotajkn,
                "sisakuotanonjkn" => $regAntrol->sisakuotanonjkn,
                "kuotanonjkn" => $regAntrol->kuotanonjkn,
                "keterangan" => "Peserta harap 30 menit lebih awal guna pencatatan administrasi."
            ];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => config('confsistem.addapi_url') . '/antrol/tambah-antrian.php', // your preferred url/
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($jsonAntrol),
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

            if ($dResponse['metadata']['code'] == 200) {
                ReferensiMobilejknBpjs::where('nobooking', $regAntrol->nobooking)->update([
                    'statuskirim' => 'Sudah'
                ]);

                return response()->json([
                    'code' => 200,
                    'message' => 'Pendaftaran Antrian berhasil',
                    'data' => [
                        'nobooking' => $regAntrol->nobooking,
                        'norawat' => $regPeriksa->no_rawat,
                    ],
                    'token' => AuthHelper::genToken(),
                ]);
            } else {
                return response()->json([
                    'code' => $dResponse['metadata']['code'],
                    'message' => $dResponse['metadata']['message'],
                    'token' => AuthHelper::genToken(),
                ]);
            }
        }
    }

    public function batalPeriksa(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'norawat'          => 'required',
                'ketbatal'            => 'required',
            ],
            [
                'norawat.required'         => 'No rawat tidak boleh kosong!',
                'ketbatal.required'           => 'Keterangan batal tidak boleh kosong!',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'code'    => 204,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $data = RegPeriksaModel::where('reg_periksa.no_rawat', $request->norawat)
            ->join('referensi_mobilejkn_bpjs', 'referensi_mobilejkn_bpjs.no_rawat', '=', 'reg_periksa.no_rawat')
            ->where('referensi_mobilejkn_bpjs.status', '!=', 'Batal')
            ->first();

        if (!$data) {
            return response()->json([
                'code' => 201,
                'message' => 'Data tidak ditemukan atau status bukan selain Batal.'
            ]);
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.addapi_url') . '/antrol/batal-antrian.php', // your preferred url/
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'kodebooking' => $data->nobooking,
                'keterangan' => $request->ketbatal
            ]),
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

        if ($dResponse['metadata']['code'] == 200) {
            //update status refmjkn
            if ($data && $data->no_rawat) {
                ReferensiMobilejknBpjs::where('nobooking', $data->nobooking)->update(['status' => 'Batal']);
                RegPeriksaModel::where('no_rawat', $data->no_rawat)->delete();

                return response()->json([
                    'code' => 200,
                    'message' => 'Pendaftaran Periksa No Rawat ' . $data->no_rawat . ' | nobooking ' . $data->nobooking . ' berhasil dibatalkan!',
                    'token' => AuthHelper::genToken(),
                ]);
            }
            //end update status refmjkn

            return response()->json([
                'code' => 201,
                'message' => 'Gagal membatalkan Pendaftaran Periksa No Rawat ' . $data->no_rawat,
                'token' => AuthHelper::genToken(),
            ]);
        } else {
            return response()->json([
                'code' => $dResponse['metadata']['code'],
                'message' => $dResponse['metadata']['message'],
                'token' => AuthHelper::genToken(),
            ]);
        }
    }

    public function addAntrianFarmasi(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'kodebooking'   => 'required|string',
                'jenisresep'    => 'required|string|in:tidak ada,racikan,non racikan',
                'keterangan'    => 'required|string',
            ],
            [
                'kodebooking.required'  => 'kodebooking tidak boleh kosong.',
                'jenisresep.required'   => 'jenisresep tidak boleh kosong.',
                'jenisresep.in'         => 'jenisresep hanya boleh diisi racikan atau non racikan.',
                'keterangan.required'   => 'keterangan tidak boleh kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'code'    => 204,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $nobooking = $request->kodebooking;

        //cek antrian
        $refAntrol = ReferensiMobilejknBpjs::where('nobooking', $nobooking)->first();

        if (!$refAntrol) {
            return response()->json([
                'code' => 201,
                'message' => 'Referensi Antrian tidak ditemukan'
            ]);
        }

        if ($refAntrol->status == 'Batal') {
            return response()->json([
                'code' => 201,
                'message' => 'Gagal proses, Pendaftaran antrian sudah dibatalkan!'
            ]);
        }

        $cari = IoReferensiAntrianFarmasi::find($nobooking);

        if ($cari) {
            return response()->json([
                'code' => 201,
                'message' => 'Antrian farmasi dengan nobooking ' . $nobooking . ' sudah ada!'
            ]);
        }

        $cariMax = IoReferensiAntrianFarmasi::where('tgl', $refAntrol->tanggalperiksa)->count();

        $data = [
            'nobooking' => $nobooking,
            'jenisresep' => $request->jenisresep,
            'nomorantrean' => (int)($cariMax + 1),
            'keterangan' => $request->keterangan,
            'tgl' => $refAntrol->tanggalperiksa
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.addapi_url') . '/antrol/tambah-antrian-farmasi.php', // your preferred url/
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

        if ($dResponse['metadata']['code'] == 200) {
            $data['validasi'] = Carbon::now()->format('Y-m-d H:i:s');

            IoReferensiAntrianFarmasi::create($data);

            return response()->json([
                'code' => 200,
                'message' => 'Pendaftaran Antrian Farmasi berhasil',
                'data' => [
                    'nobooking' => $data['nobooking'],
                    'noantrian' => $data['nomorantrean'],
                ],
                'token' => AuthHelper::genToken(),
            ]);
        } else {
            return response()->json([
                'code' => $dResponse['metadata']['code'],
                'message' => $dResponse['metadata']['message'],
                'token' => AuthHelper::genToken(),
            ]);
        }
    }
}
