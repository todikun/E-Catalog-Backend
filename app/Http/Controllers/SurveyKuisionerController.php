<?php

namespace App\Http\Controllers;

use App\Services\PengumpulanDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Carbon;

class SurveyKuisionerController extends Controller
{
    protected $pengumpulanDataService;

    public function __construct(PengumpulanDataService $pengumpulanDataService)
    {
        $this->pengumpulanDataService = $pengumpulanDataService;
    }

public function generateLinkKuisioner($id)
{
    $result = $this->pengumpulanDataService->generateLinkKuisioner($id);

    if ($result && isset($result['url'], $result['token'], $result['expired_at'])) {
        $this->pengumpulanDataService->changeStatus($id, config('constants.STATUS_PENGISIAN_PETUGAS'));

        $expiredAtWib = \Illuminate\Support\Carbon::createFromTimestamp($result['expired_at'])
            ->timezone('Asia/Jakarta')
            ->format('d-m-Y H:i:s') . ' WIB';

        return response()->json([
            'status'  => 'success',
            'message' => config('constants.SUCCESS_MESSAGE_GET'),
            'data'    => [
                'url'         => $result['url'],
                // 'token'     => $result['token'], // <-- HIDDEN
                'expired_at'  => $result['expired_at'], 
                '_expired_at' => $expiredAtWib,         
            ],
        ]);
    }

    return response()->json([
        'status'  => 'error',
        'message' => config('constants.ERROR_MESSAGE_GET'),
        'data'    => [],
    ]);
}


  public function getDataForSurveyKuisioner(Request $request)
{
    $token = $request->query('token');

    try {
        $data = json_decode(\Illuminate\Support\Facades\Crypt::decryptString($token), true);
    } catch (\Throwable $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Token tidak valid atau rusak',
            'data'    => [],
        ], 400);
    }

    if (!$data || !isset($data['timestamp'], $data['shortlist_id'])) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Token tidak valid atau rusak',
            'data'    => [],
        ], 400);
    }

    $timestamp   = (int) $data['timestamp'];
    $shortlistId = $data['shortlist_id'];
    $expiredAt   = isset($data['expired_at']) ? (int) $data['expired_at'] : ($timestamp + 10 * 24 * 60 * 60);

    if (now()->timestamp > $expiredAt) {
        $expiredAtWib = \Illuminate\Support\Carbon::createFromTimestamp($expiredAt)
            ->timezone('Asia/Jakarta')
            ->format('d-m-Y H:i:s') . ' WIB';

        return response()->json([
            'status'  => 'error',
            'message' => 'Link kadaluarsa pada ' . $expiredAtWib,
            'data'    => [],
        ], 410);
    }

    try {
        $getData = $this->pengumpulanDataService->getDataForKuisioner($shortlistId);

        if ($getData) {
            $expiredAtWib = \Illuminate\Support\Carbon::createFromTimestamp($expiredAt)
                ->timezone('Asia/Jakarta')
                ->format('d-m-Y H:i:s') . ' WIB';

            return response()->json([
                'status'  => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data'    => [
                    'expired_at'  => $expiredAt,   // unix (UTC)
                    '_expired_at' => $expiredAtWib, // string (WIB)
                    'payload'     => $getData,
                ],
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => config('constants.ERROR_MESSAGE_GET'),
            'data'    => [],
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => config('constants.ERROR_MESSAGE_GET'),
            'error'   => $e->getMessage(),
        ], 404);
    }
}


    public function storeSurveyKuisioner(Request $request)
    {
        try {
            $materialResult = [];
            foreach ((array) $request->material as $material) {
                $materialResult[] = $this->pengumpulanDataService
                    ->storeIdentifikasiSurvey($material, 'material');
            }

            $peralatanResult = [];
            foreach ((array) $request->peralatan as $peralatan) {
                $peralatanResult[] = $this->pengumpulanDataService
                    ->storeIdentifikasiSurvey($peralatan, 'peralatan');
            }

            $tenagaKerjaResult = [];
            foreach ((array) $request->tenaga_kerja as $tenagaKerja) {
                $tenagaKerjaResult[] = $this->pengumpulanDataService
                    ->storeIdentifikasiSurvey($tenagaKerja, 'tenaga_kerja');
            }

            $storeKeteranganPetugas = $this->pengumpulanDataService
                ->storeKeteranganPetugasSurvey($request);

            $response = [
                'keterangan'   => $storeKeteranganPetugas,
                'material'     => $materialResult,
                'peralatan'    => $peralatanResult,
                'tenaga_kerja' => $tenagaKerjaResult,
            ];

            if (($request['type_save'] ?? null) === 'final') {
                $this->pengumpulanDataService->changeStatus(
                    $request['identifikasi_kebutuhan_id'],
                    config('constants.STATUS_ENTRI_DATA')
                );
            }

            return response()->json([
                'status'  => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data'    => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => config('constants.ERROR_MESSAGE_GET'),
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
