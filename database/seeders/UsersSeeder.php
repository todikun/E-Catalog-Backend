<?php

namespace Database\Seeders;

use App\Models\Users;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Users::upsert(
        [
            [
                'nama_lengkap'        => "KEPALA BALAI BESAR WILAYAH SUNGAI CILIWUNG-CISADANE",
                'email'               => "kepala-ciliwung-cisadane@gmail.com",
                'id_roles'            => 2,
                'satuan_kerja_id'     => null,
                'balai_kerja_id'      => null,
                'status'              => "active",
                'no_handphone'        => "123456789",
                'nik'                 => "123456789",
                'nrp'                 => "123456789",
                'nip'                 => "123456789",
                'surat_penugasan_url' => null,
                'user_id_sipasti'     => "98e96006-ccb5-4f25-a0f2-cd7033fbb114",
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_lengkap'        => "SUPERADMIN",
                'email'               => "superadmin@gmail.com",
                'id_roles'            => 1,
                'satuan_kerja_id'     => null,
                'balai_kerja_id'      => null,
                'status'              => "active",
                'no_handphone'        => "000000000",
                'nik'                 => "000000000",
                'nrp'                 => "000000000",
                'nip'                 => "000000000",
                'surat_penugasan_url' => null,
                'user_id_sipasti'     => null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
        ],
        ['email'],
        [
            'nama_lengkap',
            'id_roles',
            'satuan_kerja_id',
            'balai_kerja_id',
            'status',
            'no_handphone',
            'nik',
            'nrp',
            'nip',
            'surat_penugasan_url',
            'user_id_sipasti',
            'updated_at',
        ]
    );


    }
}
