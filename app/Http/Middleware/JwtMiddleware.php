<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Attempt to authenticate the user
            $user = JWTAuth::parseToken()->authenticate()->load(['studentInfo','tuition']);

            // Set the authenticated user on the request
            $request->attributes->set('user', $user);
        } catch (TokenExpiredException $e) {
            return response()->json(['status' => 'error', 'msg' => 'Session has expired!'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['status' => 'error', 'msg' => 'Invalid token provided!'], 401);
        } catch (JWTException $e) {
            return response()->json(['status' => 'error', 'msg' => 'Token not found!'], 401);
        }
        return $next($request);
    }
}
