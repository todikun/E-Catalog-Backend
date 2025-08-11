<?php

namespace App\Services;

use Codedge\Fpdf\Fpdf\Fpdf;
use App\Models\DataVendor;
use App\Models\KategoriVendor;
use App\Models\Material;
use App\Models\Peralatan;
use App\Models\ShortlistVendor;
use App\Models\TenagaKerja;
use setasign\Fpdi\Fpdi;

class GeneratePdfService
{
    public function saveUrlPdf($dataVendorId, $url)
    {
        return ShortlistVendor::updateOrCreate(['id' => $dataVendorId], ['url_kuisioner' => $url]);
    }

    public function generatePdfMaterial($data)
    {
        $pdfFiles = [];
        $dataVendor = $this->getVendorById($data['vendor_id']);
        $kategoriVendor = KategoriVendor::whereIn('id', $dataVendor['kategori_vendor_id'])
            ->select('nama_kategori_vendor as name')
            ->get();
        $stringKategoriVendor = $kategoriVendor->pluck('name')->implode(', ');
        $dataVendor['string_kategori_vendor'] = $stringKategoriVendor;

        if (!isset($dataVendor)) {
            throw new \Exception('data not found');
        }

        if ($data['material_id']) {
            $pdfMaterial = $this->pdfMaterial($dataVendor, json_decode($data['material_id']));
            $pdfFiles[] = $pdfMaterial;
        }
        if ($data['peralatan_id']) {
            $pdfPeralatan = $this->pdfPeralatan($dataVendor, json_decode($data['peralatan_id']));
            $pdfFiles[] = $pdfPeralatan;
        }
        if ($data['tenaga_kerja_id']) {
            $pdfTenagaKerja = $this->pdfTenagaKerja($dataVendor, json_decode($data['tenaga_kerja_id']));
            $pdfFiles[] = $pdfTenagaKerja;
        }
        $resultPdf = $this->mergePdf($pdfFiles);


        return $resultPdf;
    }

    private function getVendorById($id)
    {
        return DataVendor::with(['provinces', 'cities', 'kategori_vendor'])
            ->find($id);
    }

    private function getIdentifikasi($id, $category)
    {
        if ($category == 'material') {
            $query = Material::select('id', 'nama_material', 'satuan', 'spesifikasi', 'merk')->find($id)->all();
        } elseif ($category == 'peralatan') {
            $query = Peralatan::select('id', 'nama_peralatan', 'satuan', 'spesifikasi', 'merk')->find($id)->all();
        } elseif ($category == 'tenaga_kerja') {
            $query = TenagaKerja::select('id', 'jenis_tenaga_kerja', 'satuan', 'kodefikasi')->find($id)->all();
        } else {
            return null;
        }

        return $query;
    }

    private function pdfMaterial($dataVendor, $id)
    {
        $pdfTempPath = [];

        $identifikasiKebutuhan = $this->getIdentifikasi($id, 'material');

        $templatePath = resource_path('views/pdf/template_material_natural.jpg');
        $templateIdentifikasiPath = resource_path('views/pdf/template_material_natural_identifikasi.jpg');

        if (!file_exists($templatePath) || !file_exists($templateIdentifikasiPath)) {
            throw new \Exception('Template not found');
        }

        $pdfInformasiUmum = $this->materialPdfInformasiUmum($templatePath, $dataVendor);
        $pdfIdentifikasi = $this->materialPdfIdentifikasi($templateIdentifikasiPath, $identifikasiKebutuhan);
        $catatanKuisoner = $this->catatankuisonerPdf();

        $pdfTempPath = array_merge($pdfInformasiUmum, $pdfIdentifikasi, $catatanKuisoner);

        return $pdfTempPath;
    }

    private function catatankuisonerPdf()
    {
        $pdf = new Fpdf();
        $pdf->AddPage('L');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Image(resource_path('views/pdf/catatan_kuisoner.jpg'), 0, 0, 297, 210);

        $tempFIlePath = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
        $pdf->Output('F', $tempFIlePath);
        $pdfFiles[] = $tempFIlePath;

        return $pdfFiles;
    }

    private function materialPdfIdentifikasi($templatePath, $data)
    {
        if (!is_array($data) || empty($data)) {
            throw new \Exception('Data is not an array or is empty.');
        }

        $pdfFiles = [];
        $count = 1;
        $Y = 60;
        $pdf = new Fpdf();
        $pdf->AddPage('L');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Image($templatePath, 0, 0, 297, 210);

        foreach ($data as $value) {

            //no
            $pdf->SetXY(17, $Y);
            $pdf->Cell(5, 8, $count, 0, 0, 'C');

            //nama
            $pdf->SetXY(24, $Y);
            $pdf->MultiCell(21, 4, $value['nama_material'], 0, 'L');

            //spesifikasi
            $pdf->SetXY(45, $Y);
            $pdf->MultiCell(22, 4, $value['spesifikasi'], 0, 'L');

            //satuan
            $pdf->SetXY(68, $Y);
            $pdf->MultiCell(10, 4, $value['satuan'], 0, 'L');

            //satuan
            $pdf->SetXY(82, $Y);
            $pdf->MultiCell(10, 4, $value['merk'], 0, 'L');

            $Y += 13;

            if ($count % 10 == 0) {
                $pdf->Output();

                $pdf->AddPage('L');
                $pdf->SetFont('Arial', 'B', 6);
                $pdf->Image($templatePath, 0, 0, 297, 210);
                $Y = 60;
            }

            $count++;
        }

        $tempFilePath = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
        $pdf->Output('F', $tempFilePath);
        $pdfFiles[] = $tempFilePath;

        return $pdfFiles;
    }

    private function mergePdf($pdfFiles)
    {
        $flattenedArray = array_merge(...$pdfFiles);
        $pdf = new Fpdi();
        foreach ($flattenedArray as $file) {
            $pageCount = $pdf->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $pdf->AddPage('L');
                $pdf->useTemplate($templateId);
            }
            unlink($file);
        }

        $randomFileName = uniqid('pdf_', true) . '.pdf';
        $savePath = public_path('kuisioner/' . $randomFileName);
        $pdf->Output('F', $savePath);
        $url = asset('kuisioner/' . $randomFileName);
        return $url;
    }

    private function peralatanPdfIdentifikasi($templatePath, $data)
    {
        if (!is_array($data) || empty($data)) {
            throw new \Exception('Data is not an array or is empty.');
        }

        $pdfFiles = [];
        $count = 1;
        $Y = 36;
        $pdf = new Fpdf();
        $pdf->AddPage('L');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Image($templatePath, 0, 0, 297, 210);

        foreach ($data as $value) {

            //no
            $pdf->SetXY(30, $Y);
            $pdf->Cell(5, 8, $count, 0, 0, 'C');

            //nama
            $pdf->SetXY(43, $Y);
            $pdf->MultiCell(37, 4, $value['nama_peralatan'], 0, 'L');

            //spesifikasi
            $pdf->SetXY(82, $Y);
            $pdf->MultiCell(35, 4, $value['spesifikasi'], 0, 'L');

            //satuan
            $pdf->SetXY(119, $Y);
            $pdf->MultiCell(18, 4, $value['satuan'], 1, 'L');

            $Y += 9.5;

            if ($count % 16 == 0) {
                $pdf->Output();

                $pdf->AddPage('L');
                $pdf->SetFont('Arial', 'B', 6);
                $pdf->Image($templatePath, 0, 0, 297, 210);
                $Y = 60;
            }

            $count++;
        }

        $tempFilePath = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
        $pdf->Output('F', $tempFilePath);
        $pdfFiles[] = $tempFilePath;

        return $pdfFiles;
    }

    private function materialPdfInformasiUmum($templatePath, $dataVendor)
    {
        $provinsi = $dataVendor->provinces->nama_provinsi;
        $kabupaten = $dataVendor->cities->nama_kota;
        $namaResponden = $dataVendor['nama_vendor'];
        $alamat = $dataVendor['alamat'];
        $geoTagging = $dataVendor['koordinat'];
        $telepon = $dataVendor['no_telepon'];
        $email = '-';
        $kategoriResponden = $dataVendor['string_kategori_vendor'];
        $idProvinsi = $dataVendor->cities->provinsi_id;
        $idKabupatenKota = $dataVendor->cities->kode_kota;

        $pdf = new Fpdf();
        $pdf->AddPage('L');

        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Image($templatePath, 0, 0, 297, 210);

        //provinsi
        $pdf->SetXY(83, 22);
        $pdf->Cell(40, 100, $provinsi);

        //id provinsi
        $pdf->SetXY(180, 68.5);
        $pdf->Cell(24, 5, $idProvinsi, 0, 0, 'L');

        //id kabupaten kota
        $pdf->SetXY(180, 73);
        $pdf->Cell(24, 5, $idKabupatenKota, 0, 0, 'L');

        //kota/kabupaten
        $pdf->SetXY(83, 27);
        $pdf->Cell(40, 100, $kabupaten);

        //nama responden
        $pdf->SetXY(83, 32);
        $pdf->Cell(40, 100, $namaResponden);

        //alamat responden
        $pdf->SetXY(83, 37);
        $pdf->Cell(40, 100, $alamat);

        //tagging responden
        $pdf->SetXY(153, 37);
        $pdf->Cell(40, 100, $geoTagging, 0, 0, 'L');

        //telepon responden
        $pdf->SetXY(83, 42);
        $pdf->Cell(40, 100, $telepon);

        //telepon responden
        $pdf->SetXY(153, 42);
        $pdf->Cell(40, 100, $email, 0, 0, 'L');

        //kategori responden
        $pdf->SetXY(83, 47);
        $pdf->Cell(40, 100, $kategoriResponden);

        $tempFIlePath = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
        $pdf->Output('F', $tempFIlePath);
        $pdfFiles[] = $tempFIlePath;

        return $pdfFiles;
    }

    private function pdfPeralatan($dataVendor, $id)
    {
        $pdfTempPath = [];

        $identifikasiKebutuhan = $this->getIdentifikasi($id, 'peralatan');

        $templatePath = resource_path('views/pdf/template_peralatan.jpg');
        $templateIdentifikasiPath = resource_path('views/pdf/template_peralatan_identifikasi.jpg');

        if (!file_exists($templatePath) || !file_exists($templateIdentifikasiPath)) {
            throw new \Exception('Template not found');
        }

        $pdfInformasiUmum = $this->pdfPeralatanInformasiUmum($templatePath, $dataVendor);
        $pdfIdentifikasi = $this->peralatanPdfIdentifikasi($templateIdentifikasiPath, $identifikasiKebutuhan);
        $catatanKuisoner = $this->catatankuisonerPdf();

        $pdfTempPath = array_merge($pdfInformasiUmum, $pdfIdentifikasi, $catatanKuisoner);

        return $pdfTempPath;
    }

    private function pdfTenagaKerja($dataVendor, $id)
    {
        $pdfTempPath = [];

        $identifikasiKebutuhan = $this->getIdentifikasi($id, 'tenaga_kerja');

        $templatePath = resource_path('views/pdf/template_tenaga_kerja.jpg');
        $templateIdentifikasiPath = resource_path('views/pdf/template_tenaga_kerja_identifikasi.jpg');

        if (!file_exists($templatePath) || !file_exists($templateIdentifikasiPath)) {
            throw new \Exception('Template not found');
        }

        $pdfInformasiUmum = $this->pdfPeralatanInformasiUmum($templatePath, $dataVendor);
        $pdfIdentifikasi = $this->tenagaKerjaPdfIdentifikasi($templateIdentifikasiPath, $identifikasiKebutuhan);
        $catatanKuisoner = $this->catatankuisonerPdf();

        $pdfTempPath = array_merge($pdfInformasiUmum, $pdfIdentifikasi, $catatanKuisoner);

        return $pdfTempPath;
    }

    private function pdfPeralatanInformasiUmum($templatePath, $dataVendor)
    {
        $provinsi = $dataVendor->provinces->nama_provinsi;
        $kabupaten = $dataVendor->cities->nama_kota;
        $namaResponden = $dataVendor['nama_vendor'];
        $alamat = $dataVendor['alamat'];
        $geoTagging = $dataVendor['koordinat'];
        $telepon = $dataVendor['no_telepon'];
        $email = '-';
        $kategoriResponden = $dataVendor['string_kategori_vendor'];
        $idProvinsi = $dataVendor->cities->provinsi_id;
        $idKabupatenKota = $dataVendor->cities->kode_kota;

        $pdf = new Fpdf();
        $pdf->AddPage('L');

        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Image($templatePath, 0, 0, 297, 210);

        //provinsi
        $pdf->SetXY(110, 22);
        $pdf->Cell(40, 100, $provinsi);

        //id provinsi
        $pdf->SetXY(246, 69);
        $pdf->Cell(24, 5, $idProvinsi, 0, 0, 'L');

        //id kabupaten kota
        $pdf->SetXY(246, 75);
        $pdf->Cell(24, 5, $idKabupatenKota, 0, 0, 'L');

        //kota/kabupaten
        $pdf->SetXY(110, 29);
        $pdf->Cell(40, 100, $kabupaten);

        //nama responden
        $pdf->SetXY(110, 35);
        $pdf->Cell(40, 100, $namaResponden);

        //alamat responden
        $pdf->SetXY(110, 41);
        $pdf->Cell(40, 100, $alamat);

        //tagging responden
        $pdf->SetXY(223, 41);
        $pdf->Cell(40, 100, $geoTagging, 0, 0, 'L');

        //telepon responden
        $pdf->SetXY(110, 47);
        $pdf->Cell(40, 100, $telepon);

        //telepon responden
        $pdf->SetXY(223, 47);
        $pdf->Cell(40, 100, $email, 0, 0, 'L');

        //kategori responden
        $pdf->SetXY(110, 53);
        $pdf->Cell(40, 100, $kategoriResponden);

        $tempFIlePath = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
        $pdf->Output('F', $tempFIlePath);
        $pdfFiles[] = $tempFIlePath;

        return $pdfFiles;
    }

    private function tenagaKerjaPdfIdentifikasi($templatePath, $data)
    {
        if (!is_array($data) || empty($data)) {
            throw new \Exception('Data is not an array or is empty.');
        }

        $pdfFiles = [];
        $count = 1;
        $Y = 60;
        $pdf = new Fpdf();
        $pdf->AddPage('L');
        $pdf->SetFont('Arial', 'B', 6);
        $pdf->Image($templatePath, 0, 0, 297, 210);

        foreach ($data as $value) {

            $pdf->SetXY(30, $Y);
            $pdf->Cell(5, 5, $count, 0, 0, 'C');

            $pdf->SetXY(43, $Y);
            $pdf->MultiCell(60, 4, $value['jenis_tenaga_kerja'], 0, 'L');

            $pdf->SetXY(107, $Y);
            $pdf->MultiCell(22, 4, $value['satuan'], 0, 'L');

            $Y += 11;

            if ($count % 12 == 0) {
                $pdf->Output();

                $pdf->AddPage('L');
                $pdf->SetFont('Arial', 'B', 6);
                $pdf->Image($templatePath, 0, 0, 297, 210);
                $Y = 60;
            }

            $count++;
        }

        $tempFIlePath = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
        $pdf->Output('F', $tempFIlePath);
        $pdfFiles[] = $tempFIlePath;

        return $pdfFiles;
    }
}
