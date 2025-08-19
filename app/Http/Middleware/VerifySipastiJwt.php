<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Firebase\JWT\BeforeValidException;
use Illuminate\Support\Facades\Config;
use Firebase\JWT\SignatureInvalidException;
use Symfony\Component\HttpFoundation\Response;

class VerifySipastiJwt
{
    public function handle(Request $request, Closure $next)
    {
        $auth  = $request->header('Authorization', '');
        $token = str_starts_with($auth, 'Bearer ') ? substr($auth, 7) : $request->cookie('sipasti_token');
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        JWT::$leeway = (int) Config::get('services.sipasti.jwt_leeway', env('SIPASTI_JWT_LEEWAY', 5));

        try {
            $algo   = Config::get('services.sipasti.jwt_algo', env('SIPASTI_JWT_ALGO', 'HS256'));
            if ($algo !== 'HS256') {
                return response()->json(['error' => 'Unsupported JWT algo'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $secret = Config::get('services.sipasti.jwt_secret', env('SIPASTI_JWT_SECRET'));
            if (!$secret) {
                return response()->json(['error' => 'JWT secret not configured'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

        } catch (ExpiredException) {
            return response()->json(['error' => 'Token expired'], Response::HTTP_UNAUTHORIZED);
        } catch (BeforeValidException) {
            return response()->json(['error' => 'Token not yet valid'], Response::HTTP_UNAUTHORIZED);
        } catch (SignatureInvalidException) {
            return response()->json(['error' => 'Invalid signature'], Response::HTTP_UNAUTHORIZED);
        } catch (\Throwable) {
            return response()->json(['error' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        $claims = [
            'user_id_sipasti' => $decoded->sub ?? null,
            'name'            => $decoded->name ?? 'Unknown',
            'email'           => $decoded->email ?? null,
            'role'            => $decoded->role ?? null,
            'nik'             => $decoded->nik  ?? null,
            'nrp'             => $decoded->nrp  ?? null,
            'no_hp'           => $decoded->no_hp ?? null,
        ];

        if (!$claims['user_id_sipasti'] && !$claims['email']) {
            return response()->json(['error' => 'Invalid claims'], Response::HTTP_UNAUTHORIZED);
        }

        $cacheKey = 'user_profile_model:' . ($claims['user_id_sipasti'] ?: 'email:' . $claims['email']);

        $model = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($claims) {
            return User::updateOrCreate(
                ['user_id_sipasti' => $claims['user_id_sipasti']],
                [
                    'name' => $claims['name'],
                    'email'=> $claims['email'],
                    'role' => $claims['role'],
                    'nik'  => $claims['nik'],
                    'nrp'  => $claims['nrp'],
                    'no_hp'=> $claims['no_hp'],
                ]
            );
        });

        Auth::setUser($model);

        $request->attributes->add([
            'auth_user' => [
                'id'               => $model->id,
                'user_id_sipasti'  => $model->user_id_sipasti,
                'name'             => $model->name,
                'email'            => $model->email,
                'role'             => $model->role,
                'nik'              => $model->nik,
                'nrp'              => $model->nrp,
                'no_hp'            => $model->no_hp,
            ],
        ]);

        return $next($request);
    }
}
