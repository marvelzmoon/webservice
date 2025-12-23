<?php

namespace App\Http\Controllers\SatuSehat;

use App\Http\Controllers\Controller;
use App\Models\IoSetting;
use App\Models\SatuSehatPatient;

class LokasiRalanController extends Controller
{
    public function getOne($nik='')
    {
        if(!$nik){
            $code = 401;
            $message = 'parameter NIK tidak ada!';
            return response()->json([
                'code' => $code,
                'message' => $message,
                'data' => null
            ]);
        }
        $sId = IoSetting::where('group', 'satu_Sehat')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }
        $auth = new AuthController();
        $auth = $auth->auth(false);
        $code = 200;
        $message = 'Ok';

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $satu_sehat_base_url.'fhir-r4/v1/Patient?identifier=https%3A%2F%2Ffhir.kemkes.go.id%2Fid%2Fnik%7C'.$nik,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$auth['data']->access_token,
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // echo $response;
        $data = json_decode($response);
        if(!isset($data->total)){
            $code = 402;
            $message = 'Data request gagal';
            return response()->json([
                'code' => $code,
                'message' => $message,
                'data' => $resonse
            ]);
        }
        if($data->total == 0){
            $code = 204;
            $message = 'Data tidak ditemukan!';
            return response()->json([
                'code' => $code,
                'message' => $message,
                'data' => null
            ]);
        }
        $patient = SatuSehatPatient::find($nik);
        $patient?:$patient=new SatuSehatPatient();
        $patient->no_ktp = $nik;
        $patient->patient_id = $data->entry[0]->resource->id;
        $patient->data = json_encode($data->entry[0]);
        $patient->save();
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $patient
        ]);
    }
}
