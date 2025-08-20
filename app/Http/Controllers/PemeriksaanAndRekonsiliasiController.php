<?php

namespace App\Http\Controllers;

use App\Services\PengumpulanDataService;
use App\Services\PerencanaanDataService;
use Illuminate\Http\Request;

class PemeriksaanAndRekonsiliasiController extends Controller
{
    protected $perencanaanDataService;
    protected $pengumpulanDataService;

    public function __construct(
        PerencanaanDataService $perencanaanDataService,
        PengumpulanDataService $pengumpulanDataService
    ) {
        $this->perencanaanDataService = $perencanaanDataService;
        $this->pengumpulanDataService = $pengumpulanDataService;
    }

    public function getAllDataPemeriksaanRekonsiliasi()
    {
        $status = [
            config('constants.STATUS_REKONSILIASI'),
            config('constants.STATUS_PENYEBARLUASAN_DATA'),
        ];

        $listData = $this->perencanaanDataService->tableListPerencanaanData($status);

        if ($listData) {
            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data' => $listData
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_GET'),
                'data' => []
            ]);
        }
    }

    public function getDataPemeriksaanRekonsiliasi($shortlistId)
    {
        // using primary key ID from shortlist_vendor table
        $getData = $this->pengumpulanDataService->getEntriData($shortlistId);
        $pemeriksaanData = $this->pengumpulanDataService->getPemeriksaanDataList($getData['data_vendor_id'], $getData['identifikasi_kebutuhan_id']);

        $responseData = [
            'data' => $getData,
            'pemeriksaan_data' => $pemeriksaanData
        ];

        if ($getData && $pemeriksaanData) {
            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data' => $responseData
            ]);
        }
    }

    public function storePemeriksaanRekonsiliasi(Request $request)
    {
        try {
            if ($request->hasFile('berita_acara_validasi')) {
                $filePath = $request->file('berita_acara_validasi')->store('sk_penugasan');
            } else {
                $filePath = "-";
            }

            $storeData = $this->pengumpulanDataService->pemeriksaanDataList($request);

            foreach ($storeData as $value) {
                if (strtolower($value['status_pemeriksaan']) == "tidak memenuhi") {
                    $this->pengumpulanDataService->changeStatusValidation($request['identifikasi_kebutuhan_id'], $filePath, config('constants.STATUS_REKONSILIASI'));
                    break;
                }
            }

            if ($storeData) {
                $this->pengumpulanDataService->changeStatusValidation($request['identifikasi_kebutuhan_id'], $filePath, config('constants.STATUS_PENYEBARLUASAN_DATA'));
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data berhasil disimpan',
                    'data' => $storeData
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data',
                'error' => $e->getMessage()
            ]);
        }
    }
}
