<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\Users;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginService
{
    public function checkUsernameAndPassword($username, $password)
    {
        return Accounts::where('username', $username)
            ->where('password', $password)
            ->first();
    }

    public function saveToken($idAccounts, $token)
    {
        $account = Accounts::findOrFail($idAccounts);

        if ($token && $account) {
            $update = $account->update([
                'remember_token' => $token
            ]);

            return $update;
        }

        return $token;
    }

    public function getUserFromId($id)
    {
        return Users::select(
            'users.nama_lengkap',
            'roles.nama As role_name',
        )->join('roles', 'users.id_roles', '=', 'roles.id')
            ->where('users.id', $id)->first();
    }
}
