<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // retoken
        $data = Crypt::decrypt($request->header('Authorization'));

        $expiredtoken = (int)config('confsistem.expired_time');
        $token = Crypt::encrypt([
            'username' => $data['username'],
            'expired' => Carbon::now()->addMinutes($expiredtoken)
        ]);

        return response()->json([
            'response' => [
                'code' => 200,
                'message' => 'Ok',
            ],
            'token' => $token,
        ]);
    }
}
