<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InformasiUmumService;
use App\Services\IdentifikasiKebutuhanService;
use App\Services\ShortlistVendorService;
use App\Services\PerencanaanDataService;
use App\Services\GeneratePdfService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PerencanaanDataController extends Controller
{
    protected $informasiUmumService;
    protected $IdentifikasiKebutuhanService;
    protected $shortlistVendorService;
    protected $perencanaanDataService;
    protected $generatePdfService;

    public function __construct(
        InformasiUmumService $informasiUmumService,
        IdentifikasiKebutuhanService $IdentifikasiKebutuhanService,
        ShortlistVendorService $shortlistVendorService,
        PerencanaanDataService $perencanaanDataService,
        GeneratePdfService $generatePdfService
    ) {
        $this->informasiUmumService = $informasiUmumService;
        $this->IdentifikasiKebutuhanService = $IdentifikasiKebutuhanService;
        $this->shortlistVendorService = $shortlistVendorService;
        $this->perencanaanDataService = $perencanaanDataService;
        $this->generatePdfService = $generatePdfService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDataInformasiUmumById($id)
    {
        try {
            $getDataInformasiUmum = $this->informasiUmumService->getDataInformasiUmumById($id);
            if (!$getDataInformasiUmum) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data informasi umum id ' . $id . ' ditemukan!',
                    'data' => $getDataInformasiUmum
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data informasi umum id ' . $id . ' tidak ditemukan!',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function storeInformasiUmumData(Request $request)
    {
        $rules = [
            'tipe_informasi_umum' => 'required',
            'nama_paket' => 'required',
            'nama_ppk' => 'required',
            'jabatan_ppk' => 'required',
        ];
        if ($request->tipe_informasi_umum == 'manual') {
            $rules = array_merge($rules, [
                'nama_balai' => 'required',
                //'tipologi' => 'required',
            ]);
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validasi gagal!',
                'data' => []
            ],404);
        }

        // $checkNamaPaket = $this->informasiUmumService->checkNamaPaket($request->nama_paket);
        // if ($checkNamaPaket) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'paket ' . $request->nama_paket . ' sudah / sedang diproses!',
        //         'data' => []
        //     ]);
        // }

        try {
            $saveInformasiUmum = $this->informasiUmumService->saveInformasiUmum($request);
            if (!$saveInformasiUmum) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal menyimpan data!',
                    'data' => []
                ]);
            }
            //change status
            $this->perencanaanDataService->changeStatusPerencanaanData(config('constants.STATUS_PERENCANAAN'), $saveInformasiUmum['id']);

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
                'data' => $saveInformasiUmum
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan pengguna',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getInformasiUmumByPerencanaanId($id)
    {
        try {
            $perencanaanData = $this->informasiUmumService->getInformasiUmumByPerencanaanId($id);
            if (!$perencanaanData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mendapatkan data dengan id ' . $id,
                    'data' => []
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil didapat',
                'data' => $perencanaanData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan pengguna',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function storeIdentifikasiKebutuhan(Request $request)
    {

        try {
            $identifikasiKebutuhanId = $request->informasi_umum_id;
            $materialResult = [];
            foreach ($request->material as $material) {
                $materialResult[] = $this->IdentifikasiKebutuhanService->storeMaterial($material, $identifikasiKebutuhanId);
            }

            $peralatanResult = [];
            foreach ($request->peralatan as $peralatan) {
                $peralatanResult[] = $this->IdentifikasiKebutuhanService->storePeralatan($peralatan, $identifikasiKebutuhanId);
            }

            $tenagaKerjaResult = [];
            foreach ($request->tenaga_kerja as $tenagaKerja) {
                $tenagaKerjaResult[] = $this->IdentifikasiKebutuhanService->storeTenagaKerja($tenagaKerja, $identifikasiKebutuhanId);
            }

            //update to perencanaan_data table
            $this->perencanaanDataService->updatePerencanaanData($identifikasiKebutuhanId, 'identifikasi_kebutuhan', $identifikasiKebutuhanId);
            $this->perencanaanDataService->changeStatusPerencanaanData(config('constants.STATUS_PERENCANAAN'), $identifikasiKebutuhanId);

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan!',
                'data' => [
                    'material' => $materialResult,
                    'peralatan' => $peralatanResult,
                    'tenaga_kerja' => $tenagaKerjaResult,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data!',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getAllDataVendor($identifikasiKebutuhanId)
    {
        $dataVendor = $this->shortlistVendorService->getDataVendor($identifikasiKebutuhanId);
        if ($dataVendor) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil didapat!',
                'data' => $dataVendor
            ],200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Data tidak dapat ditemukan!',
            'data' => []
        ],404);
    }

    public function selectDataVendor(Request $request)
    {
        $rules = [
            'shortlist_vendor' => 'required|array',
            'shortlist_vendor.*.data_vendor_id' => 'required',
            'shortlist_vendor.*.nama_vendor' => 'required',
            'shortlist_vendor.*.pemilik_vendor' => 'required',
            'shortlist_vendor.*.alamat' => 'required',
            'shortlist_vendor.*.kontak' => 'required',
            'shortlist_vendor.*.sumber_daya' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed!',
                'errors' => $validator->errors()
            ]);
        }

        DB::beginTransaction();
        try {
            $shortlistVendorId = $request->identifikasi_kebutuhan_id;
            $dataShortlistvendor = [];
            foreach ($request->shortlist_vendor as $shortlistVendor) {
                // * shortListVendorId = identifikasi_kebutuhan_id from table "informasi_umum";
                $dataShortlistvendor[] = $this->shortlistVendorService->storeShortlistVendor($shortlistVendor,  $shortlistVendorId);
            }

            $this->perencanaanDataService->updatePerencanaanData($shortlistVendorId, 'shortlist_vendor', $shortlistVendorId);
            $this->perencanaanDataService->changeStatusPerencanaanData(config('constants.STATUS_PERENCANAAN'), $shortlistVendorId);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan!',
                'shortlist_vendor_id' => $shortlistVendorId,
                'data' => [
                    'shortlist_vendor' => $dataShortlistvendor,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data!',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function informasiUmumResult(Request $request)
    {
        $getInformasiUmum = $this->perencanaanDataService->listAllPerencanaanData($request);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil didapat!',
            'data' => $getInformasiUmum,
        ]);
    }

    public function identifikasiKebutuhanResult(Request $request)
    {
        $getMaterial = $this->IdentifikasiKebutuhanService->getIdentifikasiKebutuhanByPerencanaanId('material', $request);
        $getPeralatan = $this->IdentifikasiKebutuhanService->getIdentifikasiKebutuhanByPerencanaanId('peralatan', $request);
        $getTenagaKerja = $this->IdentifikasiKebutuhanService->getIdentifikasiKebutuhanByPerencanaanId('tenaga_kerja', $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil didapat!',
            'data' => [
                'material' => $getMaterial,
                'peralatan' => $getPeralatan,
                'tenaga_kerja' => $getTenagaKerja,
            ],
        ]);
    }

    public function shortlistVendorResult(Request $request)
    {
        $getShortlistVendor = $this->shortlistVendorService->getShortlistVendorResult($request);

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil didapat!',
            'data' => $getShortlistVendor,
        ]);
    }

    public function perencanaanDataResult(Request $request)
    {
        $id = $request->query('id');

        $data = $this->perencanaanDataService->listAllPerencanaanData($id);

        if (!isset($data)) {
            return response()->json([
                'status' => 'error',
                'message' => 'data tidak ditemukan!',
                'data' => []
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil didapat!',
            'data' => $data
        ]);
    }

    public function adjustShortlistVendor(Request $request)
    {
        $rules = [
            'id_vendor' => 'required',
            'shortlist_vendor_id' => 'required',

        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed!',
                'errors' => $validator->errors()
            ]);
        }

        try {
            $shortlistVendorId = $request['shortlist_vendor_id'];
            $vendorId = $request['id_vendor'];
            $material = [];
            $peralatan = [];
            $tenagaKerja = [];

            if (count($request['material'])) {
                foreach ($request['material'] as $item) {
                    $material[] = $item['id'];
                }
            }

            if (count($request['peralatan'])) {
                foreach ($request['peralatan'] as $item) {
                    $peralatan[] = $item['id'];
                }
            }

            if (count($request['tenaga_kerja'])) {
                foreach ($request['tenaga_kerja'] as $item) {
                    $tenagaKerja[] = $item['id'];
                }
            }

            $saveData = $this->shortlistVendorService->saveKuisionerPdfData($vendorId, $shortlistVendorId, $material, $peralatan, $tenagaKerja);
            if (count($saveData)) {
                $generatePdf = $this->generatePdfService->generatePdfMaterial($saveData);
                $savePdf = $this->shortlistVendorService->saveUrlPdf($vendorId, $shortlistVendorId, $generatePdf);
                if (isset($saveData)) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Data berhasil didapat!',
                        'data' => $savePdf,
                    ]);
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data!',
                'data' => []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data!',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getShortlistVendorSumberDaya(Request $request)
    {
        $idInformasiUmum = $request->query('informasi_umum_id');
        $idShortlistVendor = $request->query('shortlist_vendor_id');

        if (!$idInformasiUmum || !$idShortlistVendor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing required parameters',
                'data' => null
            ], 400);
        }

        $queryData = $this->shortlistVendorService->getIdentifikasiByShortlist($idShortlistVendor, $idInformasiUmum);

        // Check if queryData is null
        if (is_null($queryData)) {
            return response()->json([
                'status' => 'success',
                'message' => 'No data found',
                'data' => []
            ],404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil didapat!',
            'data' => $queryData
        ],200);
    }

    public function getIdentifikasiKebutuhanStored($informasiUmumId)
    {
        $perencanaanData = $this->perencanaanDataService->listAllPerencanaanData($informasiUmumId);
        if (!empty($perencanaanData)) {
            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data' => [
                    'material' => $perencanaanData['material'],
                    'peralatan' => $perencanaanData['peralatan'],
                    'tenaga_kerja' => $perencanaanData['tenagaKerja'],
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_GET'),
                'data' => []
            ]);
        }
    }

    public function changeStatusPerencanaan($informasiUmumId)
    {
        try {
            $changeStatus = $this->perencanaanDataService->changeStatusPerencanaanData(config('constants.STATUS_PENGUMPULAN'), $informasiUmumId);

            if ($changeStatus) {
                return response()->json([
                    'status' => 'success',
                    'message' => config('constants.SUCCESS_MESSAGE_SAVE'),
                    'data' => $changeStatus
                ]);
            }

            return response()->json([
                'status' => 'gagal',
                'message' => "Data tidak ditemukan"
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_SAVE'),
                'error' => $e->getMessage()
            ]);
        }
    }

    public function tableListPerencanaan()
    {
        $status = [
            config('constants.STATUS_PERENCANAAN'),
            config('constants.STATUS_PENYEBARLUASAN_DATA'),

        ];
        $list = $this->perencanaanDataService->tableListPerencanaanData($status);
        if (isset($list)) {
            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data' => $list
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_GET'),
                'data' => []
            ],404);
        }
    }
}
