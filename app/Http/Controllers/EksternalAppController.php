<?php

namespace App\Http\Controllers;

use App\Services\EksternalService;
use Illuminate\Http\Request;

class EksternalAppController extends Controller
{
    protected $eksternalService;
    public function __construct(EksternalService $eksternalService)
    {
        $this->eksternalService = $eksternalService;
    }

    public function getAllDataMaterial()
    {
        $getMaterial = $this->eksternalService->getMaterialData();
        return response()->json([
            'status' => 'success',
            'message' => 'data berhasil didapat',
            'data' => $getMaterial
        ]);
    }

    public function getAllDataPeralatan()
    {
        $getPeralatan = $this->eksternalService->getPeralatanData();
        return response()->json([
            'status' => 'success',
            'message' => 'data berhasil didapat',
            'data' => $getPeralatan
        ]);
    }

    public function getAllTenagaKerja()
    {
        $getTenagaKerja = $this->eksternalService->getTenagaKerjaData();
        return response()->json([
            'status' => 'success',
            'message' => 'data berhasil didapat',
            'data' => $getTenagaKerja
        ]);
    }
}
