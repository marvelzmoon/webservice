<?php

namespace App\Http\Controllers\SatuSehat;

use App\Http\Controllers\Controller;
use App\Models\IoSetting;
use App\Models\SatuSehatEncounter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class EncounterController extends Controller
{
    /* =====================================================
     * STORE ENCOUNTER SATUSEHAT
     * ===================================================== */
    public function store(Request $request)
    {
        /* ================= VALIDATION ================= */
        $validator = Validator::make($request->all(), [
            'no_rawat'        => 'required|string',
            'patient_id'      => 'required|string',
            'patient_name'    => 'required|string',
            'practitioner_id' => 'required|string',
            'practitioner_name'=> 'required|string',
            'location_poli_id'=> 'required|string',
            'poli_name'       => 'required|string',
            'register_time'   => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 422,
                'message' => 'Validasi gagal',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            /* ================= LOAD CONFIG ================= */
            $settings = IoSetting::where('group', 'satu_Sehat')
                ->pluck('value', 'setting_option');

            if (empty($settings['satu_sehat_org_id'])) {
                return response()->json([
                    'code' => 500,
                    'message' => 'Konfigurasi SATUSEHAT tidak lengkap',
                    'data' => null
                ], 500);
            }

            /* ================= AUTH ================= */
            $auth = (new AuthController())->auth(false);

            if (!$auth || empty($auth['data']->access_token)) {
                return response()->json([
                    'code' => 401,
                    'message' => 'Gagal autentikasi SATUSEHAT',
                    'data' => null
                ], 401);
            }

            /* ================= BUILD PAYLOAD ================= */
            $payload = [
                'resourceType' => 'Encounter',
                'identifier' => [[
                    'system' => 'http://sys-ids.kemkes.go.id/encounter/' . $settings['satu_sehat_org_id'],
                    'value' => $request->no_rawat
                ]],
                'status' => 'arrived',
                'class' => [
                    'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                    'code' => 'AMB',
                    'display' => 'ambulatory'
                ],
                'subject' => [
                    'reference' => 'Patient/' . $request->patient_id,
                    'display' => $request->patient_name
                ],
                'participant' => [[
                    'type' => [[
                        'coding' => [[
                            'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                            'code' => 'ATND',
                            'display' => 'attender'
                        ]]
                    ]],
                    'individual' => [
                        'reference' => 'Practitioner/' . $request->practitioner_id,
                        'display' => $request->practitioner_name
                    ]
                ]],
                'period' => [
                    'start' => $request->register_time
                ],
                'location' => [[
                    'location' => [
                        'reference' => 'Location/' . $request->location_poli_id,
                        'display' => $request->poli_name
                    ]
                ]],
                'statusHistory' => [[
                    'status' => 'arrived',
                    'period' => [
                        'start' => $request->register_time
                    ]
                ]],
                'serviceProvider' => [
                    'reference' => 'Organization/' . $settings['satu_sehat_org_id']
                ]
            ];

            /* ================= SEND TO SATUSEHAT ================= */
            $response = Http::withToken($auth['data']->access_token)
                ->acceptJson()
                ->post('https://api-satusehat.kemkes.go.id/fhir-r4/v1/Encounter',$payload);

            if (!$response->successful()) {
                return response()->json([
                    'code' => $response->status(),
                    'message' => 'SATUSEHAT menolak request Encounter',
                    'data' => $response->json()
                ], $response->status());
            }

            $data = $response->json();

            if (empty($data['id'])) {
                return response()->json([
                    'code' => 500,
                    'message' => 'Response SATUSEHAT tidak valid',
                    'data' => $data
                ], 500);
            }

            /* ================= SAVE LOCAL ================= */
            $encounter = SatuSehatEncounter::find($request->no_rawat)
                ?? new SatuSehatEncounter();

            $encounter->no_rawat = $request->no_rawat;
            $encounter->id_encounter = $data['id'];
            $encounter->save();

            return response()->json([
                'code' => 200,
                'message' => 'Encounter berhasil dikirim ke SATUSEHAT',
                'data' => $encounter
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'code' => 500,
                'message' => 'Terjadi kesalahan saat memproses Encounter',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
