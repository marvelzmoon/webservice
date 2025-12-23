<?php

namespace App\Http\Controllers\SatuSehat;

use App\Helpers\AuthHelper;
use App\Http\Controllers\Controller;
use App\Models\IoSetting;
use App\Models\SatuSehatServiceRequestRadiologi;

class RadiologiRequestController extends Controller
{
    public function store()
    {   
        $request = request();
        $sId = IoSetting::where('group', 'satu_Sehat')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }
        $auth = new AuthController();
        $auth = $auth->auth(false);
        $code = 200;
        $message = 'Ok';

        $requestBody=[
            'resourceType' => 'ServiceRequest',
            'identifier' => [
                [
                    'system' => 'http://sys-ids.kemkes.go.id/servicerequest/'.$satu_sehat_org_id,
                    'value' => $request->acsn
                ],
                [
                    'use' => 'usual',
                    'type' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v2-0203',
                                'code' => 'ACSN'
                            ]
                        ]
                    ],
                    'system' => 'http://sys-ids.kemkes.go.id/acsn/'.$satu_sehat_org_id,
                    'value' => $request->acsn
                ]
            ],
            'status' => 'active',
            'intent' => 'order',
            'category' => [
                [
                    'coding' => [
                        [
                            'system' => 'http://snomed.info/sct',
                            'code' => '363679005',
                            'display' => 'Imaging'
                        ]
                    ]
                ]
            ],
            'code' => [
                'coding' => [
                    [
                        'system' => $request->exmination_system,
                        'code' => $request->exmination_code,
                        'display' => $request->exmination_display
                    ]
                    // [
                    //     'system' => $request->sample_system,
                    //     'code' => $request->sample_code,
                    //     'display' => $request->sample_display
                    // ]
                ],
                'text' => $request->exam_name
            ],
            'subject' => [
                'reference' => 'Patient/'.$request->patient_id,
            ],
            'encounter' => [
                'reference' => 'Encounter/'.$request->encounter_id,
                'display'=> "Permintaan $request->exam_name pasien $request->mrid - $request->patient_name No.Rawat $request->register_id $request->time"
            ],
            'authoredOn'=>$request->time,
            'requester' => [
                'reference' => 'Practitioner/'.$request->practitioner_id,
                'display' => $request->practitioner_name
            ],
            'performer' => [
                [
                    'reference' => 'Organization/'.$satu_sehat_org_id,
                    'display' => 'Ruang Radiologi/Petugas Radiologi'
                ]
            ],
            'reasonCode' => [
                [
                    'text' => $request->clinical_diagnosis
                ]
            ],
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api-satusehat.kemkes.go.id/fhir-r4/v1/ServiceRequest',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>json_encode($requestBody),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$auth['data']->access_token,
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $data = json_decode($response);
        $serviceRequest = SatuSehatServiceRequestRadiologi::
        where('noorder','=',$request->noorder)->
        where('kd_jenis_prw','=',$request->kd_jenis_prw)->
        first();
        $serviceRequest?:$serviceRequest=new SatuSehatServiceRequestRadiologi();
        $serviceRequest->noorder = $request->noorder;
        $serviceRequest->kd_jenis_prw = $request->kd_jenis_prw;
        $serviceRequest->id_servicerequest = $request->kd_jenis_prw;
        $serviceRequest->save();
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $serviceRequest,
            'request' => $data,
        ]);
    }
    public function update()
    {   
        $request = request();
        $sId = IoSetting::where('group', 'satu_Sehat')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }
        $auth = new AuthController();
        $auth = $auth->auth(false);
        $code = 200;
        $message = 'Ok';

        $requestBody=[
            'resourceType' => 'ServiceRequest',
            'id'=>$request->id,
            'identifier' => [
                [
                    'system' => 'http://sys-ids.kemkes.go.id/servicerequest/'.$satu_sehat_org_id,
                    'value' => $request->acsn
                ],
                [
                    'use' => 'usual',
                    'type' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v2-0203',
                                'code' => 'ACSN'
                            ]
                        ]
                    ],
                    'system' => 'http://sys-ids.kemkes.go.id/acsn/'.$satu_sehat_org_id,
                    'value' => $request->acsn
                ]
            ],
            'status' => 'active',
            'intent' => 'order',
            'category' => [
                [
                    'coding' => [
                        [
                            'system' => 'http://snomed.info/sct',
                            'code' => '363679005',
                            'display' => 'Imaging'
                        ]
                    ]
                ]
            ],
            'code' => [
                'coding' => [
                    [
                        'system' => $request->exmination_system,
                        'code' => $request->exmination_code,
                        'display' => $request->exmination_display
                    ]
                    // [
                    //     'system' => $request->sample_system,
                    //     'code' => $request->sample_code,
                    //     'display' => $request->sample_display
                    // ]
                ],
                'text' => $request->exam_name
            ],
            'subject' => [
                'reference' => 'Patient/'.$request->patient_id,
            ],
            'encounter' => [
                'reference' => 'Encounter/'.$request->encounter_id,
                'display'=> "Permintaan $request->exam_name pasien $request->mrid - $request->patient_name No.Rawat $request->register_id $request->time"
            ],
            'authoredOn'=>$request->time,
            'requester' => [
                'reference' => 'Practitioner/'.$request->practitioner_id,
                'display' => $request->practitioner_name
            ],
            'performer' => [
                [
                    'reference' => 'Organization/'.$satu_sehat_org_id,
                    'display' => 'Ruang Radiologi/Petugas Radiologi'
                ]
            ],
            'reasonCode' => [
                [
                    'text' => $request->clinical_diagnosis
                ]
            ],
        ];
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api-satusehat.kemkes.go.id/fhir-r4/v1/ServiceRequest/'.$request->id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS =>json_encode($requestBody),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$auth['data']->access_token,
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $data = json_decode($response);
        $serviceRequest = SatuSehatServiceRequestRadiologi::
        where('noorder','=',$request->noorder)->
        where('kd_jenis_prw','=',$request->kd_jenis_prw)->
        first();
        $serviceRequest?:$serviceRequest=new SatuSehatServiceRequestRadiologi();
        $serviceRequest->noorder = $request->noorder;
        $serviceRequest->kd_jenis_prw = $request->kd_jenis_prw;
        isset($data->id)?$serviceRequest->id_servicerequest = $data->kd_jenis_prw:false;
        $serviceRequest->save();
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $serviceRequest,
            'request' => $data,
            'token' => AuthHelper::genToken(),
        ]);
    }
}
