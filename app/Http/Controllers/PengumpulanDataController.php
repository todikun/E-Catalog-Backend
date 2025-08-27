<?php

namespace App\Http\Controllers;

use App\Models\Pengawas;
use App\Models\PengolahData;
use App\Models\PetugasLapangan;
use App\Services\PengumpulanDataService;
use App\Services\PerencanaanDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PengumpulanDataController extends Controller
{
    protected $pengumpulanDataService;
    protected $perencanaanDataService;

    public function __construct(
        PengumpulanDataService $pengumpulanDataService,
        PerencanaanDataService $perencanaanDataService
    ) {
        $this->pengumpulanDataService = $pengumpulanDataService;
        $this->perencanaanDataService = $perencanaanDataService;
    }

    public function storeTeamTeknisBalai(Request $request)
    {
        $rules = [
            'nama_team' => 'required',
            'ketua_team' => 'required',
            'sekretaris_team' => 'required',
            'anggota' => 'required',
            'sk_penugasan' => 'required|file|mimes:pdf,doc,docx|max:2048'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validasi gagal!',
                'data' => []
            ]);
        }

        try {
            if ($request->hasFile('sk_penugasan')) {
                $filePath = $request->file('sk_penugasan')->store('sk_penugasan');
            }

            $ketua = (int) $request->input('ketua_team');
            $sekretaris = (int) $request->input('sekretaris_team');
            $arrayPetinggi = array_merge([$ketua], [$sekretaris]);

            $arrayAnggota = array_merge(explode(',', $request->input('anggota')));
            $arrayAnggota = array_map('intval', $arrayAnggota);

            $duplicates = array_intersect($arrayPetinggi, $arrayAnggota);

            if (!empty($duplicates)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ketua atau Sekretaris tidak boleh rangkap menjadi anggota',
                    'error' => "Duplicate Data"
                ], 400);
            }

            $data = [
                'nama_team' => $request->input('nama_team'),
                'ketua_team' => $request->input('ketua_team'),
                'sekretaris_team' => $request->input('sekretaris_team'),
                'anggota' => $arrayAnggota,
                'sk_penugasan' => $filePath
            ];

            $saveData = $this->pengumpulanDataService->storeTeamPengumpulanData($data);
            if ($saveData) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data berhasil disimpan',
                    'data' => $saveData
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

    public function getTeamPengumpulanData()
    {
        $data = $this->pengumpulanDataService->getAllTeamPengumpulanData();
        if ($data) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil didapat',
                'data' => $data
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal Mendapatkan Data',
                'data' => []
            ]);
        }
    }

    public function assignTeamPengumpulanData(Request $request)
    {
        $rules = [
            'id_pengumpulan_data' => 'required',
            'id_team_pengumpulan_data' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validasi gagal!',
                'error' => $validator->errors()
            ]);
        }

        try {
            $assignTeam = $this->pengumpulanDataService->assignTeamPengumpulanData($request);
            if ($assignTeam) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data berhasil disimpan',
                    'data' => $assignTeam
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

    public function listPengumpulanData()
    {
        $status = [
            config('constants.STATUS_PENGUMPULAN'),
            config('constants.STATUS_PENGISIAN_PETUGAS'),
            config('constants.STATUS_VERIFIKASI_PENGAWAS'),
            config('constants.STATUS_ENTRI_DATA'),
            config('constants.STATUS_PEMERIKSAAN'),
        ];
        $data = $this->perencanaanDataService->tableListPerencanaanData($status);
        if ($data) {
            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data' => $data
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_GET'),
                'data' => []
            ]);
        }
    }

    // TODO: Change to Google Cloud Storage Bucket for Storing the sk_penugasan
    public function storePengawas(Request $request)
    {
        $rules = [
            'sk_penugasan' => 'required|file|mimes:pdf,doc,docx|max:2048',
            'user_id' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validasi gagal!',
                'error' => $validator->errors()
            ]);
        }

        $array = explode(',', $request['user_id']);
        try {
            $data = [];

            if ($request->hasFile('sk_penugasan')) {
                // Change it using Google Cloud Storage Bucket
                $filePath = $request->file('sk_penugasan')->store('public/sk_penugasan');
            }

            foreach (collect($array) as $value) {
                $data[] = [
                    'user_id' => $value,
                    'sk_penugasan' => $filePath
                ];
            }

            $save = Pengawas::insert($data);
            if ($save) {
                return response()->json([
                    'status' => 'success',
                    'message' => config('constants.SUCCESS_MESSAGE_SAVE'),
                    'data' => $data
                ]);
            }
        } catch (\Exception $th) {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_SAVE'),
                'error' => $th->getMessage()
            ]);
        }
    }

    public function listUser(Request $request)
    {
        $roles = $this->pengumpulanDataService->getListRoles($request['role']);

        if (empty($roles)) {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_GET'),
                'error' => 'Role tidak terdefinisi'
            ], 400);
        }

        $getData = $this->pengumpulanDataService->listUserPengumpulan($roles);
        if ($getData) {
            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data' => $getData
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => config('constants.ERROR_MESSAGE_GET'),
            'data' => []
        ], 400);
    }

    public function storePetugasLapangan(Request $request)
    {
        $rules = [
            'sk_penugasan' => 'required|file|mimes:pdf,doc,docx|max:2048',
            'user_id' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validasi gagal!',
                'error' => $validator->errors()
            ]);
        }

        $array = explode(',', $request['user_id']);
        try {
            $data = [];

            if ($request->hasFile('sk_penugasan')) {
                $filePath = $request->file('sk_penugasan')->store('sk_penugasan');
            }

            foreach (collect($array) as $value) {
                $data[] = [
                    'user_id' => $value,
                    'sk_penugasan' => $filePath
                ];
            }

            $save = PetugasLapangan::insert($data);
            if ($save) {
                return response()->json([
                    'status' => 'success',
                    'message' => config('constants.SUCCESS_MESSAGE_SAVE'),
                    'data' => $data
                ]);
            }
        } catch (\Exception $th) {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_SAVE'),
                'error' => $th->getMessage()
            ]);
        }
    }

    public function storepengolahData(Request $request)
    {
        $rules = [
            'sk_penugasan' => 'required|file|mimes:pdf,doc,docx|max:2048',
            'user_id' => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validasi gagal!',
                'error' => $validator->errors()
            ]);
        }

        $array = explode(',', $request['user_id']);
        try {
            $data = [];

            if ($request->hasFile('sk_penugasan')) {
                $filePath = $request->file('sk_penugasan')->store('sk_penugasan');
            }

            foreach (collect($array) as $value) {
                $data[] = [
                    'user_id' => $value,
                    'sk_penugasan' => $filePath
                ];
            }

            $save = PengolahData::insert($data);
            if ($save) {
                return response()->json([
                    'status' => 'success',
                    'message' => config('constants.SUCCESS_MESSAGE_SAVE'),
                    'data' => $data
                ]);
            }
        } catch (\Exception $th) {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_SAVE'),
                'error' => $th->getMessage()
            ]);
        }
    }

    public function listPengawas()
    {
        $data = $this->pengumpulanDataService->listPenugasan('pengawas');

        if ($data) {
            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data' => $data
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_GET'),
                'data' => []
            ]);
        }
    }

    public function listPengolahData()
    {
        $data = $this->pengumpulanDataService->listPenugasan('pengolah data');

        if ($data) {
            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data' => $data
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_GET'),
                'data' => []
            ]);
        }
    }

    public function listPetugasLapangan()
    {
        $data = $this->pengumpulanDataService->listPenugasan('petugas lapangan');

        if ($data) {
            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data' => $data
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_GET'),
                'data' => []
            ]);
        }
    }

    public function assignPengawas(Request $request)
    {
        $rules = [
            'id_user' => 'required',
            'pengumpulan_data_id' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validasi gagal!',
                'error' => $validator->errors()
            ]);
        }

        try {
            $assign = $this->pengumpulanDataService->assignPenugasan('pengawas', $request->id_user, $request->pengumpulan_data_id);

            if (empty($assign)) {
                return response()->json([
                    'status' => 'gagal',
                    'message' => config('constants.ERROR_MESSAGE_SAVE'),
                    'data' => $assign
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_SAVE'),
                'data' => $assign
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_SAVE'),
                'error' => $th->getMessage()
            ]);
        }
    }

    public function assignPengolahData(Request $request)
    {
        $rules = [
            'id_user' => 'required',
            'pengolah_data_id' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validasi gagal!',
                'error' => $validator->errors()
            ]);
        }

        try {
            $assign = $this->pengumpulanDataService->assignPenugasan('pengolah data', $request->id_user, $request->pengolah_data_id);
            if ($assign) {
                return response()->json([
                    'status' => 'success',
                    'message' => config('constants.SUCCESS_MESSAGE_SAVE'),
                    'data' => $assign
                ]);
            }
        } catch (\Exception $th) {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_SAVE'),
                'error' => $th->getMessage()
            ]);
        }
    }

    public function assignPetugasLapangan(Request $request)
    {
        $rules = [
            'id_user' => 'required',
            'petugas_lapangan_id' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'validasi gagal!',
                'error' => $validator->errors()
            ]);
        }

        try {
            $assign = $this->pengumpulanDataService->assignPenugasan('petugas lapangan', $request->id_user, $request->petugas_lapangan_id);
            if ($assign) {
                return response()->json([
                    'status' => 'success',
                    'message' => config('constants.SUCCESS_MESSAGE_SAVE'),
                    'data' => $assign
                ]);
            }
        } catch (\Exception $th) {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_SAVE'),
                'error' => $th->getMessage()
            ]);
        }
    }

    public function tableListPengumpulanData()
    {
        $list = $this->perencanaanDataService->tableListPerencanaanData(config('constants.STATUS_PENGUMPULAN'));
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
            ]);
        }
    }

    public function viewPdfKuisioner($id)
    {
        $kuisioner = $this->pengumpulanDataService->showKuisioner($id);
        if (isset($kuisioner)) {
            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data' => $kuisioner
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => config('constants.ERROR_MESSAGE_GET'),
                'data' => []
            ]);
        }
    }

    public function listVendorByPaket($id)
    {
        $list = $this->pengumpulanDataService->listVendorByPerencanaanId($id);
        if ($list->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data' => $list
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => config('constants.ERROR_MESSAGE_GET'),
            'data' => []
        ], 404);
    }

    public function getEntriData($id)
    {
        // ID from the shortlist_vendor
        $data = $this->pengumpulanDataService->getEntriData($id);
        if ($data) {
            return response()->json([
                'status' => 'success',
                'message' => config('constants.SUCCESS_MESSAGE_GET'),
                'data' => $data
            ]);
        }
        
        return response()->json([
            'status' => 'error',
            'message' => config('constants.ERROR_MESSAGE_GET'),
            'data' => []
        ], 404);
    }

    public function entriDataSave(Request $request)
    {
        // $rules = [
        //     'user_id_petugas_lapangan' => 'required',
        //     'user_id_pengawas' => 'required',
        //     'nama_pemberi_informasi' => 'required',
        //     'data_vendor_id' => 'required',
        //     'identifikasi_kebutuhan_id' => 'required',
        //     'tanggal_survei' => 'required',
        //     'tanggal_pengawasan' => 'required',
        // ];
        // $validator = Validator::make($request->all(), $rules);
        // if ($validator->fails()) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'validasi gagal!',
        //         'error' => $validator->errors()
        //     ]);
        // }

        try {
            $materialResult = [];
            foreach ($request->material as $material) {
                $materialResult[] = $this->pengumpulanDataService->updateIdentifikasi('material', $material['id'], $material);
            }

            $peralatanResult = [];
            foreach ($request->peralatan as $peralatan) {
                $peralatanResult[] = $this->pengumpulanDataService->updateIdentifikasi('peralatan', $peralatan['id'], $peralatan);
            }

            $tenagaKerjaResult = [];
            foreach ($request->tenaga_kerja as $tenaga_kerja) {
                $tenagaKerjaResult[] = $this->pengumpulanDataService->updateIdentifikasi('tenaga_kerja', $tenaga_kerja['id'], $tenaga_kerja);
            }

            $updateShortlist = $this->pengumpulanDataService->updateShortlistVendor($request->identifikasi_kebutuhan_id, $request->data_vendor_id, $request);
            $response = [
                'keterangan' => $updateShortlist,
                'material' => $materialResult,
                'peralatan' => $peralatanResult,
                'tenaga_kerja' => $tenagaKerjaResult,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan!',
                'data' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data!',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function verifikasiPengawas(Request $request)
    {
        $rules = [
            'identifikasi_kebutuhan_id' => 'required',
            'data_vendor_id' => 'required',
            'berita_acara' => 'required|file|mimes:pdf,doc,docx|max:2048'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
                'data' => []
            ]);
        }

        try {
            if ($request->hasFile('berita_acara')) {
                $filePath = $request->file('berita_acara')->store('berita_acara');
            }

            $this->pengumpulanDataService->updateDataVerifikasiPengawas($request);
            $this->pengumpulanDataService->pemeriksaanDataList($request);

            $data = $this->pengumpulanDataService->changeStatusVerification($request['identifikasi_kebutuhan_id'], $filePath);
            if (isset($data)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data berhasil disimpan',
                    'data' => $data
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
