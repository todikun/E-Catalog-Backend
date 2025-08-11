<?php

namespace App\Http\Controllers;

use App\Models\Provinces;
use Illuminate\Http\Request;

class ProvinceAndCitiesController extends Controller
{
    public function __construct() 
    {
        
    }

    public function getProvinceAndCities()
    {
        $provinces = Provinces::with('cities')->get();

        $formatData = $provinces->map(function ($provinces){
            return [
                'id_province' => $provinces->kode_provinsi,
                'province_name' => $provinces->nama_provinsi,
                'cities' => $provinces->cities->map(function ($city){
                    return [
                        'cities_id' => $city->kode_kota,
                        'cities_name' => $city->nama_kota,
                    ];
                })
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil didapat',
            'data' => $formatData,
        ]);
    }
}
