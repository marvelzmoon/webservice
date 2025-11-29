<?php

namespace App\Http\Controllers\Erm\DataKlinis;

use App\Helpers\AuthHelper;
use App\Http\Controllers\Controller;
use App\Models\BridgingSep;
use App\Models\DiagnosaPasien;
use App\Models\Dokter;
use App\Models\IoAssessmentPoliAccess;
use App\Models\Pegawai;
use App\Models\PemeriksaanRalan;
use App\Models\PenilaianAwalKeperawatanIgd;
use App\Models\PenilaianAwalKeperawatanKebidanan;
use App\Models\PenilaianAwalKeperawatanRalan;
use App\Models\PenilaianAwalKeperawatanRalanBayi;
use App\Models\PenilaianMedisIgd;
use App\Models\PenilaianMedisRalan;
use App\Models\PenilaianMedisRalanAnak;
use App\Models\PenilaianMedisRalanBedah;
use App\Models\PenilaianMedisRalanObgyn;
use App\Models\PenilaianMedisRalanOrthopedi;
use App\Models\PenilaianMedisRalanPenyakitDalam;
use App\Models\PenilaianMedisRalanRehabMedik;
use App\Models\PenilaianMedisRalanTht;
use App\Models\RegPeriksaModel;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RiwayatController extends Controller
{
    public function getdata(Request $request)
    {
        $rules = [
            'no_rkm_medis' => 'required|string',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'string'   => ':attribute harus berupa string',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $cari = RegPeriksaModel::with('dokter')->with('poli')->with('penjab')
            ->where("no_rkm_medis", $request->no_rkm_medis)
            ->where('tgl_registrasi', '>=', now()->subYears(5)->format('Y-m-d'))
            ->orderBy('tgl_registrasi', 'DESC')
            ->get();

        if ($cari->isEmpty()) {
            return response()->json([
                'code'    => 204,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        $dokterCache = [];
        $poliCache = [];
        $penjabCache = [];
        $data = [];

        foreach ($cari as $v) {
            // Cache dokter untuk menghindari query berulang
            if (!isset($dokterCache[$v->kd_dokter])) {
                $dokterCache[$v->kd_dokter] =
                    $v->dokter ? $v->dokter->only(['kd_dokter', 'nm_dokter']) : null;
            }

            if (!isset($poliCache[$v->kd_poli])) {
                $poliCache[$v->kd_poli] =
                    $v->poli ? $v->poli->only(['kd_poli', 'nm_poli']) : null;
            }

            if (!isset($penjabCache[$v->kd_pj])) {
                $penjabCache[$v->kd_pj] =
                    $v->penjab ? $v->penjab->only(['kd_pj', 'png_jawab']) : null;
            }

            $data[] = [
                'no_rawat' => $v->no_rawat,
                'tgl'      => $v->tgl_registrasi,
                'jam'      => $v->jam_reg,
                'dokter'   => $dokterCache[$v->kd_dokter],
                'umur'     => $v->umurdaftar . ' ' . $v->sttsumur,
                'unit'     => $poliCache[$v->kd_poli],
                'penjab'     => $penjabCache[$v->kd_pj],
            ];
        }

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'counter' => $cari->count(),
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    public function soapie(Request $request)
    {
        $rules = [
            'norawat' => 'required|string',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'string'   => ':attribute harus berupa string',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $noRawat = $request->norawat;

        // Ambil data pemeriksaan untuk satu no_rawat
        $allPemeriksaan = PemeriksaanRalan::with('pegawai')
            ->where('no_rawat', $noRawat)
            ->orderBy('tgl_perawatan')
            ->orderBy('jam_rawat')
            ->get();

        if ($allPemeriksaan->isEmpty()) {
            return response()->json([
                'code' => 204,
                'message' => 'Data tidak ditemukan'
            ]);
        }

        // Cache pegawai satu kali saja
        $pegawaiCache = [];

        $soap = [];

        foreach ($allPemeriksaan as $v) {

            if (!isset($pegawaiCache[$v->nip])) {
                $pegawaiCache[$v->nip] = $v->pegawai
                    ? $v->pegawai->only(['nik', 'nama'])
                    : null;
            }

            $soap[] = [
                'no_rawat'        => $v->no_rawat,
                'tgl_perawatan'   => $v->tgl_perawatan,
                'jam_rawat'       => $v->jam_rawat,
                'suhu_tubuh'      => $v->suhu_tubuh,
                'tensi'           => $v->tensi,
                'nadi'            => $v->nadi,
                'respirasi'       => $v->respirasi,
                'tinggi'          => $v->tinggi,
                'berat'           => $v->berat,
                'spo2'            => $v->spo2,
                'gcs'             => $v->gcs,
                'kesadaran'       => $v->kesadaran,
                'keluhan'         => $v->keluhan,
                'pemeriksaan'     => $v->pemeriksaan,
                'alergi'          => $v->alergi,
                'lingkar_perut'   => $v->lingkar_perut,
                'rtl'             => $v->rtl,
                'penilaian'       => $v->penilaian,
                'instruksi'       => $v->instruksi,
                'evaluasi'        => $v->evaluasi,
                'pegawai'         => $pegawaiCache[$v->nip],
            ];
        }

        $dataResponse = [
            'no_rawat'  => $noRawat,
            'tgl'       => $allPemeriksaan->first()->tgl_perawatan,
            'status'    => 'Rawat Jalan',
            'S.O.A.P.I.E' => $soap,
        ];

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mengambil data',
            'data'    => $dataResponse,
            'token'   => AuthHelper::genToken(),
        ]);
    }

    public function soapiemulti(Request $request)
    {
        $rules = [
            'norawat' => 'required|array',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'array'    => ':attribute harus berupa array'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $norawatList = $request->norawat;

        // Ambil semua data pemeriksaan sekaligus (EAGER LOADING)
        $allPemeriksaan = PemeriksaanRalan::with('pegawai')
            ->whereIn('no_rawat', $norawatList)
            ->orderBy('tgl_perawatan')
            ->orderBy('jam_rawat')
            ->get();

        // Cache pegawai global untuk menghindari query ulang
        $pegawaiCache = [];

        $dataResponse = [];

        // proses setiap no_rawat
        foreach ($norawatList as $noRawat) {

            // Ambil pemeriksaan untuk rawat tertentu
            $items = $allPemeriksaan->where('no_rawat', $noRawat);

            if ($items->isEmpty()) {
                $dataResponse[] = [
                    'no_rawat' => $noRawat,
                    'status'   => 'Data tidak ditemukan',
                    'tgl'      => null,
                    'S.O.A.P.I.E' => []
                ];
                continue;
            }

            $soap = [];

            foreach ($items as $v) {
                // Cek cache pegawai supaya tidak query ulang
                if (!isset($pegawaiCache[$v->nip])) {
                    $pegawaiCache[$v->nip] = $v->pegawai
                        ? $v->pegawai->only(['nik', 'nama'])
                        : null;
                }

                $soap[] = [
                    'no_rawat' => $v->no_rawat,
                    'tgl_perawatan' => $v->tgl_perawatan,
                    'jam_rawat' => $v->jam_rawat,
                    'suhu_tubuh' => $v->suhu_tubuh,
                    'tensi' => $v->tensi,
                    'nadi' => $v->nadi,
                    'respirasi' => $v->respirasi,
                    'tinggi' => $v->tinggi,
                    'berat' => $v->berat,
                    'spo2' => $v->spo2,
                    'gcs' => $v->gcs,
                    'kesadaran' => $v->kesadaran,
                    'keluhan' => $v->keluhan,
                    'pemeriksaan' => $v->pemeriksaan,
                    'alergi' => $v->alergi,
                    'lingkar_perut' => $v->lingkar_perut,
                    'rtl' => $v->rtl,
                    'penilaian' => $v->penilaian,
                    'instruksi' => $v->instruksi,
                    'evaluasi' => $v->evaluasi,
                    'pegawai' => $pegawaiCache[$v->nip]
                ];
            }

            $dataResponse[] = [
                'no_rawat' => $noRawat,
                'tgl'      => $items->first()->tgl_perawatan,
                'status'   => 'Rawat Jalan',
                'S.O.A.P.I.E' => $soap,
            ];
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mengambil data',
            'data'    => $dataResponse,
            'token' => AuthHelper::genToken(),
        ]);
    }

    public function datasep(Request $request)
    {
        $rules = [
            'norawat' => 'required|string',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'string'   => ':attribute harus berupa string',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $noRawat = $request->norawat;
        $cari = BridgingSep::where('no_rawat', $noRawat)->first();

        if (!$cari) {
            return response()->json([
                'code'      => 204,
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data = [
            'noKartu' => $cari->no_kartu,
            'noSep' => $cari->no_sep,
            "ppkPelayanan" => $cari->kdppkpelayanan . ' - ' . $cari->nmppkpelayanan,
            'tgl' => $cari->tglsep,
            'jnsPelayanan' => [
                1 => '1. r.inap',
                2 => '2. r.jalan'
            ][$cari->jnspelayanan] ?? "",
            'klsRawat' => [
                'klsRawatHak' => [
                    1 => 'Kelas 1',
                    2 => 'Kelas 2',
                    3 => 'Kelas 3'
                ][$cari->klsrawat] ?? "",
                'klsRawatNaik' => [
                    1 => '1. VVIP',
                    2 => '2. VIP',
                    3 => '3. Kelas 1',
                    4 => '4. Kelas 2',
                    5 => '5. Kelas 3',
                    6 => '6. ICCU',
                    7 => '7. ICU',
                    8 => '8. Diatas Kelas 1'
                ][$cari->klsnaik] ?? "",
                'pembiayaan' => [
                    1 => '1. Pribadi',
                    2 => '2. Pemberi Kerja',
                    3 => '3. Asuransi Kesehatan Tambahan'
                ][$cari->pembiayaan] ?? "",
                'penanggungJawab' => $cari->pjnaikkelas,
            ],
            'noMR' => $cari->nomr,
            'rujukan' => [
                "asalRujukan" => [
                    1 => '1.Faskes 1',
                    2 => '2.Faskes 2(RS)'
                ][$cari->asal_rujukan] ?? "",
                "tglRujukan" => $cari->tglrujukan,
                "noRujukan" => $cari->no_rujukan,
                "ppkRujukan" => $cari->kdppkrujukan . ' - ' . $cari->nmppkrujukan
            ],
            "catatan" => $cari->catatan,
            "diagAwal" => $cari->diagawal . ' ' . $cari->nmdiagnosaawal,
            "poli" => [
                "tujuan" => $cari->kdpolitujuan . ' - ' . $cari->nmpolitujuan,
                "eksekutif" => [0 => '0. Tidak', 1 => '1.Ya'][$cari->eksekutif] ?? ""
            ],
            "cob" => [
                "cob" => [
                    0 => '0.Tidak',
                    1 => '1. Ya'
                ][$cari->cob] ?? ""
            ],
            "katarak" => [
                "katarak" => [
                    0 => '0.Tidak',
                    1 => '1.Ya'
                ][$cari->katarak] ?? ""
            ],
            "jaminan" => [
                "lakaLantas" => [
                    0 => '0 : Bukan Kecelakaan lalu lintas [BKLL]',
                    1 => '1 : KLL dan bukan kecelakaan Kerja [BKK]',
                    2 => '2 : KLL dan KK',
                    3 => '3 : KK'
                ][$cari->lakalantas] ?? "",
                // "noLP":"{No. LP}",
                "penjamin" => [
                    "tglKejadian" => $cari->tglkkl,
                    "keterangan" => $cari->keterangankkl,
                    "suplesi" => [
                        "suplesi" => [
                            0 => '0.Tidak',
                            1 => '1. Ya'
                        ][$cari->suplesi] ?? "",
                        "noSepSuplesi" => $cari->no_sep_suplesi,
                        "lokasiLaka" => [
                            "kdPropinsi" => $cari->kdprop . ' - ' . $cari->nmprop,
                            "kdKabupaten" => $cari->kdkab . ' - ' . $cari->nmkab,
                            "kdKecamatan" => $cari->nmkec . ' - ' . $cari->nmkec
                        ]
                    ]
                ]
            ],
            "tujuanKunj" => [
                0 => 'Normal',
                1 => 'Prosedur',
                2 => 'Konsul Dokter'
            ][$cari->tujuankunjungan] ?? "",
            "flagProcedure" => [
                0 => 'Prosedur Tidak Berkelanjutan',
                1 => 'Prosedur dan Terapi Berkelanjutan'
            ][$cari->flagprosedur] ?? "",
            "kdPenunjang" => [
                1 => 'Radioterapi',
                2 => 'Kemoterapi',
                3 => 'Rehabilitasi Medik',
                4 => 'Rehabilitasi Psikososial',
                5 => 'Transfusi Darah',
                6 => 'Pelayanan Gigi',
                7 => 'Laboratorium',
                8 => 'USG',
                9 => 'Farmasi',
                10 => 'Lain-Lain',
                11 => 'MRI',
                12 => 'HEMODIALISA'
            ][$cari->penunjang] ?? "",
            "assesmentPel" => [
                1 => 'Poli spesialis tidak tersedia pada hari sebelumnya',
                2 => 'Jam Poli telah berakhir pada hari sebelumnya',
                3 => 'Dokter Spesialis yang dimaksud tidak praktek pada hari sebelumnya',
                4 => 'Atas Instruksi RS',
                5 => 'Tujuan Kontrol'
            ][$cari->asesmenpelayanan] ?? "",
            "skdp" => [
                "noSurat" => $cari->noskdp,
                "kodeDPJP" => $cari->kddpjp . ' - ' . $cari->nmdpdjp
            ],
            "dpjpLayan" => $cari->kddpjplayanan . ' - ' . $cari->nmdpjplayanan,
            "noTelp" => $cari->notelep,
            "user" => $cari->user,
        ];

        return response()->json([
            'code' => 200,
            'message' => 'OK',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    public function awalMedis(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'norawat' => 'required|string',
        ], [
            'required' => ':attribute tidak boleh kosong',
            'string'   => ':attribute harus berupa string',
        ]);

        $noRawat = $validated['norawat'];

        // Ambil data poli berdasarkan noRawat
        $poli = RegPeriksaModel::find($noRawat);

        if (!$poli) {
            return response()->json([
                'code'    => 204,
                'message' => 'Data tidak ditemukan',
            ]);
        }

        $kdPoli = $poli->kd_poli;

        $poliHandlers = IoAssessmentPoliAccess::where('kd_poli', $kdPoli)->value('asessment');

        if (!$poliHandlers) {
            return response()->json([
                'code'    => 204,
                'message' => 'Data tidak ditemukan',
            ]);
        }

        $handler = str_replace('-', '_', $poliHandlers);

        // Pastikan function-nya ada
        if (!method_exists($this, $handler)) {
            return response()->json([
                'code'    => 500,
                'message' => "Handler untuk poli $kdPoli belum dibuat: $handler",
            ]);
        }

        // Jalankan handler secara dinamis
        return $this->{$handler}($noRawat);
    }

    private function emergency($id)
    {
        $cari = PenilaianMedisIgd::find($id);

        if (!$cari) {
            return response()->json([
                'code'      => 204,
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data = Arr::except($cari->toArray(), ['kd_dokter']);
        $data['dokter'] = Dokter::select('kd_dokter', 'nm_dokter')->where('kd_dokter', $cari->kd_dokter)->first();

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    private function neurology($id)
    {
        return $this->internal_disease($id);
    }

    private function orthopedics($id)
    {
        $cari = PenilaianMedisRalanOrthopedi::find($id);

        if (!$cari) {
            return response()->json([
                'code'      => 204,
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data = Arr::except($cari->toArray(), ['kd_dokter']);
        $data['dokter'] = Dokter::select('kd_dokter', 'nm_dokter')->where('kd_dokter', $cari->kd_dokter)->first();

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    private function child($id)
    {
        $cari = PenilaianMedisRalanAnak::find($id);

        if (!$cari) {
            return response()->json([
                'code'      => 204,
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data = Arr::except($cari->toArray(), ['kd_dokter']);
        $data['dokter'] = Dokter::select('kd_dokter', 'nm_dokter')->where('kd_dokter', $cari->kd_dokter)->first();

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    private function obgyn($id)
    {
        $cari = PenilaianMedisRalanObgyn::find($id);

        if (!$cari) {
            return response()->json([
                'code'      => 204,
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data = Arr::except($cari->toArray(), ['kd_dokter']);
        $data['dokter'] = Dokter::select('kd_dokter', 'nm_dokter')->where('kd_dokter', $cari->kd_dokter)->first();

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    private function internal_disease($id)
    {
        $cari = PenilaianMedisRalanPenyakitDalam::find($id);

        if (!$cari) {
            return response()->json([
                'code'      => 204,
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data = Arr::except($cari->toArray(), ['kd_dokter']);
        $data['dokter'] = Dokter::select('kd_dokter', 'nm_dokter')->where('kd_dokter', $cari->kd_dokter)->first();

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    private function common_surgery($id)
    {
        $cari = PenilaianMedisRalanBedah::find($id);

        if (!$cari) {
            return response()->json([
                'code'      => 204,
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data = Arr::except($cari->toArray(), ['kd_dokter']);
        $data['dokter'] = Dokter::select('kd_dokter', 'nm_dokter')->where('kd_dokter', $cari->kd_dokter)->first();

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    private function tht($id)
    {
        $cari = PenilaianMedisRalanTht::find($id);

        if (!$cari) {
            return response()->json([
                'code'      => 204,
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data = Arr::except($cari->toArray(), ['kd_dokter']);
        $data['dokter'] = Dokter::select('kd_dokter', 'nm_dokter')->where('kd_dokter', $cari->kd_dokter)->first();

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    private function medical_rehabilitation($id)
    {
        $cari = PenilaianMedisRalanRehabMedik::find($id);

        if (!$cari) {
            return response()->json([
                'code'      => 204,
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data = Arr::except($cari->toArray(), ['kd_dokter']);
        $data['dokter'] = Dokter::select('kd_dokter', 'nm_dokter')->where('kd_dokter', $cari->kd_dokter)->first();

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    public function awalKeperawatan(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'norawat' => 'required|string',
        ], [
            'required' => ':attribute tidak boleh kosong',
            'string'   => ':attribute harus berupa string',
        ]);

        $noRawat = $validated['norawat'];

        // Ambil data poli berdasarkan noRawat
        $poli = RegPeriksaModel::find($noRawat);

        if (!$poli) {
            return response()->json([
                'code'    => 204,
                'message' => 'Data tidak ditemukan',
            ]);
        }

        $kdPoli = $poli->kd_poli;

        $poliHandlers = IoAssessmentPoliAccess::where('kd_poli', $kdPoli)->value('assessment_nurse');

        if (!$poliHandlers) {
            return response()->json([
                'code'    => 204,
                'message' => 'Tidak ada mapping assessment keperawatan',
            ]);
        }

        $handler = str_replace('-', '_', $poliHandlers);

        // Pastikan function-nya ada
        if (!method_exists($this, $handler)) {
            return response()->json([
                'code'    => 500,
                'message' => "Handler untuk poli $kdPoli belum dibuat: $handler",
            ]);
        }

        // Jalankan handler secara dinamis
        return $this->{$handler}($noRawat);
    }

    private function nurse_common_disease($id)
    {
        $cari = PenilaianAwalKeperawatanRalan::find($id);

        if (!$cari) {
            return response()->json([
                'code'      => 204,
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data = Arr::except($cari->toArray(), ['nip']);
        $data['pegawai'] = Pegawai::select('nik', 'nama')->where('nik', $cari->nip)->first();

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    private function nurse_baby($id)
    {
        $cari = PenilaianAwalKeperawatanRalanBayi::find($id);

        if (!$cari) {
            return response()->json([
                'code'      => 204,
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data = Arr::except($cari->toArray(), ['nip']);
        $data['pegawai'] = Pegawai::select('nik', 'nama')->where('nik', $cari->nip)->first();
        $data['masalah_keperawatan'] = DB::table('penilaian_awal_keperawatan_ralan_bayi_masalah')
            ->where('no_rawat', $cari->no_rawat)
            ->join('master_masalah_keperawatan_anak', 'master_masalah_keperawatan_anak.kode_masalah', '=', 'penilaian_awal_keperawatan_ralan_bayi_masalah.kode_masalah')
            ->first();
        $data['rencana_keperawatan'] = DB::table('penilaian_awal_keperawatan_ralan_rencana_anak')
            ->where('no_rawat', $cari->no_rawat)
            ->join('master_rencana_keperawatan_anak', 'master_rencana_keperawatan_anak.kode_rencana', '=', 'penilaian_awal_keperawatan_ralan_rencana_anak.kode_rencana')
            ->first();

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    private function nurse_obgyn($id)
    {
        $cari = PenilaianAwalKeperawatanKebidanan::find($id);

        if (!$cari) {
            return response()->json([
                'code'      => 204,
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data = Arr::except($cari->toArray(), ['nip']);
        $data['pegawai'] = Pegawai::select('nik', 'nama')->where('nik', $cari->nip)->first();

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    private function nurse_emergency($id)
    {
        $cari = PenilaianAwalKeperawatanIgd::find($id);

        if (!$cari) {
            return response()->json([
                'code'      => 204,
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data = Arr::except($cari->toArray(), ['nip']);
        $data['pegawai'] = Pegawai::select('nik', 'nama')->where('nik', $cari->nip)->first();
        $data['masalah_keperawatan'] = DB::table('penilaian_awal_keperawatan_igd_masalah')
            ->where('no_rawat', $cari->no_rawat)
            ->join('master_masalah_keperawatan_igd', 'master_masalah_keperawatan_igd.kode_masalah', '=', 'penilaian_awal_keperawatan_igd_masalah.kode_masalah')
            ->first();

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    public function diagnosaIcd10(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'norawat' => 'required|string',
        ], [
            'required' => ':attribute tidak boleh kosong',
            'string'   => ':attribute harus berupa string',
        ]);

        $noRawat = $validated['norawat'];

        $cari = DiagnosaPasien::where('no_rawat', $noRawat)->orderBy('prioritas', 'asc')->get();

        if ($cari->isEmpty()) {
            return response()->json([
                'code'    => 204,
                'message' => 'Data tidak ditemukan',
            ]);
        }

        $data = [];
        $diagnosaCache = [];

        foreach ($cari as $v) {
            if (!isset($diagnosaCache[$v->kd_penyakit])) {
                $diagnosaCache[$v->kd_penyakit] = $v->kd_penyakit
                    ? $v->penyakit
                    : null;
            }

            $data[] = [
                'no_rawat' => $v->no_rawat,
                'status' => $v->status,
                'prioritas' => $v->prioritas,
                'status_penyakit' => $v->status_penyakit,
                'penyakit' => $diagnosaCache[$v->kd_penyakit],
            ];
        }

        return $data;
    }
}
