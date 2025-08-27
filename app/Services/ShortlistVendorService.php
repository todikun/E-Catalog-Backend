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
            if (count($parts) > 1) $out[] = $parts[0] . ' ' . $parts[1]; // kata1+kata2
            return $out;
        })
            ->map(fn($s) => trim(preg_replace('/\s+/u', ' ', $s))) // normalisasi spasi
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $keywords;
    }

    public function getDataVendor($id)
    {
        $resultArray = $this->getIdentifikasiKebutuhanByIdentifikasiId($id);

        // Normalisasi: hapus duplikat & kosong
        $resultArray = collect($resultArray)
            ->map(fn($s) => trim(mb_strtolower($s)))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $queryDataVendors = DataVendor::query()
            ->with(['sumber_daya_vendor' => function ($q) use ($resultArray) {
                $q->where(function ($subQuery) use ($resultArray) {
                    $subQuery->whereIn(DB::raw('LOWER(nama)'), $resultArray);

                    foreach ($resultArray as $keyword) {
                        $subQuery->orWhere(DB::raw('LOWER(nama)'), 'like', "%{$keyword}%");
                    }
                });
            }])
            ->whereHas('sumber_daya_vendor', function ($q) use ($resultArray) {
                $q->where(function ($subQuery) use ($resultArray) {
                    $subQuery->whereIn(DB::raw('LOWER(nama)'), $resultArray);
                    foreach ($resultArray as $keyword) {
                        $subQuery->orWhere(DB::raw('LOWER(nama)'), 'like', "%{$keyword}%");
                    }
                });
            })
            ->get();

        $result = [];
        foreach ($queryDataVendors as $vendor) {
            $grouped = collect($vendor->sumber_daya_vendor)->groupBy('jenis');

            foreach ($grouped as $jenis => $list) {
                $result[$jenis][] = [
                    'id'            => $vendor->id,
                    'nama_vendor'   => $vendor->nama_vendor,
                    'pemilik_vendor' => $vendor->nama_pic,
                    'alamat'        => $vendor->alamat,
                    'kontak'        => $vendor->no_telepon,
                    'sumber_daya'   => $vendor->sumber_daya,
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
        $shortlist = ShortlistVendor::with([
            'data_vendor.sumber_daya_vendor',
            'material' => function ($q) use ($idShortlistVendor) {
                $q->where('identifikasi_kebutuhan_id', $idShortlistVendor)
                    ->select('id', 'identifikasi_kebutuhan_id', 'nama_material', 'satuan', 'spesifikasi', 'merk');
            },
            'peralatan' => function ($q) use ($idShortlistVendor) {
                $q->where('identifikasi_kebutuhan_id', $idShortlistVendor)
                    ->select('id', 'identifikasi_kebutuhan_id', 'nama_peralatan', 'satuan', 'spesifikasi', 'merk');
            },
            'tenaga_kerja' => function ($q) use ($idShortlistVendor) {
                $q->where('identifikasi_kebutuhan_id', $idShortlistVendor)
                    ->select('id', 'identifikasi_kebutuhan_id', 'jenis_tenaga_kerja', 'satuan');
            }
        ])
            ->where('shortlist_vendor.id', $id)
            ->first();

        if (!$shortlist) {
            return [
                'id_vendor' => null,
                'identifikasi_kebutuhan' => [
                    'material' => [],
                    'peralatan' => [],
                    'tenaga_kerja' => []
                ]
            ];
        }

        $shortlist = $shortlist->toArray();

        $vendor = $shortlist['data_vendor'] ?? null;

        if (!$vendor) {
            return [
                'id_vendor' => null,
                'identifikasi_kebutuhan' => [
                    'material' => [],
                    'peralatan' => [],
                    'tenaga_kerja' => []
                ]
            ];
        }

        $sumberList = collect($vendor['sumber_daya_vendor'] ?? []);
        $result = [
            'material' => [],
            'peralatan' => [],
            'tenaga_kerja' => []
        ];

        // Filter material
        foreach ($shortlist['material'] as $mat) {
            $namaMaterial = $mat['nama_material'];

            $matches = $sumberList
                ->where('jenis', 'material')
                ->filter(fn($item) => stripos($item['nama'], $namaMaterial) !== false)
                ->map(fn($item) => ['nama' => $item['nama'], 'spesifikasi' => $item['spesifikasi']])
                ->values()
                ->toArray();

            if (!empty($matches)) {
                $result['material'][] = array_merge($mat, [
                    'tersedia_sebagai' => $matches
                ]);
            }
        }

        // Filter peralatan
        foreach ($shortlist['peralatan'] as $per) {
            $namaPeralatan = $per['nama_peralatan'];

            $matches = $sumberList
                ->where('jenis', 'peralatan')
                ->filter(fn($item) => stripos($item['nama'], $namaPeralatan) !== false)
                ->map(fn($item) => ['nama' => $item['nama'], 'spesifikasi' => $item['spesifikasi']])
                ->values()
                ->toArray();

            if (!empty($matches)) {
                $result['peralatan'][] = array_merge($per, [
                    'tersedia_sebagai' => $matches
                ]);
            }
        }

        // Filter tenaga kerja
        foreach ($shortlist['tenaga_kerja'] as $tk) {
            $namaTenaga = $tk['jenis_tenaga_kerja'];

            $matches = $sumberList
                ->where('jenis', 'tenaga_kerja')
                ->filter(fn($item) => stripos($item['nama'], $namaTenaga) !== false)
                ->map(fn($item) => ['nama' => $item['nama'], 'spesifikasi' => $item['spesifikasi']])
                ->values()
                ->toArray();

            if (!empty($matches)) {
                $result['tenaga_kerja'][] = array_merge($tk, [
                    'tersedia_sebagai' => $matches
                ]);
            }
        }

        return [
            'id_vendor' => $vendor['id'],
            'identifikasi_kebutuhan' => $result
        ];
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
