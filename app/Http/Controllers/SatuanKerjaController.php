<?php

namespace App\Http\Controllers;

use App\Models\SatuanKerja;
use Illuminate\Http\Request;

class SatuanKerjaController extends Controller
{
    public function getAllSatuanKerja()
    {
        $data = SatuanKerja::Select('id', 'nama')->get();
        if (isset($data)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil didapat',
                'data' => $data,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mendapatkan data!',
            'data' => []
        ]);
    }

    public function storeSatuanKerja(Request $request)
    {
        try {
            $dataArray = [
                'nama' => $request['nama']
            ];

            $dataStore = SatuanKerja::updateOrCreate(
                [
                    'nama' => $request['nama']
                ],
                $dataArray
            );

            if ($dataStore) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data berhasil disimpan',
                    'data' => $dataStore,
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
