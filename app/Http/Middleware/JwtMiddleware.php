<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    public function __construct(
        private JwtService $jwtService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {

        $token = $request->cookie('access_token');

        if (!$token) {
            return $this->newAccessToken($request, $next);
        }

        try {
            $user = JWTAuth::setToken($token)->authenticate();
            if (!$user) {
                return $this->newAccessToken($request, $next);
            }

            Auth::setUser($user);
            return $next($request);
        } catch (TokenInvalidException $e) {
            return redirect()->route('login');
        } catch (JWTException $e) {

            return $this->newAccessToken($request, $next);
        }
    }

    protected function newAccessToken(Request $request, Closure $next)
    {
        $refreshToken = $request->cookie('refresh_token');
        if (!$refreshToken) {
            return redirect()->route('login');
        }

        try {
            $newRefreshToken = JWTAuth::setToken($refreshToken)->refresh();
            $newAccessToken = JWTAuth::customClaims(['type' => 'access'])->fromUser(
                JWTAuth::setToken($newRefreshToken)->authenticate()
            );

            $user = JWTAuth::setToken($newRefreshToken)->authenticate();
            Auth::setUser($user);

            $response = $next($request);

            $accessCoockie = cookie('access_token', $newAccessToken, 15, '/', null, false, true, false, 'Lax');
            $refreshCoockie = cookie('refresh_token', $newRefreshToken, 60 * 24 * 7, '/', null, false, true, false, 'Lax');

            return $response->withCookie($accessCoockie)->withCookie($refreshCoockie);
        } catch (JWTException $e) {
            return redirect()->route('login');
        }
    }
}
