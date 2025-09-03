<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Peralatan;
use App\Models\TenagaKerja;
use Illuminate\Support\Facades\DB;

class EksternalService
{

    public function getMaterialData()
    {
        $data = Material::select(
            'material.id As material_id',
            'perencanaan_data.identifikasi_kebutuhan_id As identifikasi_kebutuhan_id',
            'provinces.nama_provinsi As provinsi',
            'cities.nama_kota As kabupaten_kota',
            'material.nama_material As jenis_material',
            'material.spesifikasi As spesifikasi',
            'material.ukuran As ukuran',
            'material.satuan As satuan',
            'material.kodefikasi As kodefikasi',
            'material.kelompok_material As kelompok_material',
            'material.harga_konversi_satuan_setempat As harga_satuan_pokok',
            'data_vendors.nama_vendor As nama_instansi_perusahaan',
            'data_vendors.alamat As alamat_instansi',
            'data_vendors.no_telepon As no_telepon',
            'data_vendors.koordinat As titik_koordinat_material',
        )
            ->join('provinces', 'material.provincies_id', '=', 'provinces.kode_provinsi')
            ->join('cities', 'material.cities_id', '=', 'cities.kode_kota')
            ->join('perencanaan_data', 'material.identifikasi_kebutuhan_id', '=', 'perencanaan_data.identifikasi_kebutuhan_id')
            ->join('kuisioner_pdf_data', 'perencanaan_data.identifikasi_kebutuhan_id', '=', 'kuisioner_pdf_data.shortlist_id')
            ->join('data_vendors', 'kuisioner_pdf_data.vendor_id', '=', 'data_vendors.id')
            ->whereJsonContains('kuisioner_pdf_data.material_id', DB::raw('CAST(material.id AS CHAR)'))
            ->where('perencanaan_data.status', config('constants.STATUS_PENYEBARLUASAN_DATA'))
            ->get();

        $responseData = [];

        foreach ($data as $value) {
            $responseData[] = [
                'provinsi' => $value->provinsi,
                'kabupaten/kota' => $value->kabupaten_kota,
                'jenis_material' => $value->jenis_material,
                'spesifikasi' => $value->spesifikasi,
                'ukuran' => $value->ukuran,
                'kodefikasi' => $value->kodefikasi,
                'kelompok_material' => $value->kelompok_material,
                'satuan' => $value->satuan,
                'harga_satuan_pokok' => $value->harga_satuan_pokok,
                'nama_instansi_perusahaan' => $value->nama_instansi_perusahaan,
                'alamat_instansi' => $value->alamat_instansi,
                'no_telepon' => $value->no_telepon,
                'titik_koordinat_material' => $value->titik_koordinat_material,
                'bulan_tahun' => '-',
                'sumber_referensi' => '-',
                'dokumen_pendukung' => '-',
            ];
        }

        return $responseData;
    }
    public function getPeralatanData()
    {
        $data = Peralatan::select(
            'peralatan.id As peralatan_id',
            'perencanaan_data.identifikasi_kebutuhan_id As identifikasi_kebutuhan_id',
            'provinces.nama_provinsi As provinsi',
            'cities.nama_kota As kabupaten_kota',
            'peralatan.nama_peralatan As jenis_peralatan',
            'peralatan.kapasitas As kapasitas',
            'peralatan.spesifikasi As daya',
            'peralatan.kodefikasi As kodefikasi',
            'peralatan.kelompok_peralatan As kelompok_peralatan',
            'peralatan.satuan As satuan',
            'peralatan.harga_pokok As harga_beli',
            'peralatan.harga_sewa_konversi As harga_sewa',
            'data_vendors.nama_vendor As nama_instansi_perusahaan',
            'data_vendors.alamat As alamat_instansi',
            'data_vendors.no_telepon As no_telepon',
        )
            ->join('provinces', 'peralatan.provincies_id', '=', 'provinces.kode_provinsi')
            ->join('cities', 'peralatan.cities_id', '=', 'cities.kode_kota')
            ->join('perencanaan_data', 'peralatan.identifikasi_kebutuhan_id', '=', 'perencanaan_data.identifikasi_kebutuhan_id')
            ->join('kuisioner_pdf_data', 'perencanaan_data.identifikasi_kebutuhan_id', '=', 'kuisioner_pdf_data.shortlist_id')
            ->join('data_vendors', 'kuisioner_pdf_data.vendor_id', '=', 'data_vendors.id')
            ->whereJsonContains('kuisioner_pdf_data.peralatan_id', DB::raw('CAST(peralatan.id AS CHAR)'))
            ->where('perencanaan_data.status', config('constants.STATUS_PENYEBARLUASAN_DATA'))
            ->get();

        $responseData = [];

        foreach ($data as $value) {
            $responseData[] = [
                'provinsi' => $value->provinsi,
                'kabupaten/kota' => $value->kabupaten_kota,
                'jenis_peralatan' => $value->jenis_peralatan,
                'kapasitas' => $value->kapasitas,
                'daya' => $value->daya,
                'kodefikasi' => $value->kodefikasi,
                'kelompok_peralatan' => $value->kelompok_peralatan,
                'satuan' => $value->satuan,
                'harga_beli' => $value->harga_beli,
                'harga_sewa' => $value->harga_sewa,
                'nama_instansi_perusahaan' => $value->nama_instansi_perusahaan,
                'alamat_instansi' => $value->alamat_instansi,
                'no_telepon' => $value->no_telepon,
                'bulan_tahun' => '-',
                'sumber_referensi' => '-',
                'dokumen_pendukung' => '-',
            ];
        }

        return $responseData;
    }
    public function getTenagaKerjaData()
    {
        $data = TenagaKerja::select(
            'tenaga_kerja.id As tenaga_kerja_id',
            'perencanaan_data.identifikasi_kebutuhan_id As identifikasi_kebutuhan_id',
            'provinces.nama_provinsi As provinsi',
            'cities.nama_kota As kabupaten_kota',
            'tenaga_kerja.jenis_tenaga_kerja As jenis_tkk',
            'tenaga_kerja.kodefikasi As kodefikasi',
            'tenaga_kerja.satuan As satuan',
            'tenaga_kerja.harga_konversi_perjam As upah_tenaga_kerja',
            'data_vendors.nama_vendor As nama_instansi_perusahaan',
            'data_vendors.alamat As alamat_instansi',
            'data_vendors.no_telepon As no_telepon',
        )
            ->join('provinces', 'tenaga_kerja.provincies_id', '=', 'provinces.kode_provinsi')
            ->join('cities', 'tenaga_kerja.cities_id', '=', 'cities.kode_kota')
            ->join('perencanaan_data', 'tenaga_kerja.identifikasi_kebutuhan_id', '=', 'perencanaan_data.identifikasi_kebutuhan_id')
            ->join('kuisioner_pdf_data', 'perencanaan_data.identifikasi_kebutuhan_id', '=', 'kuisioner_pdf_data.shortlist_id')
            ->join('data_vendors', 'kuisioner_pdf_data.vendor_id', '=', 'data_vendors.id')
            ->whereJsonContains('kuisioner_pdf_data.tenaga_kerja_id', DB::raw('CAST(tenaga_kerja.id AS CHAR)'))
            ->where('perencanaan_data.status', config('constants.STATUS_PENYEBARLUASAN_DATA'))
            ->get();

        $responseData = [];

        foreach ($data as $value) {
            $responseData[] = [
                'provinsi' => $value->provinsi,
                'kaupaten/kota' => $value->kabupaten_kota,
                'jenis_tkk' => $value->jenis_tkk,
                'kodefikasi' => $value->kodefikasi,
                'satuan' => $value->satuan,
                'upah_tenaga_kerja' => $value->upah_tenaga_kerja,
                'nama_instansi_perusahaan' => $value->nama_instansi_perusahaan,
                'alamat_instansi' => $value->alamat_instansi,
                'no_telepon' => $value->no_telepon,
                'bulan_tahun' => '-',
                'sumber_referensi' => '-',
                'dokumen_pendukung' => '-',
            ];
        }

        return $responseData;
    }
}
