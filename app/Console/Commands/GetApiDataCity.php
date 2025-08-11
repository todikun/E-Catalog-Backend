<?php

namespace App\Console\Commands;

use App\Models\Cities;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetApiDataCity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:get-city';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $apiUrlCities = 'https://sipedas.pertanian.go.id/api/wilayah/list_wilayah?thn=2024&lvl=10&lv2=12';

        $response2 = Http::get($apiUrlCities);

        if ($response2->successful()) {
            $data2 = $response2->json();

            foreach ($data2 as $fullCode  => $citiesName) {
                
                $provinsiId = substr($fullCode, 0, 2);

                Cities::updateOrCreate(
                    ['provinsi_id' => $provinsiId, 'kode_kota' => $fullCode],
                    ['nama_kota' => $citiesName],
                );
            }

            $this->info('Provinces data successfully retrieved from API and saved to the database.');
        } else {
            $this->error('Failed to retrieve data from API. Status Code: ' . $response2->status());
        }
        return 0;
    }
}
