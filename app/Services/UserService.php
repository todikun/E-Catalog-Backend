<?php

namespace App\Services;

use App\Models\Users;
use App\Models\PerencanaanData;
use App\Models\Roles;
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

    public function listUserByRoleAndBalai($data)
    {
        $roleName = $data['role'];
        $balaiKey = $data['balai_key'];

        // Step 1: Get all eligible users (active, verified, correct role & balai)
        $users = Users::select([
            'users.id AS user_id',
            'users.nama_lengkap',
            'users.nrp',
            'satuan_kerja.nama AS satuan_kerja_name',
            'users.surat_penugasan_url as surat_penugasan'
        ])
            ->join('roles', 'users.id_roles', '=', 'roles.id')
            ->join('satuan_balai_kerja', 'users.balai_kerja_id', '=', 'satuan_balai_kerja.id')
            ->join('satuan_kerja', 'users.satuan_kerja_id', '=', 'satuan_kerja.id')
            ->where('users.status', 'active')
            ->whereNotNull('users.email_verified_at')
            ->where('users.id_roles', '!=', 1) // exclude superadmin
            ->where('roles.nama', $roleName) // filter by role name
            ->where('satuan_balai_kerja.id', $balaiKey) // filter by balai
            ->get();

        // Step 2: Map each user and add status_penugasan
        $result = $users->map(function ($user) use ($roleName) {
            $exists = false;

            if ($roleName === 'pengawas') {
                $exists = PerencanaanData::whereJsonContains('pengawas_id', (string)$user->user_id)->exists();
            } elseif ($roleName === 'pengolah data') {
                $exists = PerencanaanData::whereJsonContains('pengolah_data_id', (string)$user->user_id)->exists();
            } elseif ($roleName === 'petugas lapangan') {
                $exists = PerencanaanData::whereJsonContains('petugas_lapangan_id', (string)$user->user_id)->exists();
            }

            return [
                'user_id' => $user->user_id,
                'nama_lengkap' => $user->nama_lengkap,
                'nrp' => $user->nrp,
                'satuan_kerja_name' => $user->satuan_kerja_name,
                'status_penugasan' => $exists ? 'ditugaskan' : 'tidak ditugaskan',
                'surat_penugasan' => $user->surat_penugasan
            ];
        });

        return $result;
    }
}
