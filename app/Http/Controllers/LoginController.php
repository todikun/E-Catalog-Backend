<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\UserService;
use App\Services\LoginService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{
    protected $userService;
    protected $loginService;

    public function __construct(
        UserService $userService,
        LoginService $loginService
    ) {
        $this->userService = $userService;
        $this->loginService = $loginService;
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validasi error',
                'error' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('username', 'password');
        try {

            if (!auth('api')->attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal login!',
                    'error' => 'Invalid credentials'
                ], 401);
            }

            // --- Refactored token generation ---
            $account = auth('api')->user();

            // Get the role from the service and add it as a custom claim
            $check = $this->loginService->getUserFromId($account['user_id']);
            $customClaims = ['role' => $check['role_name']];

            // Generate a short-lived access token (1 hours)
            JWTAuth::factory()->setTTL(60);
            $accessToken = JWTAuth::customClaims($customClaims)->fromUser($account);

            // Generate a long-lived refresh token (3 days)
            JWTAuth::factory()->setTTL(4320);
            $refreshToken = JWTAuth::customClaims($customClaims)->fromUser($account);

            return response()->json([
                'status' => 'success',
                'message' => 'berhasil login!',
                'token' => $accessToken,
                'refreshToken' => $refreshToken,
                'data' => $account
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal login!',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {

        try {
            $message = [];

            // Invalidate Access Token
            try {
                JWTAuth::setToken($request->token)->invalidate();
                $message[] = 'Access token dihapus';
            } catch (\Throwable $e) {
                $message[] = 'Access token tidak valid';
            }

            // Invalidate Refresh Token
            try {
                JWTAuth::setToken($request->refreshToken)->invalidate();
                $message[] = 'Refresh token dihapus';
            } catch (\Throwable $th) {
                $message[] = 'Refresh token tidak valid';
            }

            return response()->json([
                'status' => 'Success',
                'message' => $message
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal logout!',
                'error' => 'AccessToken dan RefreshToken tidak ada'
            ], 500);
        }
    }


    public function refresh(Request $request)
    {
        $refreshToken = $request->input('refreshToken');

        if (!$refreshToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Refresh token is required'
            ], 400);
        }

        try {
            // Use Tymon's refresh method
            $newToken = JWTAuth::setToken($refreshToken)->refresh();

            return response()->json([
                'status' => 'success',
                'message' => 'Access token refreshed successfully',
                'data' => [
                    'access_token' => $newToken,
                    'refresh_token' => $refreshToken // Keep same refresh token
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not refresh access token',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function checkRole()
    {
        try {
            $token = JWTAuth::parseToken();
            $payload = $token->getPayload();
            $role = $payload->get('role');
            if ($role) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Role retrieved successfully.',
                    'data' => $role,
                ]);
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Role retrieved successfully.',
                    'data' => [],
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is invalid or expired.',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
