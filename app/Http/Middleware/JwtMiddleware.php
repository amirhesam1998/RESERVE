<?php

namespace App\Http\Middleware;

use App\Models\RefreshToken;
use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    public function __construct(
        private JwtService $jwtService
    ) {}
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('access_token');

        if (!$token) {
            return $this->newAccessToken($request, $next);
        }

        try {
            $user = JWTAuth::setToken($token)->authenticate();
            if (! $user) {
                return redirect()->route('login');
            }

            Auth::setUser($user);
            return $next($request);
        } catch (TokenInvalidException $e) {
            return redirect()->route('login');
        }
    }

    protected function newAccessToken(Request $request, Closure $next)
    {
        $refreshToken = $request->cookie('refresh_token');
        if (! $refreshToken) {
            return redirect()->route('login');
        }

        try {
            $newAccessToken = JWTAuth::setToken($refreshToken)->refresh();

            $user = JWTAuth::setToken($newAccessToken)->authenticate();
            Auth::setUser($user);

            $response = $next($request);

            $coockie = Cookie(
                'access_token',
                $newAccessToken,
                15,
                '/',
                null,
                false,
                true,
                false,
                'lax'
            );

            return $response->withCookie($coockie);
        } catch (JWTException $e) {
            return redirect()->route('login');
        }
    }
}
