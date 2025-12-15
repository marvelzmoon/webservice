<?php

namespace App\Http\Controllers;

use App\Models\IoUser;
use Carbon\Carbon;
// use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $username = $request->username;
        $password = $request->password;

        if (!$username) {
            return response()->json([
                'code' => 204,
                'message' => "Username harus diisi!"
            ]);
        }

        if (!$password) {
            return response()->json([
                'code' => 204,
                'message' => "Password harus diisi!"
            ]);
        }

        $user = IoUser::find($username);

        // if (!$user || !Hash::check($request->password, $user->password)) {
        if (!$user || $password !== $user->password) {
            return response()->json(['code' => 401, 'message' => 'Invalid credentials'], 401);
        }

        $expiredtoken = (int)config('confsistem.expired_time');

        $info = [
            'username' => $user->id,
            'expired' => Carbon::now()->addMinutes($expiredtoken)
        ];

        $token = Crypt::encrypt($info);

        return response()->json([
            'code' => 200,
            'message' => 'Berhasil mendapatkan token',
            'token' => $token,
        ]);
    }
    public function checkUsername(Request $request)
    {
        $username = $request->username;

        if (!$username) {
            return response()->json([
                'code' => 204,
                'message' => "Username harus diisi!"
            ]);
        }
        $user = IoUser::find($username);
        if (!$user) {
            return response()->json(['code' => 401, 'message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
        ]);
    }
    public function loginData()
    {
        $data = Crypt::decrypt(request()->header('Authorization'));

        $username = $data['username'];
        $expired = $data['expired'];

        if (!isset($username)) {
            return response()->json(['code' => 401, 'message' => 'Unauthorized'], 401);
        }

        if (!isset($expired)) {
            return response()->json(['code' => 401, 'message' => 'Unauthorized'], 401);
        }

        if (Carbon::now()->greaterThan($expired)) {
            return response()->json(['code' => 401, 'message' => 'Token expired'], 401);
        }
        $expiredtoken = (int)config('confsistem.expired_time');
        $info = [
            'username' => $username,
            'expired' => Carbon::now()->addMinutes($expiredtoken)
        ];
        $data = DB::table('io_user')->where('id', $username)->first([
            'id',
            'user_access',
            'group_access'
        ]);
        $data->expired = $expired;
        $token = Crypt::encrypt($info);

        return response()->json([
            'code' => 200,
            'message' => 'Ok',
            'data' => $data,
            'token' => $token,
        ]);
    }
    public function check(Request $request)
    {
        $data = Crypt::decrypt($request->header('Authorization'));

        $username = $data['username'];
        $expired = $data['expired'];

        if (!isset($username)) {
            return response()->json(['code' => 401, 'message' => 'Unauthorized'], 401);
        }

        if (!isset($expired)) {
            return response()->json(['code' => 401, 'message' => 'Unauthorized'], 401);
        }

        if (Carbon::now()->greaterThan($expired)) {
            return response()->json(['code' => 401, 'message' => 'Token expired'], 401);
        } else {
            return response()->json(['code' => 200, 'message' => 'Ok check', 'expired' => $expired->toDateTimeString()], 200);
        }
    }
    public function test()
    {
        return response()->json([
            'code' => 200,
            'message' => 'Ok',
        ]);
    }
}
