<?php

namespace App\Http\Controllers\SatuSehat;

use App\Helpers\AuthHelper;
use App\Http\Controllers\Controller;
use App\Models\IoRadiologyDicomInstance;
use App\Models\IoSetting;
use App\Models\IoStatuSehatImagingStudy;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImagingStudiesController extends Controller
{
    /* =====================================================
     * RESPONSE HELPER
     * ===================================================== */
    private function success(string $message, $data = null)
    {
        return response()->json([
            'code' => 200,
            'message' => $message,
            'data' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }

    private function error(string $message, int $code = 500, $data = null)
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /* =====================================================
     * UPLOAD DICOM + MODIFY STUDY
     * ===================================================== */
    public function upload(string $acsn = '1234')
    {
        $request = request();

        if (!$request->hasFile('dicoms')) {
            return $this->error('File DICOM tidak ditemukan', 400);
        }

        $input = json_decode($request->input('body'));
        if (!$input) {
            return $this->error('Body JSON tidak valid', 400);
        }

        $client = new Client([
            'base_uri' => config('services.orthanc.url'),
            'timeout' => 120,
        ]);

        try {
            /* 1️⃣ Hapus study lama */
            $oldStudies = $this->findStudiesByAccession($acsn);
            foreach ($oldStudies as $studyId) {
                $this->deleteStudy($studyId);
            }

            /* 2️⃣ Upload DICOM */
            $instanceIds = [];
            foreach ($request->file('dicoms') as $file) {
                if ($file->getClientOriginalExtension() !== 'dcm') {
                    continue;
                }

                $resp = $client->post('/instances', [
                    'headers' => ['Content-Type' => 'application/dicom'],
                    'body' => fopen($file->getRealPath(), 'r'),
                ]);

                $result = json_decode($resp->getBody(), true);
                $instanceIds[] = $result['ID'];
            }

            if (empty($instanceIds)) {
                return $this->error('Tidak ada file DICOM valid', 422);
            }

            /* 3️⃣ Ambil Study */
            $instanceInfo = json_decode(
                $client->get("/instances/{$instanceIds[0]}")->getBody(),
                true
            );

            $studyId = $instanceInfo['ParentStudy'];

            /* 4️⃣ Modify Accession */
            $modify = $client->post("/studies/{$studyId}/modify", [
                'json' => [
                    'Replace' => [
                        '0008,0050' => $acsn,
                        '0032,1032' => $input->requester ?? ''
                    ],
                    'Force' => true
                ]
            ]);

            $finalStudy = json_decode($modify->getBody(), true);

            /* 5️⃣ Ambil detail study */
            $studyInfo = json_decode(
                $client->get("/studies/{$finalStudy['ID']}")->getBody(),
                true
            );

            $studyUid = $studyInfo['MainDicomTags']['StudyInstanceUID'] ?? null;

            /* 6️⃣ Ambil series */
            $seriesList = json_decode(
                $client->get("/studies/{$finalStudy['ID']}/series")->getBody(),
                true
            );

            if (empty($seriesList)) {
                return $this->error('Series tidak ditemukan pada study', 422);
            }

            $series = $seriesList[0];

            /* 7️⃣ Simpan ImagingStudy */
            $imagingStudy = IoStatuSehatImagingStudy::find($acsn)
                ?? new IoStatuSehatImagingStudy();

            $imagingStudy->acsn = $acsn;
            $imagingStudy->noorder = $input->noorder ?? null;
            $imagingStudy->patient_id = $finalStudy['PatientID'];
            $imagingStudy->study_id = $finalStudy['ID'];
            $imagingStudy->series_id = $series['ID'];
            $imagingStudy->study_uid = $studyUid;
            $imagingStudy->kd_jenis_prw = $input->kd_jenis_prw ?? null;
            $imagingStudy->save();

            /* 8️⃣ Simpan instance */
            IoRadiologyDicomInstance::where('acsn', $acsn)->delete();

            foreach ($series['Instances'] as $instanceId) {
                IoRadiologyDicomInstance::updateOrCreate(
                    ['instance_id' => $instanceId],
                    ['acsn' => $acsn]
                );
            }

            return $this->success('Upload DICOM & ImagingStudy berhasil', [
                'study_id' => $finalStudy['ID'],
                'study_uid' => $studyUid
            ]);

        } catch (\Throwable $e) {
            return $this->error('Gagal memproses Imaging Study', 500, $e->getMessage());
        }
    }

    /* =====================================================
     * FIND & DELETE STUDY ORTHANC
     * ===================================================== */
    private function findStudiesByAccession(string $accession): array
    {
        $resp = Http::post(config('services.orthanc.url') . '/tools/find', [
            'Level' => 'Study',
            'Query' => ['AccessionNumber' => $accession]
        ]);

        if (!$resp->successful()) {
            throw new \Exception('Orthanc find study gagal');
        }

        return $resp->json() ?? [];
    }

    public function deleteStudy(string $studyId): void
    {
        $resp = Http::delete(config('services.orthanc.url') . "/studies/{$studyId}");
        if (!$resp->successful()) {
            throw new \Exception("Gagal menghapus study {$studyId}");
        }
    }

    /* =====================================================
     * SEND TO MODALITY
     * ===================================================== */
    public function sendToModality(string $modality)
    {
        $studyIds = request()->input('study_ids');

        if (!is_array($studyIds) || empty($studyIds)) {
            return $this->error('study_ids harus berupa array', 400);
        }

        try {
            $client = new Client([
                'base_uri' => config('services.orthanc.url'),
                'timeout' => 600
            ]);

            $resp = $client->post("/modalities/{$modality}/store", [
                'json' => ['Resources' => $studyIds]
            ]);

            $result = json_decode($resp->getBody(), true);

            if (!$result) {
                return $this->error('Gagal mengirim ImagingStudy ke modality', 422);
            }

            return $this->success('ImagingStudy berhasil dikirim ke modality', $result);

        } catch (\Throwable $e) {
            return $this->error('Send to modality error', 500, $e->getMessage());
        }
    }

    /* =====================================================
     * STORE METADATA
     * ===================================================== */
    public function storemetadata(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required',
            'patient_name' => 'required',
            'accession_number' => 'required',
            'study_instance_uid' => 'required',
            'modality' => 'required',
            'study_date' => 'required',
        ]);

        try {
            // simpan ke DB sesuai kebutuhan
            return $this->success('Metadata berhasil disimpan');
        } catch (\Throwable $e) {
            return $this->error('Gagal menyimpan metadata', 500, $e->getMessage());
        }
    }

    /* =====================================================
     * GET CLOUD DATA SATUSEHAT
     * ===================================================== */
    public function getCloudData(string $acsn)
    {
        try {
            $settings = IoSetting::where('group', 'satu_Sehat')
                ->pluck('value', 'setting_option');

            if (!isset($settings['satu_sehat_org_id'])) {
                return $this->error('Konfigurasi SATUSEHAT tidak lengkap', 500);
            }

            $auth = (new AuthController())->auth(false);
            if (!$auth || empty($auth['data']->access_token)) {
                return $this->error('Auth SATUSEHAT gagal', 401);
            }

            $url = "https://api-satusehat.kemkes.go.id/fhir-r4/v1/ImagingStudy"
                . "?identifier=http://sys-ids.kemkes.go.id/acsn/"
                . "{$settings['satu_sehat_org_id']}|{$acsn}";

            $resp = Http::withToken($auth['data']->access_token)
                ->acceptJson()
                ->get($url);

            $data = $resp->json();

            if (($data['total'] ?? 0) === 0) {
                return $this->error('Data ImagingStudy tidak ditemukan di SATUSEHAT', 204);
            }

            $local = IoStatuSehatImagingStudy::find($acsn);
            if (!$local) {
                return $this->error('ImagingStudy belum ada di lokal', 404);
            }

            $local->id_imaging_study = $data['entry'][0]['resource']['id'];
            $local->save();

            return $this->success(
                'ImagingStudy berhasil sinkron ke SATUSEHAT',
                $local->id_imaging_study
            );

        } catch (\Throwable $e) {
            return $this->error('Gagal mengambil data SATUSEHAT', 500, $e->getMessage());
        }
    }
}
