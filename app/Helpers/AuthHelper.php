<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class AuthHelper {
    public static function genToken() {
        if(request()->header('Authorization') == null){
            return null;
        }
        $data = Crypt::decrypt(request()->header('Authorization'));

        $username = $data['username'];
        $expiredtoken = (int)config('confsistem.expired_time');
        $info = [
            'username' => $username,
            'expired' => Carbon::now()->addMinutes($expiredtoken)
        ];
        $token = Crypt::encrypt($info);
        return $token;
    }
}