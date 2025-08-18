<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class RoleStatusExpiryMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        $expirationDate = Carbon::parse($user->tuition->expiry_datetime);
        if ($expirationDate->isPast()) {
            return response()->json(['status' => 'error', 'msg' => 'Your plan has been expired!'], 401);
        }

        if ($user->status !== 'active') {
            try {
                JWTAuth::invalidate(JWTAuth::getToken());
                return response()->json(['status' => 'error', 'msg' => 'Account Inactive!'], 401);
            } catch (JWTException $e) {
                return response()->json(['error' => 'Something wrong, please try again'], 401);
            }
            return response()->json(['status' => 'error', 'msg' => 'Account Inactive!'], 401);
        }
        
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Forbidden: You do not have access'
            ], 403);
        }
        return $next($request);
    }
}
