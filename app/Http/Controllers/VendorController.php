<?php

namespace App\Http\Controllers;

use App\Models\DataVendor;
use App\Models\SumberDayaVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{

    public function getVendor($id)
    {
        $vendor = DataVendor::find($id);

        if ($vendor) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil didapat!',
                'data' => $vendor
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mendapatkan data!',
                'data' => []
            ]);
        }
    }

    public function getVendorAll()
    {
        $vendor = DataVendor::select('id', 'nama_vendor', 'alamat', 'no_telepon', 'nama_pic')->get();

        if ($vendor) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil didapat!',
                'data' => $vendor
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mendapatkan data!',
                'data' => []
            ]);
        }
    }

    public function inputVendor(Request $request)
    {
        DB::beginTransaction();
        try {

            if ($request->hasFile('logo_url') && $request->hasFile('dok_pendukung_url')) {
                $filePathLogo = $request->file('logo_url')->store('logo_vendor', 'public');
                $filePathDokPendukung = $request->file('dok_pendukung_url')->store('doc_pendukung_vendor');
            }

            $vendor = new DataVendor();
            $vendor->nama_vendor = $request->nama_vendor;
            $vendor->jenis_vendor_id = json_decode($request->jenis_vendor_id, true);
            $vendor->kategori_vendor_id = json_decode($request->kategori_vendor_id, true);
            $vendor->alamat = $request->alamat;
            $vendor->no_telepon = $request->no_telepon;
            $vendor->no_hp = $request->no_hp;
            $vendor->nama_pic = $request->nama_pic;
            $vendor->provinsi_id = $request->provinsi_id;
            $vendor->kota_id = $request->kota_id;
            $vendor->koordinat = $request->koordinat;
            $vendor->logo_url = "-";
            $vendor->dok_pendukung_url = "-";
            $vendor->logo_url = ($filePathLogo) ? $filePathLogo : "-";
            $vendor->dok_pendukung_url = ($filePathDokPendukung) ? $filePathDokPendukung : "-";
            $vendor->sumber_daya = $request->sumber_daya;
            $vendor->save();

            $datas = [];
            foreach ($request->sumber_daya as $key => $types) {
                foreach($types as $value) {
                    $datas[] = [
                        'data_vendor_id' => $vendor->id,
                        'jenis' => $key,
                        'nama' => $value['nama'],
                        'spesifikasi' => $value['spesifikasi']
                    ];
                }
            }
            SumberDayaVendor::insert($datas);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Vendor berhasil disimpan',
                'data' => $vendor
            ],201);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data vendor',
                'error' => $th->getMessage()
            ],400);
        }
    }
}
