<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\users\LoginRequest;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        try {
            $token = JWTAuth::attempt($credentials);
            if ($token === false) {
                return response()->json([
                    'message' => 'Invalid credentials',
                ], 401);
            }
            $user = JWTAuth::user();

            $accessToken = JWTAuth::customClaims(['type' => 'access'])->fromUser($user);

            $refreshTTl = 60 * 24 * 7;
            $refreshToken = auth('api')->setTTL($refreshTTl)->claims(['type' => 'refresh'], ['permissions' => []])->fromUser($user);

            return response()->json([
                'accesstoken' => $accessToken,
                'refreshtoken' => $refreshToken,
                'user' => $user
            ])->cookie(
                'access_token',
                $accessToken,
                15,
                '/',
                null,
                false,
                true,
                false,
                'Lax'
            )->cookie(
                'refresh_token',
                $refreshToken,
                60 * 24 * 7,
                '/',
                null,
                false,
                true,
                false,
                'Lax'
            );
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'somthing went wrong, try again later'
            ], 500);
        }
    }
}
