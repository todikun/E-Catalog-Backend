<?php

namespace App\Services;

use App\Models\InformasiUmum;
use App\Models\PerencanaanData;

class InformasiUmumService
{
    public function getDataInformasiUmumById($informasiUmumId)
    {
        return InformasiUmum::find($informasiUmumId);
    }

    public function checkNamaPaket($namaPaket)
    {
        return InformasiUmum::where('nama_paket', $namaPaket)->exists();
    }

    public function saveInformasiUmum($dataInformasiUmum)
    {
        $informasiUmum = InformasiUmum::updateOrCreate(
            [
                'nama_paket' => $dataInformasiUmum->nama_paket,
            ],
            [
                'kode_rup' => $dataInformasiUmum->kode_rup == null ? '' : $dataInformasiUmum->kode_rup,
                'nama_paket' => $dataInformasiUmum->nama_paket,
                'jabatan_ppk' => $dataInformasiUmum->jabatan_ppk,
                'jenis_informasi' => $dataInformasiUmum->tipe_informasi_umum,
                'nama_balai' => $dataInformasiUmum->tipe_informasi_umum == 'manual' ? $dataInformasiUmum->nama_balai : null,
                'tipologi' => $dataInformasiUmum->tipologi == null ? '' : $dataInformasiUmum->tipologi,
                'nama_ppk' => $dataInformasiUmum->nama_ppk,
            ]
        );

        $informasiUmumId = $informasiUmum->id;
        $savePrencanaanData = $this->savePerencanaanData($informasiUmumId, 'informasi_umum_id');
        if (!$informasiUmum && !$savePrencanaanData) {
            return false;
        }

        return $informasiUmum;
    }

    private function savePerencanaanData($id, $namaField)
    {
        $data = PerencanaanData::updateOrCreate(
            [
                $namaField => $id,
            ]
        );
        return $data;
    }

    public function getInformasiUmumByPerencanaanId($id)
    {
        return InformasiUmum::with('perencanaanData')
            ->select(
                'kode_rup',
                'nama_paket',
                'nama_ppk',
                'jabatan_ppk',
                'nama_balai',
                'tipologi',
                'jenis_informasi'
            )
            ->where('id', $id)
            ->get()->first();
    }
}
