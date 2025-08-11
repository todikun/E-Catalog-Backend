<?php

namespace App\Services;

use App\Models\PerencanaanData;

class PerencanaanDataService
{
    public function listAllPerencanaanData($id)
    {
        $query = PerencanaanData::with([
            'informasiUmum',
            'material.provinces',
            'material.cities',
            'peralatan.provinces',
            'peralatan.cities',
            'tenagaKerja.provinces',
            'tenagaKerja.cities',
            'shortlistVendor'
        ])
            ->where('informasi_umum_id', $id)->first();

        if ($query) {
            $query->material = $query->material->map(function ($item) {
                $item->provinsi = $item->provinces ? $item->provinces->nama_provinsi : null;
                $item->kota = $item->cities ? $item->cities->nama_kota : null;
                unset($item->provinces, $item->cities);
                return $item;
            });

            $query->tenagaKerja = $query->tenagaKerja->map(function ($item) {
                $item->provinsi = $item->provinces ? $item->provinces->nama_provinsi : null;
                $item->kota = $item->cities ? $item->cities->nama_kota : null;
                unset($item->provinces, $item->cities);
                return $item;
            });

            $query->peralatan = $query->peralatan->map(function ($item) {
                $item->provinsi = $item->provinces ? $item->provinces->nama_provinsi : null;
                $item->kota = $item->cities ? $item->cities->nama_kota : null;
                unset($item->provinces, $item->cities);
                return $item;
            });

            $response = [
                'id' => $query->id,
                'informasi_umum_id' => $query->informasi_umum_id,
                'identifikasi_kebutuhan_id' => $query->identifikasi_kebutuhan_id,
                'shortlist_vendor_id' => $query->shortlist_vendor_id,
                'informasi_umum' => $query->informasiUmum,
                'material' => $query->material,
                'peralatan' => $query->peralatan,
                'tenagaKerja' => $query->tenagaKerja,
                'shortlist_vendor' => $query->shortlistVendor,
            ];
        }

        return $response;
    }

    public function updatePerencanaanData($informasiUmumId, $field, $value)
    {
        $valueToUpdate = [];

        if ($field == 'identifikasi_kebutuhan') {
            $valueToUpdate['identifikasi_kebutuhan_id'] = $value;
        } elseif ($field == 'shortlist_vendor') {
            $valueToUpdate['shortlist_vendor_id'] = $value;
        } else {
            return false;
        }

        PerencanaanData::updateOrCreate(
            [
                'informasi_umum_id' => $informasiUmumId,
            ],
            $valueToUpdate
        );

        return true;
    }

    public function changeStatusPerencanaanData($status, $informasiUmumId)
    {
        $dataSave = PerencanaanData::where('informasi_umum_id', $informasiUmumId)
            ->update(['status' => $status]);

        return $dataSave > 0;
    }

    public function tableListPerencanaanData($status)
    {
        return PerencanaanData::join('informasi_umum', 'perencanaan_data.informasi_umum_id', '=', 'informasi_umum.id')
            ->whereIn('perencanaan_data.status', $status)
            ->select([
                'perencanaan_data.informasi_umum_id As id',
                'perencanaan_data.status',
                'informasi_umum.nama_paket',
                'informasi_umum.nama_balai',
                'informasi_umum.nama_ppk',
                'informasi_umum.jabatan_ppk',
                'informasi_umum.kode_rup'
            ])
            ->get();
    }
}
