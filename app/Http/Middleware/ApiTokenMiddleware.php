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
        if ($request->header('Authorization') == null) {
            return response()->json(['code' => 401, 'message' => 'Unauthorized'], 401);
        }
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
