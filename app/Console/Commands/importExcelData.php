<?php

namespace App\Console\Commands;

use App\Models\SatuanBalaiKerja;
use App\Models\SatuanKerja;
use App\Models\Unor;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class importExcelData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:excel {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data into database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("file missing : $filePath");
            return;
        }

        try {
            $spreadSheet = IOFactory::load($filePath);
            $sheet = $spreadSheet->getActiveSheet();

            foreach ($sheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $data = [];

                foreach ($cellIterator as $value) {
                    $data[] = $value->getValue();
                }

                if ($filePath == 'resources/excel/satuan_kerja.xlsx') {
                    SatuanKerja::create([
                        'nama' => $data[0],
                    ]);
                } else {
                    if (!empty($data[0]) && !empty($data[1])) {
                        SatuanBalaiKerja::create([
                            'nama' => $data[0],
                            'unor_id' => $data[1]
                        ]);
                    }
                }
            }

            $unor = [
                'bina marga',
                'cipta karya',
                'perumahan',
                'sumber daya air'
            ];

            if ($filePath == 'resources/excel/bina_marga.xlsx') {
                foreach ($unor as $value) {
                    Unor::create([
                        'nama' => $value
                    ]);
                }
            }

            $this->info('Data imported successfully.');
        } catch (\Exception $e) {
            $this->alert($e->getMessage());
        }
    }
}
