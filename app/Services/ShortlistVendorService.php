<?php

namespace App\Services;

use App\Models\DataVendor;
use App\Models\PerencanaanData;
use App\Models\ShortlistVendor;
use App\Models\KuisionerPdfData;
use Illuminate\Support\Facades\DB;

class ShortlistVendorService
{
    private function getIdentifikasiKebutuhanByIdentifikasiId($id)
    {
        $getDataIdentifikasi = PerencanaanData::with([
            'material:id,identifikasi_kebutuhan_id,nama_material,spesifikasi,ukuran',
            'peralatan:id,identifikasi_kebutuhan_id,nama_peralatan,spesifikasi,kapasitas',
            'tenagaKerja:id,identifikasi_kebutuhan_id,jenis_tenaga_kerja'
        ])->select('identifikasi_kebutuhan_id')->where('identifikasi_kebutuhan_id', $id)
            ->get();

        $phrases = $getDataIdentifikasi->flatMap(function ($item) {
                $materials   = optional($item->material)->pluck('nama_material')->filter();
                $peralatans  = optional($item->peralatan)->pluck('nama_peralatan')->filter();
                $tenagaKerja = optional($item->tenagaKerja)->pluck('jenis_tenaga_kerja')->filter();

                return $materials->concat($peralatans)->concat($tenagaKerja);
            });

            // Ubah jadi keywords: kata1 atau kata1+kata2
            $keywords = $phrases->flatMap(function ($str) {
                $parts = preg_split('/\s+/u', trim((string) $str));
                if (!$parts || $parts[0] === '') return [];
                $out = [$parts[0]];                         // kata pertama
                if (count($parts) > 1) $out[] = $parts[0].' '.$parts[1]; // kata1+kata2
                return $out;
            })
            ->map(fn ($s) => trim(preg_replace('/\s+/u', ' ', $s))) // normalisasi spasi
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $keywords;
    }

    public function getDataVendor($id)
    {
        $resultArray = $this->getIdentifikasiKebutuhanByIdentifikasiId($id);
        //dd($resultArray);

        $queryDataVendors = DataVendor::all();

        $dataVendors = [];
        foreach ($queryDataVendors as $value) {
            $sumberDayaArray = explode(';', $value->sumber_daya);

            $resultElemination = $this->eleminationArray($resultArray, $sumberDayaArray);
            if (!empty($resultElemination)) {
                $dataVendors[] = $value;
            }
        }

        $result = [];

        foreach ($queryDataVendors as $vendor) {
            $grouped = collect($vendor->sumber_daya_vendor)->groupBy('jenis');

            foreach ($grouped as $jenis => $list) {
                $result[$jenis][] = [
                    'id' => $vendor->id,
                    'nama_vendor' => $vendor->nama_vendor,
                    'pemilik' => $vendor->nama_pic,
                    'alamat' => $vendor->alamat,
                    'kontak' => $vendor->no_telepon,
                    'sumber_daya' => $vendor->sumber_daya,
                    'sumber_daya_vendor' => $list->map(function ($sd) {
                        return [
                            'id'          => $sd['id'],
                            'jenis'       => $sd['jenis'],
                            'nama'        => $sd['nama'],
                            'spesifikasi' => $sd['spesifikasi']
                        ];
                    })->toArray()
                ];
            }
        }
        return $result;
    }

    public function storeShortlistVendor($data, $shortlistVendorId)
    {
        //$makeKuisioner = app(GeneratePdfService::class)->generatePdfMaterialNatural($data['data_vendor_id']);

        $shortlistVendorArray = [
            'data_vendor_id' => $data['data_vendor_id'],
            'shortlist_vendor_id' => $shortlistVendorId,
            'nama_vendor' => $data['nama_vendor'],
            'pemilik_vendor' => $data['pemilik_vendor'],
            'alamat' => $data['alamat'],
            'kontak' => $data['kontak'],
            'sumber_daya' => $data['sumber_daya']
        ];

        $shortlistVendor = ShortlistVendor::updateOrCreate(
            [
                'data_vendor_id' => $data['data_vendor_id'],
                'shortlist_vendor_id' => $shortlistVendorId
            ],
            $shortlistVendorArray
        );

        return $shortlistVendor->toArray();
    }

    public function getShortlistVendorResult($id)
    {
        return ShortlistVendor::where('shortlist_vendor_id', $id)->get;
    }

    private function eleminationArray(array $array1, array $array2)
    {
        $matches = [];

        $lowercasedArray1 = array_map('strtolower', $array1);
        $lowercasedArray2 = array_map('strtolower', $array2);

        foreach ($lowercasedArray1 as $value1) {
            foreach ($lowercasedArray2 as $value2) {
                if (strpos($value1, $value2) !== false) {
                    $matches[] = $value1;
                }
            }
        }

        return array_values(array_unique($matches));
    }

    public function getIdentifikasiByShortlist($id, $idShortlistVendor)
    {
        $query = ShortlistVendor::with([
            'material' => function ($subQuery) {
                $subQuery->select('id', 'identifikasi_kebutuhan_id', 'nama_material', 'satuan', 'spesifikasi', 'merk');
            },
            'peralatan' => function ($subQuery) {
                $subQuery->select('id', 'identifikasi_kebutuhan_id', 'nama_peralatan', 'satuan', 'spesifikasi', 'merk');
            },
            'tenaga_kerja' => function ($subQuery) {
                $subQuery->select('id', 'identifikasi_kebutuhan_id', 'jenis_tenaga_kerja', 'satuan');
            }
        ])
            ->where('shortlist_vendor.id', $id)
            ->where(function ($query) use ($idShortlistVendor) {
                // Relationship Filtering
                $query->whereHas('material', function ($subQuery) use ($idShortlistVendor) {
                    $subQuery->where('identifikasi_kebutuhan_id', $idShortlistVendor);
                })
                    ->orWhereHas('peralatan', function ($subQuery) use ($idShortlistVendor) {
                        $subQuery->where('identifikasi_kebutuhan_id', $idShortlistVendor);
                    })
                    ->orWhereHas('tenaga_kerja', function ($subQuery) use ($idShortlistVendor) {
                        $subQuery->where('identifikasi_kebutuhan_id', $idShortlistVendor);
                    });
            })
            ->select('id', 'data_vendor_id', 'shortlist_vendor_id', 'nama_vendor', 'pemilik_vendor', 'alamat', 'kontak', 'sumber_daya')
            ->first();

        if (!$query) {
            return [
                'id_vendor' => null,
                'identifikasi_kebutuhan' => [
                    'material' => [],
                    'peralatan' => [],
                    'tenaga_kerja' => []
                ]
            ];
        }

        // dd($query->toArray(true));

        $identifikasi = [
            'material' => [],
            'peralatan' => [],
            'tenaga_kerja' => []
        ];

        if (!empty($query->sumber_daya)) {
            $sumberDaya = explode(',', $query->sumber_daya);
        } else {
            $sumberDaya = [];
        }

        // dd($sumberDaya);

        foreach ($sumberDaya as $value) {
            if (count($query['material'])) {
                foreach ($query['material'] as $data) {
                    if (strpos(strtolower($data['nama_material']), strtolower($value)) !== false) {
                        $identifikasi['material'][] = $data;
                    }
                }
            }

            if (count($query['peralatan'])) {
                foreach ($query['peralatan'] as $data) {
                    if (strpos(strtolower($data['nama_peralatan']), strtolower($value)) !== false) {
                        $identifikasi['peralatan'][] = $data;
                    }
                }
            }

            if (count($query['tenaga_kerja'])) {
                foreach ($query['tenaga_kerja'] as $data) {
                    if (strpos(strtolower($data['jenis_tenaga_kerja']), strtolower($value)) !== false) {
                        $identifikasi['tenaga_kerja'][] = $data;
                    }
                }
            }
        }

        $resultData = [
            'id_vendor' => $query->data_vendor_id,
            'identifikasi_kebutuhan' => $identifikasi
        ];

        return $resultData;
    }

    public function saveKuisionerPdfData($idVendor, $idShortlistVendor, $material, $peralatan, $tenagaKerja)
    {

        $kuisionerData = KuisionerPdfData::updateOrCreate(
            ['shortlist_id' => $idShortlistVendor, 'vendor_id' => $idVendor],
            [
                'material_id' => (count($material)) ? json_encode($material) : null,
                'peralatan_id' => (count($peralatan)) ? json_encode($peralatan) : null,
                'tenaga_kerja_id' => (count($tenagaKerja)) ? json_encode($tenagaKerja) : null,
            ]
        );

        return $kuisionerData->toArray();
    }

    public function saveUrlPdf($vendorId, $shortlistVendorId, $url)
    {

        $dataVendor = DataVendor::find($vendorId);
        if (!$dataVendor) {
            throw new \Exception("DataVendor with id $vendorId not found.");
        }

        $namaVendor = $dataVendor->nama_vendor;
        $pemilikVendor = $dataVendor->nama_pic;
        $alamat = $dataVendor->alamat;
        $no_telepon = $dataVendor->no_telepon ?? $dataVendor->no_hp;

        $data = ShortlistVendor::updateOrCreate(
            ['data_vendor_id' => $vendorId, 'shortlist_vendor_id' => $shortlistVendorId],
            [
                'url_kuisioner' => $url,
                'nama_vendor' => $namaVendor,
                'pemilik_vendor' => $pemilikVendor,
                'alamat' => $alamat,
                'kontak' => $no_telepon
            ]
        );
        return $data['url_kuisioner'];
    }
}
