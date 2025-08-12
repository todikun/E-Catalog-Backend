<?php

namespace App\Services;

use App\Models\Users;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function checkNik($nik)
    {
        return Users::where('nik', $nik)->exists();
    }

    public function checkUserIfExist($userId)
    {
        // return Users::where('id', $userId)->whereNull('email_verified_at')->first();
        return Users::join('accounts', 'users.id', '=', 'accounts.user_id')
            ->where('users.id', $userId)
            ->whereNull('users.email_verified_at')
            ->select('users.*')->first();
    }

    public function listUser()
    {
        $data = Users::select(
            'users.id AS user_id',
            'users.nama_lengkap',
            'users.no_handphone',
            'users.nrp AS nrp/nip',
            'satuan_kerja.nama AS satuan_kerja',
            'satuan_balai_kerja.nama AS balai_kerja',
            'users.email',
            'users.surat_penugasan_url AS sk_penugasan',
        )
            ->join('satuan_kerja', 'users.satuan_kerja_id', '=', 'satuan_kerja.id')
            ->join('satuan_balai_kerja', 'users.balai_kerja_id', '=', 'satuan_balai_kerja.id')
            ->where('users.status', 'verification')
            ->whereNotNull('users.email_verified_at')
            ->where('users.id_roles', '!=', 1)
            ->get();

        $data->transform(function ($item) {
            $item->sk_penugasan = Storage::url($item->sk_penugasan);
            return $item;
        });
        return $data;
    }
}
