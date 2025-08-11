<?php

namespace App\Http\Controllers;

use App\Models\SatuanBalaiKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BalaiKerjaController extends Controller
{
    public function getAllSatuanBalaiKerja()
    {
        $data = SatuanBalaiKerja::select('id', 'nama', 'unor_id')->get();

        if (count($data)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil didapat',
                'data' => $data
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gagal mendapatkan data',
            'data' => []
        ]);
    }

    public function storeSatuanBalaiKerja(Request $request)
    {
        $rules = [
            'nama_balai_kerja' => 'required',
            'unor_id' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed!',
                'errors' => $validator->errors()
            ]);
        }

        $check = SatuanBalaiKerja::where('nama', $request['nama_balai_kerja'])
            ->where('unor_id', $request['unor_id'])->first();

        if ($check) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data dengan balai: ' . $request['nama_balai_kerja'] . ' dan unor: ' . $request['unor_id'] . ' sudah ada!',
                'data' => []
            ]);
        }

        try {
            $satuanBalaiKerja = new SatuanBalaiKerja();
            $satuanBalaiKerja->nama = $request['nama_balai_kerja'];
            $satuanBalaiKerja->unor_id = $request['unor_id'];
            $data = $satuanBalaiKerja->save();

            if ($data) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data berhasil disimpan',
                    'data' => $satuanBalaiKerja
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
