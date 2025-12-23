<?php

namespace App\Http\Controllers\SatuSehat;

use App\Http\Controllers\Controller;
use App\Models\IoSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use LZCompressor\LZString;

class AuthController extends Controller
{
    public function auth($json=true)
    {
        $sId = IoSetting::where('group', 'satu_Sehat')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $satu_sehat_base_url.'oauth2/v1/accesstoken?grant_type=client_credentials',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => 'client_id='.$satu_sehat_client.'&client_secret='.$satu_sehat_secret.'',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // echo $response;

        $code = 200;
        $message = 'Ok';
        if($json){
            return response()->json([
                'code' => $code,
                'message' => $message,
                'data' => json_decode($response)
            ]);
        }
        else{
            return [
                'code' => $code,
                'message' => $message,
                'data' => json_decode($response)
            ];
        }
    }
}
