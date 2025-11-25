<?php

namespace App\Http\Controllers\Jkn;

use App\Http\Controllers\Controller;
use App\Models\IoSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use LZCompressor\LZString;

class JknApiAntrolController extends Controller
{
    public function refPoli()
    {
        $sId = IoSetting::where('group', 'bpjs_kesehatan')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }

        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $bpjs_cons_id . "&" . $tStamp, $bpjs_secret_key, true);
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.url_antrol') . '/ref/poli',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
                'X-cons-id: ' . $bpjs_cons_id . '',
                'X-timestamp: ' . $tStamp . '',
                'X-signature: ' . $encodedSignature . '',
                // 'user_key: ' . $antrol_userkey . '',
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $decode = json_decode($response, true);

        if ($decode['metadata']['code'] != 1) {
            $code = $decode['metadata']['code'];
            $message = $decode['metadata']['message'];
            $data = null;
        } else {
            $key = $bpjs_cons_id . $bpjs_secret_key . $tStamp;
            $string = $decode['response'];

            $encrypt_method = 'AES-256-CBC';
            $key_hash = hex2bin(hash('sha256', $key));
            $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

            $decompress = LZString::decompressFromEncodedURIComponent($output);

            $code = 200;
            $message = 'Ok';
            $data = json_decode($decompress);
        }

        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function refDokter()
    {
        $sId = IoSetting::where('group', 'bpjs_kesehatan')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }

        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $bpjs_cons_id . "&" . $tStamp, $bpjs_secret_key, true);
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.url_antrol') . '/ref/dokter',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
                'X-cons-id: ' . $bpjs_cons_id . '',
                'X-timestamp: ' . $tStamp . '',
                'X-signature: ' . $encodedSignature . '',
                // 'user_key: ' . $antrol_userkey . '',
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $decode = json_decode($response, true);

        if ($decode['metadata']['code'] != 1) {
            $code = $decode['metadata']['code'];
            $message = $decode['metadata']['message'];
            $data = null;
        } else {
            $key = $bpjs_cons_id . $bpjs_secret_key . $tStamp;
            $string = $decode['response'];

            $encrypt_method = 'AES-256-CBC';
            $key_hash = hex2bin(hash('sha256', $key));
            $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

            $decompress = LZString::decompressFromEncodedURIComponent($output);

            $code = 200;
            $message = 'Ok';
            $data = json_decode($decompress);
        }

        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function refJadwalDokter(Request $request)
    {
        $rules = [
            'poli' => 'required',
            'tanggal' => 'required|date_format:Y-m-d',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'date_format' => ':attribute Format tanggal harus Y-m-d',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $sId = IoSetting::where('group', 'bpjs_kesehatan')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }

        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $bpjs_cons_id . "&" . $tStamp, $bpjs_secret_key, true);
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.url_antrol') . '/jadwaldokter/kodepoli/' . $request->poli . '/tanggal/' . $request->tanggal,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
                'X-cons-id: ' . $bpjs_cons_id . '',
                'X-timestamp: ' . $tStamp . '',
                'X-signature: ' . $encodedSignature . '',
                // 'user_key: ' . $antrol_userkey . '',
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $decode = json_decode($response, true);

        if ($decode['metadata']['code'] != 200) {
            $code = $decode['metadata']['code'];
            $message = $decode['metadata']['message'];
            $data = null;
        } else {
            $key = $bpjs_cons_id . $bpjs_secret_key . $tStamp;
            $string = $decode['response'];

            $encrypt_method = 'AES-256-CBC';
            $key_hash = hex2bin(hash('sha256', $key));
            $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

            $decompress = LZString::decompressFromEncodedURIComponent($output);

            $code = 200;
            $message = 'Ok';
            $data = json_decode($decompress);
        }

        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function refPoliFP()
    {
        $sId = IoSetting::where('group', 'bpjs_kesehatan')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }

        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $bpjs_cons_id . "&" . $tStamp, $bpjs_secret_key, true);
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.url_antrol') . '/ref/poli/fp',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
                'X-cons-id: ' . $bpjs_cons_id . '',
                'X-timestamp: ' . $tStamp . '',
                'X-signature: ' . $encodedSignature . '',
                // 'user_key: ' . $antrol_userkey . '',
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $decode = json_decode($response, true);

        if ($decode['metadata']['code'] != 1) {
            $code = $decode['metadata']['code'];
            $message = $decode['metadata']['message'];
            $data = null;
        } else {
            $key = $bpjs_cons_id . $bpjs_secret_key . $tStamp;
            $string = $decode['response'];

            $encrypt_method = 'AES-256-CBC';
            $key_hash = hex2bin(hash('sha256', $key));
            $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

            $decompress = LZString::decompressFromEncodedURIComponent($output);

            $code = 200;
            $message = 'Ok';
            $data = json_decode($decompress);
        }

        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function refPasienFP(Request $request)
    {
        $rules = [
            'noiden' => 'required',
            'jenis' => 'required',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $sId = IoSetting::where('group', 'bpjs_kesehatan')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }

        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $bpjs_cons_id . "&" . $tStamp, $bpjs_secret_key, true);
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.url_antrol') . '/ref/pasien/fp/identitas/' . $request->jenis . '/noidentitas/' . $request->noiden,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
                'X-cons-id: ' . $bpjs_cons_id . '',
                'X-timestamp: ' . $tStamp . '',
                'X-signature: ' . $encodedSignature . '',
                // 'user_key: ' . $antrol_userkey . '',
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $decode = json_decode($response, true);

        if ($decode['metadata']['code'] != 1) {
            $code = $decode['metadata']['code'];
            $message = $decode['metadata']['message'];
            $data = null;
        } else {
            $key = $bpjs_cons_id . $bpjs_secret_key . $tStamp;
            $string = $decode['response'];

            $encrypt_method = 'AES-256-CBC';
            $key_hash = hex2bin(hash('sha256', $key));
            $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

            $decompress = LZString::decompressFromEncodedURIComponent($output);

            $code = 200;
            $message = 'Ok';
            $data = json_decode($decompress);
        }

        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function antrianPerTgl(Request $request)
    {
        $rules = [
            'tanggal' => 'required|date_format:Y-m-d',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'date_format' => ':attribute Format tanggal harus Y-m-d',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $sId = IoSetting::where('group', 'bpjs_kesehatan')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }

        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $bpjs_cons_id . "&" . $tStamp, $bpjs_secret_key, true);
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.url_antrol') . '/antrean/pendaftaran/tanggal/' . $request->tanggal,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
                'X-cons-id: ' . $bpjs_cons_id . '',
                'X-timestamp: ' . $tStamp . '',
                'X-signature: ' . $encodedSignature . '',
                // 'user_key: ' . $antrol_userkey . '',
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $decode = json_decode($response, true);

        if ($decode['metadata']['code'] != 200) {
            $code = $decode['metadata']['code'];
            $message = $decode['metadata']['message'];
            $data = null;
        } else {
            $key = $bpjs_cons_id . $bpjs_secret_key . $tStamp;
            $string = $decode['response'];

            $encrypt_method = 'AES-256-CBC';
            $key_hash = hex2bin(hash('sha256', $key));
            $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

            $decompress = LZString::decompressFromEncodedURIComponent($output);

            $code = 200;
            $message = 'Ok';
            $data = json_decode($decompress);
        }

        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function antrianPerKbo(Request $request)
    {
        $rules = [
            'kodebooking' => 'required',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $sId = IoSetting::where('group', 'bpjs_kesehatan')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }

        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $bpjs_cons_id . "&" . $tStamp, $bpjs_secret_key, true);
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.url_antrol') . '/antrean/pendaftaran/kodebooking/' . $request->kodebooking,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
                'X-cons-id: ' . $bpjs_cons_id . '',
                'X-timestamp: ' . $tStamp . '',
                'X-signature: ' . $encodedSignature . '',
                // 'user_key: ' . $antrol_userkey . '',
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $decode = json_decode($response, true);

        if ($decode['metadata']['code'] != 200) {
            $code = $decode['metadata']['code'];
            $message = $decode['metadata']['message'];
            $data = null;
        } else {
            $key = $bpjs_cons_id . $bpjs_secret_key . $tStamp;
            $string = $decode['response'];

            $encrypt_method = 'AES-256-CBC';
            $key_hash = hex2bin(hash('sha256', $key));
            $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

            $decompress = LZString::decompressFromEncodedURIComponent($output);

            $code = 200;
            $message = 'Ok';
            $data = json_decode($decompress);
        }

        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function antrianAktif()
    {
        $sId = IoSetting::where('group', 'bpjs_kesehatan')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }

        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $bpjs_cons_id . "&" . $tStamp, $bpjs_secret_key, true);
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.url_antrol') . '/antrean/pendaftaran/aktif',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
                'X-cons-id: ' . $bpjs_cons_id . '',
                'X-timestamp: ' . $tStamp . '',
                'X-signature: ' . $encodedSignature . '',
                // 'user_key: ' . $antrol_userkey . '',
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $decode = json_decode($response, true);

        if ($decode['metadata']['code'] != 200) {
            $code = $decode['metadata']['code'];
            $message = $decode['metadata']['message'];
            $data = null;
        } else {
            $key = $bpjs_cons_id . $bpjs_secret_key . $tStamp;
            $string = $decode['response'];

            $encrypt_method = 'AES-256-CBC';
            $key_hash = hex2bin(hash('sha256', $key));
            $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

            $decompress = LZString::decompressFromEncodedURIComponent($output);

            $code = 200;
            $message = 'Ok';
            $data = json_decode($decompress);
        }

        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function antrianAktifDetail(Request $request)
    {
        $rules = [
            'kodepoli'          => 'required',
            'kodedokter'        => 'required',
            'hari'              => 'required',
            'jampraktek'        => 'required',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $sId = IoSetting::where('group', 'bpjs_kesehatan')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }

        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $bpjs_cons_id . "&" . $tStamp, $bpjs_secret_key, true);
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.url_antrol') . '/antrean/pendaftaran/kodepoli/' . $request->kodepoli . '/kodedokter/' . $request->kodedokter . '/hari/' . $request->hari . '/jampraktek/' . $request->jampraktek,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
                'X-cons-id: ' . $bpjs_cons_id . '',
                'X-timestamp: ' . $tStamp . '',
                'X-signature: ' . $encodedSignature . '',
                // 'user_key: ' . $antrol_userkey . '',
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $decode = json_decode($response, true);

        if ($decode['metadata']['code'] != 200) {
            $code = $decode['metadata']['code'];
            $message = $decode['metadata']['message'];
            $data = null;
        } else {
            $key = $bpjs_cons_id . $bpjs_secret_key . $tStamp;
            $string = $decode['response'];

            $encrypt_method = 'AES-256-CBC';
            $key_hash = hex2bin(hash('sha256', $key));
            $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

            $decompress = LZString::decompressFromEncodedURIComponent($output);

            $code = 200;
            $message = 'Ok';
            $data = json_decode($decompress);
        }

        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function listTaskid(Request $request)
    {
        $rules = [
            'kodebooking' => 'required',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $sId = IoSetting::where('group', 'bpjs_kesehatan')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }

        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $bpjs_cons_id . "&" . $tStamp, $bpjs_secret_key, true);
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.url_antrol') . '/antrean/getlisttask',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'kodebooking' => $request->kodebooking
            ]),
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
                'X-cons-id: ' . $bpjs_cons_id . '',
                'X-timestamp: ' . $tStamp . '',
                'X-signature: ' . $encodedSignature . '',
                // 'user_key: ' . $antrol_userkey . '',
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $decode = json_decode($response, true);

        if ($decode['metadata']['code'] != 200) {
            $code = $decode['metadata']['code'];
            $message = $decode['metadata']['message'];
            $data = null;
        } else {
            $key = $bpjs_cons_id . $bpjs_secret_key . $tStamp;
            $string = $decode['response'];

            $encrypt_method = 'AES-256-CBC';
            $key_hash = hex2bin(hash('sha256', $key));
            $iv = substr(hex2bin(hash('sha256', $key)), 0, 16);
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key_hash, OPENSSL_RAW_DATA, $iv);

            $decompress = LZString::decompressFromEncodedURIComponent($output);

            $code = 200;
            $message = 'Ok';
            $data = json_decode($decompress);
        }

        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function updateWaktuAntrian(Request $request)
    {
        $rules = [
            'kodebooking' => 'required',
            'taskid'      => 'required',
            'waktu'       => 'required',
        ];

        if (config('confsistem.add_farmasi') === 'YA') {
            $rules['jenisresep'] = 'required';
        }

        $messages = [
            'required' => ':attribute tidak boleh kosong',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $sId = IoSetting::where('group', 'bpjs_kesehatan')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }

        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $bpjs_cons_id . "&" . $tStamp, $bpjs_secret_key, true);
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.url_antrol') . '/antrean/updatewaktu',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'kodebooking' => $request->kodebooking,
                'taskid' => (int)$request->taskid,
                'waktu' => (int)$request->waktu
            ]),
            CURLOPT_HTTPHEADER => array(
                // Set here requred headers
                "accept: */*",
                "accept-language: en-US,en;q=0.8",
                "content-type: application/json",
                'X-cons-id: ' . $bpjs_cons_id . '',
                'X-timestamp: ' . $tStamp . '',
                'X-signature: ' . $encodedSignature . '',
                // 'user_key: ' . $antrol_userkey . '',
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $decode = json_decode($response, true);

        if ($decode['metadata']['code'] == 200) {
            return response()->json([
                'code' => 200,
                'message' => 'Taskid ' . $request->taskid . ' berhasil update waktu!',
            ]);
        }

        return response()->json($decode);
    }

    public function daftarAntrian(Request $request)
    {
        $rules = [
            'kodebooking'       => 'required',
            'jenispasien'       => 'required',
            'nomorkartu'        => 'required',
            'nik'               => 'required',
            'nohp'              => 'required',
            'kodepoli'          => 'required',
            'namapoli'          => 'required',
            'pasienbaru'        => 'required',
            'norm'              => 'required',
            'tanggalperiksa'    => 'required',
            'kodedokter'        => 'required',
            'namadokter'        => 'required',
            'jampraktek'        => 'required',
            'jeniskunjungan'    => 'required',
            'nomorreferensi'    => 'required',
            'nomorantrean'      => 'required',
            'angkaantrean'      => 'required',
            'estimasidilayani'  => 'required',
            'sisakuotajkn'      => 'required',
            'kuotajkn'          => 'required',
            'sisakuotanonjkn'   => 'required',
            'kuotanonjkn'       => 'required',
            'keterangan'        => 'required',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $data = [
            'kodebooking'       => $request->kodebooking,
            // 'jenispasien'       => $request->jenispasien,
            // 'nomorkartu'        => $request->nomorkartu,
            // 'nik'               => $request->nik,
            // 'nohp'              => $request->nohp,
            // 'kodepoli'          => $request->kodepoli,
            // 'namapoli'          => $request->namapoli,
            // 'pasienbaru'        => $request->pasienbaru,
            // 'norm'              => $request->norm,
            // 'tanggalperiksa'    => $request->tanggalperiksa,
            // 'kodedokter'        => (int)$request->kodedokter,
            // 'namadokter'        => $request->namadokter,
            // 'jampraktek'        => $request->jampraktek,
            // 'jeniskunjungan'    => (int)$request->jeniskunjungan,
            // 'nomorreferensi'    => $request->nomorreferensi,
            // 'nomorantrean'      => $request->nomorantrean,
            // 'angkaantrean'      => (int)$request->angkaantrean,
            // 'estimasidilayani'  => (int)$request->estimasidilayani,
            // 'sisakuotajkn'      => (int)$request->sisakuotajkn,
            // 'kuotajkn'          => (int)$request->kuotajkn,
            // 'sisakuotanonjkn'   => (int)$request->sisakuotanonjkn,
            // 'kuotanonjkn'       => (int)$request->kuotanonjkn,
            // 'keterangan'        => $request->keterangan,
        ];

        $sId = IoSetting::where('group', 'bpjs_kesehatan')->get();

        foreach ($sId as $row) {
            ${$row['setting_option']} = $row['value'];
        }

        date_default_timezone_set('UTC');
        $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        $signature = hash_hmac('sha256', $bpjs_cons_id . "&" . $tStamp, $bpjs_secret_key, true);
        $encodedSignature = base64_encode($signature);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('confsistem.url_antrol') . '/antrean/add',
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
                'X-cons-id: ' . $bpjs_cons_id . '',
                'X-timestamp: ' . $tStamp . '',
                'X-signature: ' . $encodedSignature . '',
                // 'user_key: ' . $antrol_userkey . '',
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $decode = json_decode($response, true);

        if ($decode['metadata']['code'] != 200) {
            return response()->json([
                'code' => 201,
                'message' => 'Proses gagal, Parameter tidak sesuai'
            ]);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Daftar Antrol berhasil, kodebooking ' . $request->kodebooking
        ]);
    }

    public function daftarAntrianFarmasi(Request $request)
    {
        $rules = [
            'kodebooking'   => 'required',
            'jenisresep'    => 'required',
            'nomorantrean'  => 'required',
            'keterangan'    => 'required',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $data = [
            'kodebooking'   => $request->kodebooking,
            'jenisresep'    => $request->jenisresep,
            'nomorantrean'  => (int)$request->nomorantrean,
            'keterangan'    => $request->keterangan,
        ];

        return $data;

        // $sId = IoSetting::where('group', 'bpjs_kesehatan')->get();

        // foreach ($sId as $row) {
        //     ${$row['setting_option']} = $row['value'];
        // }

        // date_default_timezone_set('UTC');
        // $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        // $signature = hash_hmac('sha256', $bpjs_cons_id . "&" . $tStamp, $bpjs_secret_key, true);
        // $encodedSignature = base64_encode($signature);

        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => config('confsistem.url_antrol') . '/antrean/farmasi/add',
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => "",
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 30000,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => "POST",
        //     CURLOPT_POSTFIELDS => json_encode($data),
        //     CURLOPT_HTTPHEADER => array(
        //         // Set here requred headers
        //         "accept: */*",
        //         "accept-language: en-US,en;q=0.8",
        //         "content-type: application/json",
        //         'X-cons-id: ' . $bpjs_cons_id . '',
        //         'X-timestamp: ' . $tStamp . '',
        //         'X-signature: ' . $encodedSignature . '',
        //         // 'user_key: ' . $antrol_userkey . '',
        //     ),
        // ));

        // $response = curl_exec($curl);
        // $err = curl_error($curl);

        // $decode = json_decode($response, true);

        // if ($decode['metadata']['code'] == 200) {
        //     return response()->json([
        //         'code' => 201,
        //         'message' => 'Proses gagal, Parameter tidak sesuai'
        //     ]);
        // }

        // return response()->json($decode);
    }

    public function batalAntrean(Request $request)
    {
        $rules = [
            'kodebooking'   => 'required',
            'keterangan'    => 'required',
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'code'    => 201,
                'message' => $validator->errors()->first()
            ]);
        }

        $data = [
            'kodebooking'   => $request->kodebooking,
            'keterangan'    => $request->keterangan,
        ];

        return $data;

        // $sId = IoSetting::where('group', 'bpjs_kesehatan')->get();

        // foreach ($sId as $row) {
        //     ${$row['setting_option']} = $row['value'];
        // }

        // date_default_timezone_set('UTC');
        // $tStamp = strval(time() - strtotime('1970-01-01 00:00:00'));
        // $signature = hash_hmac('sha256', $bpjs_cons_id . "&" . $tStamp, $bpjs_secret_key, true);
        // $encodedSignature = base64_encode($signature);

        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => config('confsistem.url_antrol') . '/antrean/batal',
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => "",
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 30000,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => "POST",
        //     CURLOPT_POSTFIELDS => json_encode($data),
        //     CURLOPT_HTTPHEADER => array(
        //         // Set here requred headers
        //         "accept: */*",
        //         "accept-language: en-US,en;q=0.8",
        //         "content-type: application/json",
        //         'X-cons-id: ' . $bpjs_cons_id . '',
        //         'X-timestamp: ' . $tStamp . '',
        //         'X-signature: ' . $encodedSignature . '',
        //         // 'user_key: ' . $antrol_userkey . '',
        //     ),
        // ));

        // $response = curl_exec($curl);
        // $err = curl_error($curl);

        // $decode = json_decode($response, true);

        // if ($decode['metadata']['code'] == 200) {
        // return response()->json([
        //     'code' => 200,
        //     'message' => 'Daftar Antrol Farmasi berhasil'
        // ]);
        // }

        //     return response()->json($decode);
    }
}
