<?php

namespace App\Http\Controllers;

use App\Services\PengumpulanDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use function PHPUnit\Framework\throwException;

class SurveyKuisionerController extends Controller
{
    protected $pengumpulanDataService;

    public function __construct(
        PengumpulanDataService $pengumpulanDataService
    ) {
        $this->pengumpulanDataService = $pengumpulanDataService;
    }

    public function generateLinkKuisioner($id)
    {
        $urlToken = $this->pengumpulanDataService->generateLinkKuisioner($id);
        if ($urlToken) {
            $this->pengumpulanDataService->changeStatus($id, config('constants.STATUS_PENGISIAN_PETUGAS'));
            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data' => $urlToken
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_GET'),
                'data' => []
            ]);
        }
    }

    public function getDataForSurveyKuisioner(Request $request)
    {
        $token = $request->query('token');

        $data = json_decode(Crypt::decryptString($token), true);

        if (!$data || !isset($data['timestamp'], $data['shortlist_id'])) {
            throw new \Exception('Token tidak valid atau rusak');
        }

        $timestamp = $data['timestamp'];
        $shortlistId = $data['shortlist_id'];

        if (now()->timestamp - $timestamp > 10 * 24 * 60 * 60) {
            return response()->json([
                'status' => 'error',
                'message' => 'Link kadaluarsa, telah melewati 10 hari',
                'data' => []
            ]);
        }

        try {
            $getData = $this->pengumpulanDataService->getDataForKuisioner($shortlistId);
            if ($getData) {
                return response()->json([
                    'status' => 'success',
                    'message' => config('constants.SUCCESS_MESSAGE_GET'),
                    'data' => $getData
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_GET'),
                'error' => $e->getMessage()
            ],404);
        }
    }

    public function storeSurveyKuisioner(Request $request)
    {
        try {
            $materialResult = [];
            foreach ($request->material as $material) {
                $materialResult[] = $this->pengumpulanDataService->storeIdentifikasiSurvey($material, 'material');
            }
            $peralatanResult = [];
            foreach ($request->peralatan as $peralatan) {
                $peralatanResult[] = $this->pengumpulanDataService->storeIdentifikasiSurvey($peralatan, 'peralatan');
            }
            $tenagaKerjaResult = [];
            foreach ($request->tenaga_kerja as $tenagaKerja) {
                $tenagaKerjaResult[] = $this->pengumpulanDataService->storeIdentifikasiSurvey($tenagaKerja, 'tenaga_kerja');
            }

            $storeKeteranganPetugas = $this->pengumpulanDataService->storeKeteranganPetugasSurvey($request);

            $response = [
                'keterangan' => $storeKeteranganPetugas,
                'material' => $materialResult,
                'peralatan' => $peralatanResult,
                'tenaga_kerja' => $tenagaKerjaResult,
            ];

            if ($request['type_save'] == 'final') {
                $this->pengumpulanDataService->changeStatus($request['identifikasi_kebutuhan_id'], config('constants.STATUS_ENTRI_DATA'));
            }

            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_GET'),
                'error' => $e->getMessage()
            ]);
        }
    }
}
