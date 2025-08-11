<?php

namespace Database\Seeders;

use App\Models\Roles;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Roles::insert([
            ['nama' => 'Tim Teknis Balai'],
            ['nama' => 'PJ Balai'],
            ['nama' => 'Pengawas'],
            ['nama' => 'Petugas Lapangan'],
            ['nama' => 'Direktorat'],
            ['nama' => 'Pengolah Data'],
            ['nama' => 'Koordinator Provinsi'],
            ['nama' => 'eksternal app']
        ]);
    }
}
