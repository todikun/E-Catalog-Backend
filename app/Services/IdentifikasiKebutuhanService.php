<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Peralatan;
use App\Models\TenagaKerja;

class IdentifikasiKebutuhanService
{
    public function storeMaterial($dataMaterial, $identifikasiKebutuhanId)
    {
        $material = Material::updateOrCreate(
            [
                'identifikasi_kebutuhan_id' => $identifikasiKebutuhanId,
                'nama_material' => $dataMaterial['nama_material'],
                'spesifikasi' => $dataMaterial['spesifikasi'],
            ],
            [
                'satuan' => $dataMaterial['satuan'],
                'ukuran' => $dataMaterial['ukuran'],
                'kodefikasi' => $dataMaterial['kodefikasi'],
                'kelompok_material' => $dataMaterial['kelompok_material'],
                'jumlah_kebutuhan' => $dataMaterial['jumlah_kebutuhan'],
                'merk' => $dataMaterial['merk'],
                'provincies_id' => $dataMaterial['provincies_id'],
                'cities_id' => $dataMaterial['cities_id'],
            ]
        );

        return $material;
    }

    public function storePeralatan($dataPeralatan, $identifikasiKebutuhanId)
    {
        $peralatan = Peralatan::updateOrCreate(
            [
                'identifikasi_kebutuhan_id' => $identifikasiKebutuhanId,
                'nama_peralatan' => $dataPeralatan['nama_peralatan'],
                'spesifikasi' => $dataPeralatan['spesifikasi'],
            ],
            [
                'satuan' => $dataPeralatan['satuan'],
                'kapasitas' => $dataPeralatan['kapasitas'],
                'kodefikasi' => $dataPeralatan['kodefikasi'],
                'kelompok_peralatan' => $dataPeralatan['kelompok_peralatan'],
                'jumlah_kebutuhan' => $dataPeralatan['jumlah_kebutuhan'],
                'merk' => $dataPeralatan['merk'],
                'provincies_id' => $dataPeralatan['provincies_id'],
                'cities_id' => $dataPeralatan['cities_id'],
            ]
        );

        return $peralatan;
    }

    public function storeTenagaKerja($dataTenagaKerja, $identifikasiKebutuhanId)
    {
        $tenagaKerja = TenagaKerja::updateOrCreate(
            [
                'identifikasi_kebutuhan_id' => $identifikasiKebutuhanId,
                'jenis_tenaga_kerja' => $dataTenagaKerja['jenis_tenaga_kerja'],
                'kodefikasi' => $dataTenagaKerja['kodefikasi'],
            ],
            [
                'satuan' => $dataTenagaKerja['satuan'],
                'jumlah_kebutuhan' => $dataTenagaKerja['jumlah_kebutuhan'],
                'provincies_id' => $dataTenagaKerja['provincies_id'],
                'cities_id' => $dataTenagaKerja['cities_id'],
            ]
        );

        return $tenagaKerja;
    }

    public function getIdentifikasiKebutuhanByPerencanaanId($jenisIdentifikasi, $id)
    {
        if ($jenisIdentifikasi == 'material') {
            return Material::where('identifikasi_kebutuhan_id', $id)->get();
        } elseif ($jenisIdentifikasi == 'peralatan') {
            return Peralatan::where('identifikasi_kebutuhan_id', $id)->get();
        } elseif ($jenisIdentifikasi == 'tenaga_kerja') {
            return TenagaKerja::where('identifikasi_kebutuhan_id', $id)->get();
        }
        return false;
    }
}
