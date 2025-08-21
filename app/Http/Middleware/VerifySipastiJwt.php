<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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

        $baseUrl = rtrim(env('SIPASTI_BASE_URL'), '/');

        $resp = Http::withToken($token)->acceptJson()->get("$baseUrl/auth/profile");
        dd($resp->json());
        if ($resp->ok()) {
            return $resp->json('data') ?? $resp->json();
        }


        $profile = Cache::remember("sipasti:profile:{$decoded->sub}", now()->addMinutes(30), function () use ($baseUrl, $token) {
            $resp = Http::withToken($token)->acceptJson()->get("$baseUrl/auth/profile");
            if ($resp->ok()) {
                return $resp->json('data') ?? $resp->json();
            }

            abort(response()->json(['error' => 'Cannot fetch profile from SIPASTI'], \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED));
        });

        $roleMap  = Helper::getRoleMap();
        $guestId  = $roleMap['guest'] ?? null;
        $rawRole  = Str::lower((string)($profile['role'] ?? ''));
        $roleName = match (true) {
            Str::contains($rawRole, 'kepala balai') => 'PJ Balai',
            $rawRole === 'superadmn'                => 'superadmin',
            default                                 => 'guest',
        };
        $roleId = $roleMap[$roleName] ?? $guestId;

        $userIdSipasti = (string)($profile['id'] ?? $decoded->sub ?? '');
        $email         = isset($profile['email']) ? trim($profile['email']) : null;
        if ($userIdSipasti === '' && !$email) {
            return response()->json(['error' => 'Invalid profile payload'], 401);
        }

        $cacheKey = 'user_profile_model:' . ($userIdSipasti ?: 'email:' . $email);
        $model = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($userIdSipasti, $email, $profile, $roleId) {
            $balaiKerja = \App\Models\SatuanBalaiKerja::where('nama', 'like', "%{$profile['unit_kerja']}%")->first();

            $where = $userIdSipasti !== '' ? ['user_id_sipasti' => $userIdSipasti] : ['email' => $email];
            return \App\Models\Users::updateOrCreate(
                $where,
                [
                    'nama_lengkap'  => $profile['name'],
                    'email'         => $email,
                    'no_handphone'  => isset($profile['phone']) ? $profile['phone'] : null,
                    'nik'           => isset($profile['detail']) ? $profile['detail']['nik'] : $profile['nik'] ?? null,
                    'nrp'           => isset($profile['detail']) ? $profile['detail']['nrp'] : $profile['nrp'] ?? null,
                    'nip'           => isset($profile['detail']) ? $profile['detail']['nip'] : $profile['nip'] ?? null,
                    'id_roles'       => $roleId,
                    'status'        => 'active',
                    'balai_kerja_id' => $balaiKerja ? $balaiKerja->id : null,
                    'user_id_sipasti' => $userIdSipasti
                ]
            );
        });

        Auth::setUser($model);

        $request->attributes->add([
            'auth_user' => [
                'id' => $model->id,
                'nama_lengkap' => $model->nama_lengkap,
                'email' => $model->email,
                'no_handphone' => $model->no_handphone,
                'nik' => $model->nik,
                'nrp' => $model->nrp,
                'nip' => $model->nip,
                'id_roles' => $model->role_id,
                'status' => $model->status,
                'balai_kerja_id' => $model->balai_kerja_id,
                'satuan_kerja_id' => $model->satuan_kerja_id,
                'user_id_sipasti' => $model->user_id_sipasti
            ],
        ]);

        return $next($request);
    }
}
