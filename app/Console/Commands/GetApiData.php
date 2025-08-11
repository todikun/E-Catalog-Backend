<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Provinces;
use App\Models\Cities;
use GuzzleHttp\Client;

class GetApiData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:get-provinces';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get data from an external API Province';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $apiUrl = 'https://sipedas.pertanian.go.id/api/wilayah/list_wilayah?thn=2024&lvl=10&lv2=11';

        $response = Http::get($apiUrl);

        if ($response->successful()) {
            $data = $response->json();

            foreach ($data as $provinceId => $provinceName) {
                Provinces::updateOrCreate(
                    ['kode_provinsi' => $provinceId],
                    ['nama_provinsi' => $provinceName],
                );
            }
            $this->info('Provinces data successfully retrieved from API and saved to the database.');
        } else {
            $this->error('Failed to retrieve data from API. Status Code: ' . $response->status());
        }
        return 0;
    }
}
