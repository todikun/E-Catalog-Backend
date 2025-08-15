<?php

namespace App\Http\Controllers;

use App\Models\PerencanaanData;
use App\Services\GeneratePdfServiceChange;
use Exception;
use Illuminate\Http\Request;

class KuisionerController extends Controller
{

    /**
     * Metode untuk membuat dan menggabungkan PDF kuisioner untuk semua vendor
     * dalam satu perencanaan berdasarkan filter string yang diberikan.
     *
     * @param Request $request
     * @param GeneratePdfService $pdfService
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateKuisionerPdf(Request $request, GeneratePdfServiceChange $pdfService)
    {
        try {
            // Validasi data input. Sekarang menerima string.
            $request->validate([
                'perencanaan_id' => 'required|integer',
                'material' => 'sometimes|string',
                'peralatan' => 'sometimes|string',
                'tenaga_kerja' => 'sometimes|string',
            ]);

            $perencanaanId = $request->input('perencanaan_id');
            
            // Mengambil filter sebagai string dari request.
            $materialFilter = $request->input('material', '');
            $peralatanFilter = $request->input('peralatan', '');
            $tenagaKerjaFilter = $request->input('tenaga_kerja', '');

            // Ambil data perencanaan dengan eager loading relasi yang dibutuhkan
            $perencanaan = PerencanaanData::with('shortlistVendor.data_vendor')->find($perencanaanId);

            if (!$perencanaan) {
                return response()->json(['message' => 'Data perencanaan tidak ditemukan.'], 404);
            }

            // Panggil metode generateAllVendorsPdf dari service dengan filter string
            $urls = $pdfService->generateAllVendorsPdf(
                $perencanaan, 
                $materialFilter, 
                $peralatanFilter, 
                $tenagaKerjaFilter
            );

            return response()->json([
                'message' => 'PDF kuisioner berhasil dibuat untuk vendor yang relevan.',
                'urls' => $urls
            ]);

        } catch (Exception $e) {
            // Tangani exception jika terjadi kesalahan
            return response()->json([
                'message' => 'Terjadi kesalahan saat membuat PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}
