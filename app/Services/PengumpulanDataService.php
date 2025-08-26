<?php

namespace App\Services;

use App\Models\KategoriVendor;
use App\Models\KeteranganPetugasSurvey;
use App\Models\Material;
use App\Models\MaterialSurvey;
use App\Models\Pengawas;
use App\Models\PengolahData;
use App\Models\Peralatan;
use App\Models\PeralatanSurvey;
use App\Models\PerencanaanData;
use App\Models\PetugasLapangan;
use App\Models\Roles;
use App\Models\ShortlistVendor;
use App\Models\TeamTeknisBalai;
use App\Models\TenagaKerja;
use App\Models\TenagaKerjaModel;
use App\Models\TenagaKerjaSurvey;
use App\Models\Users;
use App\Models\VerifikasiValidasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class PengumpulanDataService
{
    public function storeTeamPengumpulanData($data)
    {
        $teamPengumpulanData = new TeamTeknisBalai();
        $teamPengumpulanData->nama_team = $data['nama_team'];
        $teamPengumpulanData->user_id_ketua = $data['ketua_team'];
        $teamPengumpulanData->user_id_sekretaris = $data['sekretaris_team'];
        $teamPengumpulanData->user_id_anggota = $data['anggota'];
        $teamPengumpulanData->url_sk_penugasan = $data['sk_penugasan'];
        $teamPengumpulanData->save();

        return $teamPengumpulanData;
    }

    public function getAllTeamPengumpulanData()
    {
        return TeamTeknisBalai::select('id', 'nama_team')->get();
    }

    public function assignTeamPengumpulanData($data)
    {
        return PerencanaanData::updateOrCreate(
            [
                'id' => $data['id_pengumpulan_data'],
            ],
            [
                'team_pengumpulan_data_id' => $data['id_team_pengumpulan_data'],
            ]
        );
    }

    public function listUserPengumpulan($role)
    {

        $query = Users::select('id AS user_id', 'nama_lengkap')
            ->where('status', 'active')
            ->where('id_roles', $role)
            ->whereNotNull('email_verified_at')
            ->whereNot('id_roles', 1)->get();

        $query = $query->filter(function ($item) use ($role) {
            $exists = false;

            if ($role == 'pengawas') {
                $exists = PerencanaanData::whereJsonContains('pengawas_id', (string) $item->user_id)->exists();
            } elseif ($role == 'pengolah data') {
                $exists = PerencanaanData::whereJsonContains('pengolah_data_id', (string) $item->user_id)->exists();
            } elseif ($role == 'petugas lapangan') {
                $exists = PerencanaanData::whereJsonContains('petugas_lapangan_id', (string) $item->user_id)->exists();
            }

            // keep hanya user yang TIDAK ditugaskan
            return !$exists;
        })->values(); // reset index

        return $query;
    }

    public function getListRoles($rolesString)
    {
        $role = Roles::select('id')
            ->where('nama', $rolesString)->first();

        if ($role) {
            return $role->id;
        }
        return [];
    }

    public function listPenugasan($table)
    {
        if ($table == 'pengawas') {
            $data = Pengawas::select(
                'pengawas.id as pengawas_id',
                'pengawas.sk_penugasan',
                'users.nama_lengkap',
                'users.id as id_user',
                'users.nrp',
                'satuan_kerja.nama as satuan_kerja_nama',
            )
                ->join('users', 'pengawas.user_id', '=', 'users.id')
                ->join('satuan_kerja', 'users.satuan_kerja_id', '=', 'satuan_kerja.id')
                ->get();

            $data->transform(function ($item) {
                $exists = PerencanaanData::whereJsonContains('pengawas_id', (string) $item->id_user)
                    ->exists();

                $item->status = $exists ? 'ditugaskan' : 'belum ditugaskan';
                $item->url_sk_penugasan = Storage::url($item->sk_penugasan);
                unset($item->sk_penugasan);
                return $item;
            });

            return $data;
        } elseif ($table == 'pengolah data') {
            $data = PengolahData::select(
                'pengolah_data.id as pengolah_data_id',
                'pengolah_data.sk_penugasan',
                'users.nama_lengkap',
                'users.id as id_user',
                'users.nrp',
                'satuan_kerja.nama as satuan_kerja_nama',
            )
                ->join('users', 'pengolah_data.user_id', '=', 'users.id')
                ->join('satuan_kerja', 'users.satuan_kerja_id', '=', 'satuan_kerja.id')
                ->get();

            $data->transform(function ($item) {
                $exists = PerencanaanData::whereJsonContains('pengolah_data_id', (string) $item->id_user)
                    ->exists();

                $item->status = $exists ? 'ditugaskan' : 'belum ditugaskan';
                $item->url_sk_penugasan = Storage::url($item->sk_penugasan);
                unset($item->sk_penugasan);
                return $item;
            });

            return $data;
        } elseif ($table == 'petugas lapangan') {
            $data = PetugasLapangan::select(
                'petugas_lapangan.id as petugas_lapangan_id',
                'petugas_lapangan.sk_penugasan',
                'users.nama_lengkap',
                'users.id as id_user',
                'users.nrp',
                'satuan_kerja.nama as satuan_kerja_nama',
            )
                ->join('users', 'petugas_lapangan.user_id', '=', 'users.id')
                ->join('satuan_kerja', 'users.satuan_kerja_id', '=', 'satuan_kerja.id')
                ->get();

            $data->transform(function ($item) {
                $exists = PerencanaanData::whereJsonContains('petugas_lapangan_id', (string) $item->id_user)
                    ->exists();

                $item->status = $exists ? 'ditugaskan' : 'belum ditugaskan';
                $item->url_sk_penugasan = Storage::url($item->sk_penugasan);
                unset($item->sk_penugasan);
                return $item;
            });

            return $data;
        }
    }

    public function assignPenugasan($table, $idTable, $idPerencanaan)
    {
        $array = explode(',', $idTable);
        $arrayPerson = array_map('intval', $array);

        // Todo: Check if the user is exists
        $userExists = Users::whereIn('id', $array)->get();
        $countUserExists = $userExists->count();

        if (count($arrayPerson) != $countUserExists) {
            return [];
        }

        if ($table == 'pengawas') {
            return PerencanaanData::updateOrCreate(
                [
                    'id' => $idPerencanaan,
                ],
                [
                    'pengawas_id' => $array,
                ]
            );
        } elseif ($table == 'pengolah data') {
            return PerencanaanData::updateOrCreate(
                [
                    'id' => $idPerencanaan,
                ],
                [
                    'pengolah_data_id' => $array,
                ]
            );
        } elseif ($table == 'petugas lapangan') {
            return PerencanaanData::updateOrCreate(
                [
                    'id' => $idPerencanaan,
                ],
                [
                    'petugas_lapangan_id' => $array,
                ]
            );
        }
    }

    public function listVendorByPerencanaanId($perencanaanId)
    {
        return ShortlistVendor::select(
            'id As shortlist_id',
            'shortlist_vendor_id As informasi_umum_id',
            'nama_vendor',
            'pemilik_vendor As pic',
            'alamat As alamat_vendor'
        )
            ->where('shortlist_vendor_id', $perencanaanId)
            ->get();
    }

    public function showKuisioner($shortlistId)
    {
        return ShortlistVendor::select(
            'url_kuisioner'
        )
            ->where('id', $shortlistId)
            ->first();
    }

    public function generateLinkKuisioner($id)
    {
        $data = [
            'shortlist_id' => $id,
            'timestamp' => now()->timestamp
        ];

        $encryptedToken = Crypt::encryptString(json_encode($data));

        $url = URL::to('/api/survey-kuisioner/get-data-survey') . '?token=' . urlencode($encryptedToken);

        return $url;
    }

    private function getPengawasPetugasLapangan($shortlistId)
    {
        $data = PerencanaanData::select(
            'id',
            'pengawas_id',
            'petugas_lapangan_id',
        )->where('shortlist_vendor_id', $shortlistId)->first();

        if (!$data) {
            return response()->json(['message' => 'Data not found']);
        }

        $pengawasIds = json_decode($data->pengawas_id);
        $petugasLapanganIds = json_decode($data->petugas_lapangan_id);

        $pengawas = Users::whereIn('id', $pengawasIds)->get(['nama_lengkap', 'nrp', 'id']);
        $petugasLapangan = Users::whereIn('id', $petugasLapanganIds)->get(['nama_lengkap', 'nrp', 'id']);

        $responseData = [
            'id' => $data->id,
            'pengawas' => $pengawas->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'name' => $user->nama_lengkap,
                    'nip' => $user->nrp,
                ];
            }),
            'petugas_lapangan' => $petugasLapangan->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'name' => $user->nama_lengkap,
                    'nip' => $user->nrp,
                ];
            }),
        ];

        return $responseData;
    }

    public function getDataForKuisioner($shortlistId)
    {
        $vendor = ShortlistVendor::select(
            'data_vendors.nama_vendor',
            'data_vendors.kategori_vendor_id',
            'data_vendors.alamat',
            'data_vendors.no_telepon',
            'data_vendors.provinsi_id',
            'data_vendors.kota_id',
            'provinces.nama_provinsi',
            'cities.nama_kota',
            'shortlist_vendor.shortlist_vendor_id As identifikasi_kebutuhan_id',
            'shortlist_vendor.petugas_lapangan_id',
            'shortlist_vendor.pengawas_id',
            'shortlist_vendor.nama_pemberi_informasi',
            'shortlist_vendor.tanggal_survei',
            'shortlist_vendor.tanggal_pengawasan',
            'data_vendors.id As vendor_id',
            'kuisioner_pdf_data.material_id',
            'kuisioner_pdf_data.peralatan_id',
            'kuisioner_pdf_data.tenaga_kerja_id',
        )
            ->join('data_vendors', 'shortlist_vendor.data_vendor_id', '=', 'data_vendors.id')
            ->join('provinces', 'data_vendors.provinsi_id', '=', 'provinces.kode_provinsi')
            ->join('cities', 'data_vendors.kota_id', '=', 'cities.kode_kota')
            ->join('kuisioner_pdf_data', 'data_vendors.id', '=', 'kuisioner_pdf_data.vendor_id')
            ->where('shortlist_vendor.id', $shortlistId)
            ->first();

        if (!$vendor) {
            throw new \Exception("Data tidak ditemukan untuk shortlist_id: {$shortlistId}");
        }

        $keteranganPetugas = $this->getKeteranganPetugas($vendor['petugas_lapangan_id']);
        $keteranganPengawas = $this->getKeteranganPetugas($vendor['pengawas_id']);

        $material = $this->getIdentifikasiSurvey('material', $vendor['material_id']);
        $peralatan = $this->getIdentifikasiSurvey('peralatan', $vendor['peralatan_id']);
        $tenagaKerja = $this->getIdentifikasiSurvey('tenaga_kerja', $vendor['tenaga_kerja_id']);

        $kategoriVendor = KategoriVendor::whereIn('id', json_decode($vendor['kategori_vendor_id'], true))
            ->select('nama_kategori_vendor as name')
            ->get();
        $stringKategoriVendor = $kategoriVendor->pluck('name')->implode(', ');

        $response = [
            'data_vendor_id' => $vendor['vendor_id'],
            'identifikasi_kebutuhan_id' => $vendor['identifikasi_kebutuhan_id'],
            'provinsi' => $vendor['nama_provinsi'],
            'kota' => $vendor['nama_kota'],
            'nama_responden' => $vendor['nama_vendor'],
            'alamat' => $vendor['alamat'],
            'no_telepon' => $vendor['no_telepon'],
            'kategori_responden' => $stringKategoriVendor,
            'keterangan_petugas_lapangan' => [
                'nama_petugas_lapangan' => isset($keteranganPetugas['nama']) ? $keteranganPetugas['nama'] : null,
                'nip_petugas_lapangan' => isset($keteranganPetugas['nip']) ? $keteranganPetugas['nip'] : null,
                'tanggal_survei' => isset($vendor['tanggal_survei']) ? Carbon::createFromFormat('Y-m-d', $vendor['tanggal_survei'])->format('d-m-Y') : null,
                'nama_pengawas' => isset($keteranganPengawas['nama']) ? $keteranganPengawas['nama'] : null,
                'nip_pengawas' => isset($keteranganPengawas['nip']) ? $keteranganPengawas['nip'] : null,
                'tanggal_pengawasan' => isset($vendor['tanggal_pengawasan']) ? Carbon::createFromFormat('Y-m-d', $vendor['tanggal_pengawasan'])->format('d-m-Y') : null,
            ],
            'keterangan_pemberi_informasi' => [
                'nama_pemberi_informasi' => isset($vendor['nama_pemberi_informasi']) ? $vendor['nama_pemberi_informasi'] : null,
                'tanda_tangan_responden' => isset($vendor['nama_pemberi_informasi'])
                    ? 'Ditandatangain oleh ' . $vendor['nama_pemberi_informasi'] . ' pada ' . Carbon::now()
                    : null
            ],
            'material' => $material,
            'peralatan' => $peralatan,
            'tenaga_kerja' => $tenagaKerja,
        ];
        return $response;
    }

    private function getKeteranganPetugas($id)
    {
        return Users::select('nama_lengkap As nama', 'nrp As nip')
            ->where('id', $id)->first();
    }

    private function getIdentifikasiSurvey($table, $id)
    {
        // Decode JSON and ensure it's an array
        $idArray = json_decode($id, true);
        if (!is_array($idArray) || empty($idArray)) {
            return collect(); // Return an empty collection if $id is invalid or empty
        }

        // Check for existence in related surveys
        $checkMaterial = MaterialSurvey::whereIn('material_id', $idArray)->exists();
        $checkPeralatan = PeralatanSurvey::whereIn('peralatan_id', $idArray)->exists();
        $checkTenagaKerja = TenagaKerjaSurvey::whereIn('tenaga_kerja_id', $idArray)->exists();

        if ($table == 'material') {
            if ($checkMaterial) {
                return Material::select(
                    'material.id',
                    'material.identifikasi_kebutuhan_id',
                    'material.nama_material',
                    'material.satuan',
                    'material.spesifikasi',
                    'material.ukuran',
                    'material.kodefikasi',
                    'material.kelompok_material',
                    'material.jumlah_kebutuhan',
                    'material.merk',
                    'material.provincies_id',
                    'material.cities_id',
                    'material_survey.satuan_setempat',
                    'material_survey.satuan_setempat_panjang',
                    'material_survey.satuan_setempat_lebar',
                    'material_survey.satuan_setempat_tinggi',
                    'material_survey.konversi_satuan_setempat',
                    'material_survey.harga_satuan_setempat',
                    'material_survey.harga_konversi_satuan_setempat',
                    'material_survey.harga_khusus',
                    'material_survey.keterangan',
                )
                    ->join('material_survey', 'material.id', '=', 'material_survey.material_id')
                    ->whereIn('material.id', $idArray)
                    ->get();
            } else {
                return Material::whereIn('id', $idArray)->get();
            }
        } elseif ($table == 'peralatan') {
            if ($checkPeralatan) {
                return Peralatan::select(
                    'peralatan.id',
                    'peralatan.identifikasi_kebutuhan_id',
                    'peralatan.nama_peralatan',
                    'peralatan.satuan',
                    'peralatan.spesifikasi',
                    'peralatan.kapasitas',
                    'peralatan.kodefikasi',
                    'peralatan.kelompok_peralatan',
                    'peralatan.jumlah_kebutuhan',
                    'peralatan.merk',
                    'peralatan.provincies_id',
                    'peralatan.cities_id',
                    'peralatan_survey.satuan_setempat',
                    'peralatan_survey.harga_sewa_satuan_setempat',
                    'peralatan_survey.harga_sewa_konversi',
                    'peralatan_survey.harga_pokok',
                )
                    ->join('peralatan_survey', 'peralatan.id', '=', 'peralatan_survey.peralatan_id')
                    ->whereIn('peralatan.id', $idArray)
                    ->get();
            } else {
                return Peralatan::whereIn('id', $idArray)->get();
            }
        } elseif ($table == 'tenaga_kerja') {
            if ($checkTenagaKerja) {
                return TenagaKerja::select(
                    'tenaga_kerja.id',
                    'tenaga_kerja.identifikasi_kebutuhan_id',
                    'tenaga_kerja.jenis_tenaga_kerja',
                    'tenaga_kerja.satuan',
                    'tenaga_kerja.jumlah_kebutuhan',
                    'tenaga_kerja.kodefikasi',
                    'tenaga_kerja.provincies_id',
                    'tenaga_kerja.cities_id',
                    'tenaga_kerja_survey.harga_per_satuan_setempat',
                    'tenaga_kerja_survey.harga_konversi_perjam',
                    'tenaga_kerja_survey.keterangan',
                )
                    ->join('tenaga_kerja_survey', 'tenaga_kerja.id', '=', 'tenaga_kerja_survey.tenaga_kerja_id')
                    ->whereIn('tenaga_kerja.id', $idArray)
                    ->get();
            } else {
                return TenagaKerja::whereIn('id', $idArray)->get();
            }
        }

        return collect(); // Default return for unsupported $table
    }


    public function getEntriData($shortlistId)
    {
        $vendor = ShortlistVendor::select(
            'data_vendors.nama_vendor',
            'data_vendors.kategori_vendor_id',
            'data_vendors.alamat',
            'data_vendors.no_telepon',
            'data_vendors.provinsi_id',
            'data_vendors.kota_id',
            'provinces.nama_provinsi',
            'cities.nama_kota',
            'shortlist_vendor.shortlist_vendor_id As identifikasi_kebutuhan_id',
            'shortlist_vendor.petugas_lapangan_id',
            'shortlist_vendor.pengawas_id',
            'shortlist_vendor.nama_pemberi_informasi',
            'shortlist_vendor.tanggal_survei',
            'shortlist_vendor.tanggal_pengawasan',
            'data_vendors.id As vendor_id',
            'kuisioner_pdf_data.material_id',
            'kuisioner_pdf_data.peralatan_id',
            'kuisioner_pdf_data.tenaga_kerja_id',
        )
            ->join('data_vendors', 'shortlist_vendor.data_vendor_id', '=', 'data_vendors.id')
            ->join('provinces', 'data_vendors.provinsi_id', '=', 'provinces.kode_provinsi')
            ->join('cities', 'data_vendors.kota_id', '=', 'cities.kode_kota')
            ->join('kuisioner_pdf_data', 'data_vendors.id', '=', 'kuisioner_pdf_data.vendor_id')
            ->where('shortlist_vendor.id', $shortlistId)
            ->first();

        $keteranganPetugas = $this->getKeteranganPetugas($vendor['petugas_lapangan_id']);
        $keteranganPengawas = $this->getKeteranganPetugas($vendor['pengawas_id']);

        $material = ($vendor['material_id']) ? Material::whereIn('id', json_decode($vendor['material_id']))->get() : null;
        $peralatan = ($vendor['peralatan_id']) ? Peralatan::whereIn('id', json_decode($vendor['peralatan_id']))->get() : null;
        $tenagaKerja = ($vendor['tenaga_kerja_id']) ? TenagaKerja::whereIn('id', json_decode($vendor['tenaga_kerja_id']))->get() : null;

        $kategoriVendor = KategoriVendor::whereIn('id', json_decode($vendor['kategori_vendor_id'], true))
            ->select('nama_kategori_vendor as name')
            ->get();
        $stringKategoriVendor = $kategoriVendor->pluck('name')->implode(', ');

        $response = [
            'data_vendor_id' => $vendor['vendor_id'],
            'identifikasi_kebutuhan_id' => $vendor['identifikasi_kebutuhan_id'],
            'provinsi' => $vendor['nama_provinsi'],
            'kota' => $vendor['nama_kota'],
            'nama_responden' => $vendor['nama_vendor'],
            'alamat' => $vendor['alamat'],
            'no_telepon' => $vendor['no_telepon'],
            'kategori_responden' => $stringKategoriVendor,
            'keterangan_petugas_lapangan' => [
                'nama_petugas_lapangan' => isset($keteranganPetugas['nama']) ? $keteranganPetugas['nama'] : null,
                'nip_petugas_lapangan' => isset($keteranganPetugas['nip']) ? $keteranganPetugas['nip'] : null,
                'tanggal_survei' => isset($vendor['tanggal_survei']) ? Carbon::createFromFormat('Y-m-d', $vendor['tanggal_survei'])->format('d-m-Y') : null,
                'nama_pengawas' => isset($keteranganPengawas['nama']) ? $keteranganPengawas['nama'] : null,
                'nip_pengawas' => isset($keteranganPengawas['nip']) ? $keteranganPengawas['nip'] : null,
                'tanggal_pengawasan' => isset($vendor['tanggal_pengawasan']) ? Carbon::createFromFormat('Y-m-d', $vendor['tanggal_pengawasan'])->format('d-m-Y') : null,
            ],
            'keterangan_pemberi_informasi' => [
                'nama_pemberi_informasi' => isset($vendor['nama_pemberi_informasi']) ? $vendor['nama_pemberi_informasi'] : null,
                'tanda_tangan_responden' => isset($vendor['nama_pemberi_informasi'])
                    ? 'Ditandatangain oleh ' . $vendor['nama_pemberi_informasi'] . ' pada ' . Carbon::now()
                    : null
            ],
            'material' => $material,
            'peralatan' => $peralatan,
            'tenaga_kerja' => $tenagaKerja,
        ];
        return $response;
    }

    public function updateDataVerifikasiPengawas($data)
    {
        return ShortlistVendor::updateOrCreate(
            [
                'data_vendor_id' => $data['data_vendor_id'],
                'shortlist_vendor_id' => $data['identifikasi_kebutuhan_id'],
            ],
            [
                'catatan_blok_1' => $data['catatan_blok_1'],
                'catatan_blok_2' => $data['catatan_blok_2'],
                'catatan_blok_3' => $data['catatan_blok_3'],
                'catatan_blok_4' => $data['catatan_blok_4'],
            ]
        );
    }

    public function pemeriksaanDataList($data)
    {
        $result = [];
        foreach (json_decode($data['verifikasi_validasi']) as $value) {
            $result[] = VerifikasiValidasi::updateOrCreate(
                [
                    'data_vendor_id' => $data->data_vendor_id,
                    'shortlist_vendor_id' => $data->identifikasi_kebutuhan_id,
                    'item_number' => $value->id_pemeriksaan,
                ],
                [
                    'status_pemeriksaan' => $value->status_pemeriksaan,
                    'verified_by' => $value->verified_by,
                ]
            );
        }

        return $result;
    }

    public function changeStatusVerification($id, $filePath)
    {
        return PerencanaanData::updateOrCreate(
            [
                'identifikasi_kebutuhan_id' => $id,
            ],
            [
                'status' => config('constants.STATUS_PEMERIKSAAN'),
                'doc_berita_acara' => $filePath,
            ]
        );
    }

    public function changeStatusValidation($id, $filePath, $status)
    {
        return PerencanaanData::updateOrCreate(
            [
                'identifikasi_kebutuhan_id' => $id,
            ],
            [
                'status' => $status,
                'doc_berita_acara' => $filePath,
            ]
        );
    }

    public function updateIdentifikasi($table, $tableId, $data)
    {
        if ($table == 'material') {
            return Material::updateOrCreate(
                [
                    'id' => $tableId,
                ],
                [
                    'satuan_setempat' => $data['satuan_setempat'],
                    'satuan_setempat_panjang' => $data['satuan_setempat_panjang'],
                    'satuan_setempat_lebar' => $data['satuan_setempat_lebar'],
                    'satuan_setempat_tinggi' => $data['satuan_setempat_tinggi'],
                    'konversi_satuan_setempat' => $data['konversi_satuan_setempat'],
                    'harga_satuan_setempat' => $data['harga_satuan_setempat'],
                    'harga_konversi_satuan_setempat' => $data['harga_konversi_satuan_setempat'],
                    'harga_khusus' => $data['harga_khusus'],
                    'keterangan' => $data['keterangan'],
                ]
            );
        } elseif ($table == 'peralatan') {
            return Peralatan::updateOrCreate(
                [
                    'id' => $tableId,
                ],
                [
                    'satuan_setempat' => $data['satuan_setempat'],
                    'harga_sewa_satuan_setempat' => $data['harga_sewa_satuan_setempat'],
                    'harga_sewa_konversi' => $data['harga_sewa_konversi'],
                    'harga_pokok' => $data['harga_pokok'],
                    'keterangan' => $data['keterangan'],
                ]
            );
        } elseif ($table == 'tenaga_kerja') {
            return TenagaKerja::updateOrCreate(
                [
                    'id' => $tableId,
                ],
                [
                    'harga_per_satuan_setempat' => $data['harga_per_satuan_setempat'],
                    'harga_konversi_perjam' => $data['harga_konversi_perjam'],
                    'keterangan' => $data['keterangan'],
                ]
            );
        }
    }

    public function updateShortlistVendor($shortlistId, $vendorId, $data)
    {

        return ShortlistVendor::updateOrCreate(
            [
                'shortlist_vendor_id' => $shortlistId,
                'data_vendor_id' => $vendorId
            ],
            [
                'petugas_lapangan_id' => $data['user_id_petugas_lapangan'],
                'pengawas_id' => $data['user_id_pengawas'],
                'nama_pemberi_informasi' => $data['nama_pemberi_informasi'],
                'tanggal_survei' => Carbon::createFromFormat('d-m-Y', $data['tanggal_survei'])->format('Y-m-d'),
                'tanggal_pengawasan' => Carbon::createFromFormat('d-m-Y', $data['tanggal_pengawasan'])->format('Y-m-d'),
            ]
        );
    }

    public function storeIdentifikasiSurvey($data, $table)
    {
        if ($table == 'material') {
            return MaterialSurvey::updateOrCreate(
                [
                    'material_id' => $data['id'],
                ],
                [
                    'satuan_setempat' => $data['satuan_setempat'],
                    'satuan_setempat_panjang' => $data['satuan_setempat_panjang'],
                    'satuan_setempat_lebar' => $data['satuan_setempat_lebar'],
                    'satuan_setempat_tinggi' => $data['satuan_setempat_tinggi'],
                    'konversi_satuan_setempat' => $data['konversi_satuan_setempat'],
                    'harga_satuan_setempat' => $data['harga_satuan_setempat'],
                    'harga_konversi_satuan_setempat' => $data['harga_konversi_satuan_setempat'],
                    'harga_khusus' => $data['harga_khusus'],
                    'keterangan' => $data['keterangan'],
                ]
            );
        } elseif ($table == 'peralatan') {
            return PeralatanSurvey::updateOrCreate(
                [
                    'peralatan_id' => $data['id'],
                ],
                [
                    'satuan_setempat' => $data['satuan_setempat'],
                    'harga_sewa_satuan_setempat' => $data['harga_sewa_satuan_setempat'],
                    'harga_sewa_konversi' => $data['harga_sewa_konversi'],
                    'harga_pokok' => $data['harga_pokok'],
                    'keterangan' => $data['keterangan'],
                ]
            );
        } elseif ($table == 'tenaga_kerja') {
            return TenagaKerjaSurvey::updateOrCreate(
                [
                    'tenaga_kerja_id' => $data['id'],
                ],
                [
                    'harga_per_satuan_setempat' => $data['harga_per_satuan_setempat'],
                    'harga_konversi_perjam' => $data['harga_konversi_perjam'],
                    'keterangan' => $data['keterangan'],
                ]
            );
        }
    }

    public function storeKeteranganPetugasSurvey($data)
    {
        return KeteranganPetugasSurvey::updateOrCreate(
            [
                'identifikasi_kebutuhan_id' => $data['identifikasi_kebutuhan_id'],
            ],
            [
                'petugas_lapangan_id' => $data['user_id_petugas_lapangan'],
                'pengawas_id' => $data['user_id_pengawas'],
                'nama_pemberi_informasi' => $data['nama_pemberi_informasi'],
                'tanggal_survei' => Carbon::createFromFormat('d-m-Y', $data['tanggal_survei'])->format('Y-m-d'),
                'tanggal_pengawasan' => Carbon::createFromFormat('d-m-Y', $data['tanggal_pengawasan'])->format('Y-m-d'),
            ]
        );
    }

    public function changeStatus($id, $status)
    {
        if ($status == config('constants.STATUS_PENGISIAN_PETUGAS')) {
            $shortlistVendorId = ShortlistVendor::select('shortlist_vendor_id')
                ->where('id', $id)->first();

            return PerencanaanData::updateOrCreate(
                [
                    'shortlist_vendor_id' => $shortlistVendorId['shortlist_vendor_id'],
                ],
                [
                    'status' => $status
                ]
            );
        } else {
            return PerencanaanData::updateOrCreate(
                [
                    'shortlist_vendor_id' => $id,
                ],
                [
                    'status' => $status
                ]
            );
        }
    }

    public function getPemeriksaanDataList($dataVendorId, $shortlistVendorId)
    {
        $data = VerifikasiValidasi::select(
            'data_vendor_id',
            'shortlist_vendor_id',
            'item_number',
            'status_pemeriksaan',
            'verified_by',
        )->where('data_vendor_id', $dataVendorId)
            ->where('shortlist_vendor_id', $shortlistVendorId)->get();
        return $data;
    }
}
