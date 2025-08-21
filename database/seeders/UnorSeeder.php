<?php

namespace Database\Seeders;

use App\Models\Unor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $rows = [
            ['nama' => 'Unit 1', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Unit 2', 'created_at' => now(), 'updated_at' => now()],
        ];


        collect($rows)->chunk(500)->each(function ($chunk) {
            Unor::upsert(
                $chunk->all(),
                ['nama'],
                ['updated_at']
            );
        });
    }

}
