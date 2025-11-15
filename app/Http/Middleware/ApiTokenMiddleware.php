<?php

namespace App\Http\Middleware;

use App\Models\IoUser;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $token = $request->bearerToken();

        // if (!$token) {
        //     return response()->json(['error' => 'Token missing'], 401);
        // }

        // // hash token dari header
        // $hashed = hash('sha256', $token);

        // $user = IoUser::where('api_token', $hashed)->first();

        // if (!$user) {
        //     return response()->json(['error' => 'Invalid token'], 401);
        // }

        // // cek expired
        // if (Carbon::now()->greaterThan($user->api_token_expires_at)) {
        //     return response()->json(['error' => 'Token expired'], 401);
        // }

        // // simpan user ke request
        // $request->merge(['auth_user' => $user]);

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
        }

        return $next($request);
    }
}
