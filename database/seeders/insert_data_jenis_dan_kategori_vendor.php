<?php

namespace Database\Seeders;

use App\Models\JenisVendor;
use App\Models\KategoriVendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class insert_data_jenis_dan_kategori_vendor extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        JenisVendor::insert([
            ['nama_jenis_vendor' => 'Material'],
            ['nama_jenis_vendor' => 'Peralatan'],
            ['nama_jenis_vendor' => 'Tenaga Kerja'],
        ]);

        KategoriVendor::insert([
            ['jenis_vendor_id' => 1, 'nama_kategori_vendor' => 'Pedagang Grosir'],
            ['jenis_vendor_id' => 1, 'nama_kategori_vendor' => 'Distributor'],
            ['jenis_vendor_id' => 1, 'nama_kategori_vendor' => 'Produsen'],
            ['jenis_vendor_id' => 1, 'nama_kategori_vendor' => 'Pedagang Campuran'],
            ['jenis_vendor_id' => 2, 'nama_kategori_vendor' => 'Jasa Penyewaan Alat Berat'],
            ['jenis_vendor_id' => 2, 'nama_kategori_vendor' => 'Kontraktor'],
            ['jenis_vendor_id' => 2, 'nama_kategori_vendor' => 'Agen'],
            ['jenis_vendor_id' => 2, 'nama_kategori_vendor' => 'Produsen'],
            ['jenis_vendor_id' => 3, 'nama_kategori_vendor' => 'Kontraktor'],
            ['jenis_vendor_id' => 3, 'nama_kategori_vendor' => 'Pemerintah Daerah'],
        ]);
    }
}
