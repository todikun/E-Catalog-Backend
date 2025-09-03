<?php

namespace Database\Seeders;

use App\Models\SatuanBalaiKerja;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BalaiKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SatuanBalaiKerja::upsert(
            [
                [
                    'nama' => 'BALAI BESAR WILAYAH SUNGAI CILIWUNG-CISADANE',
                    'unor_id' => 1,
                    'created_at' => now(),
                    'edited_at' => now(),
                ]
            ],
            ['nama'],
            ['unor_id', 'edited_at']
        );
    }
}
