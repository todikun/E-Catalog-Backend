<?php

namespace App\Http\Controllers;

use App\Models\DataVendor;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\SumberDayaVendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{

    public function getVendor($id)
    {
        $vendor = DataVendor::with('sumber_daya_vendor:id,data_vendor_id,jenis,nama,spesifikasi')->find($id);

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
        foreach (['jenis_vendor_id', 'kategori_vendor_id'] as $key) {
            if (is_string($request->$key)) {
                $request->merge([$key => json_decode($request->$key, true)]);
            }
        }

        $rules = [
            'nama_vendor'         => 'required|string|max:255',
            'jenis_vendor_id'     => 'required|array|min:1',
            'jenis_vendor_id.*'   => 'required|in:1,2,3',
            'kategori_vendor_id'  => 'required|array|min:1',
            'kategori_vendor_id.*'=> 'integer',
            'alamat'              => 'required|string|max:500',
            'no_telepon'          => 'nullable|string|max:25',
            'no_hp'               => 'nullable|string|max:25',
            'nama_pic'            => 'required|string|max:255',
            'provinsi_id'         => 'required|integer',
            'kota_id'             => 'required|integer',
            'koordinat'           => 'required|string|max:255',
            'logo_url'            => 'nullable|file|mimes:jpg,jpeg,png,webp,svg,pdf|max:2048',
            'dok_pendukung_url'   => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,zip,doc,docx|max:5120',
            'sumber_daya'                         => 'required|array',
            'sumber_daya.material'                => 'nullable|array',
            'sumber_daya.material.*.nama'         => 'nullable|string|max:255',
            'sumber_daya.material.*.spesifikasi'  => 'nullable|string|max:500',
            'sumber_daya.peralatan'               => 'nullable|array',
            'sumber_daya.peralatan.*.nama'        => 'nullable|string|max:255',
            'sumber_daya.peralatan.*.spesifikasi' => 'nullable|string|max:500',
            'sumber_daya.tenaga_kerja'               => 'nullable|array',
            'sumber_daya.tenaga_kerja.*.nama'        => 'nullable|string|max:255',
            'sumber_daya.tenaga_kerja.*.spesifikasi' => 'nullable|string|max:500',
        ];

        $messages = [
            'jenis_vendor_id.*.in' => 'jenis_vendor_id must be 1 (material), 2 (peralatan), or 3 (tenaga_kerja).',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        $validator->after(function ($v) use ($request) {
            $count = collect($request->input('sumber_daya', []))
                ->flatMap(fn ($items) => collect($items))
                ->filter(fn ($row) => filled($row['nama'] ?? null) || filled($row['spesifikasi'] ?? null))
                ->count();

                if ($count === 0) {
                    $v->errors()->add('sumber_daya', 'At least one sumber daya must be provided.');
                }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed!',
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            if ($request->hasFile('logo_url') && $request->hasFile('dok_pendukung_url')) {
                $filePathLogo = $request->file('logo_url')->store('logo_vendor', 'public');
                $filePathDokPendukung = $request->file('dok_pendukung_url')->store('doc_pendukung_vendor');
            }

            $vendor = new DataVendor();
            $vendor->nama_vendor = $request->nama_vendor;
            $vendor->jenis_vendor_id = $request->jenis_vendor_id;
            $vendor->kategori_vendor_id = $request->kategori_vendor_id;
            $vendor->alamat = $request->alamat;
            $vendor->no_telepon = $request->no_telepon ?? "-";
            $vendor->no_hp = $request->no_hp ?? "-";
            $vendor->nama_pic = $request->nama_pic;
            $vendor->provinsi_id = $request->provinsi_id;
            $vendor->kota_id = $request->kota_id;
            $vendor->koordinat = $request->koordinat;
            $vendor->logo_url = isset($filePathLogo) ? $filePathLogo : "-";
            $vendor->dok_pendukung_url = isset($filePathDokPendukung) ? $filePathDokPendukung : "-";
            $vendor->sumber_daya = implode(';', Arr::flatten(Arr::pluck($request->sumber_daya, '*.nama')));;
            $vendor->save();

            $datas = [];
            foreach ($request->sumber_daya as $key => $types) {
                foreach($types as $value) {
                    if (empty($value['nama']) && empty($value['spesifikasi'])) {
                        continue;
                    }
                    $datas[] = [
                        'data_vendor_id' => $vendor->id,
                        'jenis' => $key,
                        'nama' => $value['nama'],
                        'spesifikasi' => $value['spesifikasi']
                    ];
                }
            }

            if (!empty($datas)) {
                SumberDayaVendor::insert($datas);
            }

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
            ],500);
        }
    }
}
