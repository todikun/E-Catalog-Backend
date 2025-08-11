<?php

namespace Database\Seeders;

use App\Models\PemeriksaanDataList;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PemeriksaanListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PemeriksaanDataList::insert([
            ['section' => 'KRITERIA VERIFIKASI', 'item_number' => 'A1', 'description' => 'Memeriksa kelengkapan data dan ada tidaknya bukti dukung.'],
            ['section' => 'KRITERIA VERIFIKASI', 'item_number' => 'A2', 'description' => 'Jenis material, peralatan, tenaga kerja yang dilakukan pengumpulan data berdasarkan identifikasi kebutuhan.'],
            ['section' => 'KRITERIA VERIFIKASI', 'item_number' => 'A3', 'description' => 'Sumber harga pasar.'],
            ['section' => 'KRITERIA VERIFIKASI', 'item_number' => 'A4', 'description' => 'Harga survei didapat minimal 3 vendor untuk setiap jenis material peralatan atau sesuai dengan kondisi di lapangan.'],
            ['section' => 'KRITERIA VERIFIKASI', 'item_number' => 'A5', 'description' => 'Khusus peralatan mencantumkan harga beli dan harga sewa.'],
            ['section' => 'KRITERIA VALIDASI', 'item_number' => 'B1', 'description' => 'Kuesioner terisi lengkap dan sesuai dengan petunjuk cara pengisian kuesioner (lampiran iv) dan sudah ditandatangani Responden, Petugas Lapangan, dan Pengawas.'],
            ['section' => 'KRITERIA VALIDASI', 'item_number' => 'B2', 'description' => 'Pemeriksaan dilakukan dengan diskusi/tatap muka antara Pengawas dan Petugas Lapangan.'],
            ['section' => 'KRITERIA PEMERIKSAAN HASIL DATA', 'item_number' => 'C1', 'description' => 'Pemeriksaan satuan yang salah atau belum terisi.'],
            ['section' => 'KRITERIA PEMERIKSAAN HASIL DATA', 'item_number' => 'C2', 'description' => 'Penulisan nama kabupaten/kota.'],
            ['section' => 'KRITERIA PEMERIKSAAN HASIL DATA', 'item_number' => 'C3', 'description' => 'Nama responden/vendor yang tidak jelas.'],
            ['section' => 'KRITERIA PEMERIKSAAN HASIL DATA', 'item_number' => 'C4', 'description' => 'Konsistensi dalam pengisian kuesioner.'],
            ['section' => 'PEMERIKSAAN ANOMALI HARGA', 'item_number' => 'D1', 'description' => 'Ketidakwajaran harga satuan pokok.'],
            ['section' => 'PEMERIKSAAN ANOMALI HARGA', 'item_number' => 'D2', 'description' => 'Keterbandingan antar harga satuan pokok di wilayah yang berdekatan.'],
        ]);
    }
}
