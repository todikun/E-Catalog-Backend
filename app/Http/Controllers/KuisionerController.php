<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Peralatan;
use App\Models\ShortlistVendor;
use App\Models\TenagaKerja;
use App\Services\InformasiUmumService;

class KuisionerController extends Controller
{

    protected $informasiUmumService;

    public function __construct(InformasiUmumService $informasi_umum_service)
    {
        $this->informasiUmumService = $informasi_umum_service;
    }

    public function getInformasiUmum($idInformasiUmum)
    {
        $informasiUmumData = $this->informasiUmumService->getDataInformasiUmumById($idInformasiUmum);

        if (is_null($informasiUmumData)) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Data kosong',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 'berhasil',
            'message' => 'Data berhasil diambil',
            'data' => $informasiUmumData
        ], 200);
    }

    public function getMaterial($identifikasiKebutuhanId)
    {
        $materialData = Material::where("identifikasi_kebutuhan_id", $identifikasiKebutuhanId)->get();

        if ($materialData->isEmpty()) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Data kosong',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 'berhasil',
            'message' => 'Data berhasil diambil',
            'data' => $materialData
        ]);
    }


    public function getPeralatan($identifikasiKebutuhanId)
    {
        $peralatanData = Peralatan::where('identifikasi_kebutuhan_id', $identifikasiKebutuhanId)->get();

        if ($peralatanData->isEmpty()) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Data kosong',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 'berhasil',
            'message' => 'Data berhasil diambil',
            'data' => $peralatanData
        ]);
    }

    public function getTenagaKerja($identifikasiKebutuhanId)
    {
        $tenagaKerjaData = TenagaKerja::where('identifikasi_kebutuhan_id', $identifikasiKebutuhanId)->get();
        if ($tenagaKerjaData->isEmpty()) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Data kosong',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 'berhasil',
            'message' => 'Data berhasil diambil',
            'data' => $tenagaKerjaData
        ], 200);
    }

    public function getShortListVendor($shortListVendorId)
    {
        // IdentifikasiKebutuhanId sama persis dengan shortListVendorId
        $shortListVendorData = ShortlistVendor::where('shortlist_vendor_id', $shortListVendorId)->get();
        if ($shortListVendorData->isEmpty()) {
            return response()->json([
                'status' => 'gagal',
                'message' => 'Data kosong',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 'berhasil',
            'message' => 'Data berhasil diambil',
            'data' => $shortListVendorData
        ], 200);
    }

    public function generatePdf($shortListVendorId){
        
    }
}
