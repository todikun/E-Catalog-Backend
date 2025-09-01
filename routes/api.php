<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BalaiKerjaController;
use App\Http\Controllers\EksternalAppController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\PengumpulanDataController;
use App\Http\Controllers\PerencanaanDataController;
use App\Http\Controllers\ProvinceAndCitiesController;
use App\Http\Controllers\SatuanKerjaController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\KuisionerController;
use App\Http\Controllers\PemeriksaanAndRekonsiliasiController;
use App\Http\Controllers\SurveyKuisionerController;
use App\Models\SatuanBalaiKerja;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/hello-nginx-vm', function () {
    return response()->json(['message' => "Hello World Nginx VM"]);
});

Route::post('/store-user', [UsersController::class, 'store']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout']);
Route::post('/refresh', [LoginController::class, 'refresh']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);

Route::get('/get-balai-kerja', [BalaiKerjaController::class, 'getAllSatuanBalaiKerja']);
Route::get('/get-satuan-kerja', [SatuanKerjaController::class, 'getAllSatuanKerja']);

// TODO: please implements the jwt-code from SSO Sipasti
Route::get('/check-role', [LoginController::class, 'checkRole']);

Route::get('/list-role', [UsersController::class, 'listRole']);
Route::get('/user/list-user-verif', [UsersController::class, 'getListUserVerification']);

Route::get('/get-user/{id}', [UsersController::class, 'getUserById']);

Route::post('/store-balai-kerja', [BalaiKerjaController::class, 'storeSatuanBalaiKerja']);

Route::post('/store-satuan-kerja', [SatuanKerjaController::class, 'storeSatuanKerja']);

Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');

Route::get('/get-vendor/{id}', [VendorController::class, 'getVendor']);
Route::get('/get-vendor-all', [VendorController::class, 'getVendorAll']);
Route::post('/input-vendor', [VendorController::class, 'inputVendor']);

Route::post('/perencanaan-data/store-informasi-umum/', [PerencanaanDataController::class, 'storeInformasiUmumData']);
Route::get('/perencanaan-data/informasi-umum/{id}', [PerencanaanDataController::class, 'getInformasiUmumByPerencanaanId']);
Route::post('/perencanaan-data/store-identifikasi-kebutuhan', [PerencanaanDataController::class, 'storeIdentifikasiKebutuhan']);
Route::get('/perencanaan-data/get-identifikasi-kebutuhan/{id}', [PerencanaanDataController::class, 'getIdentifikasiKebutuhanStored']);

Route::get('/perencanaan-data/get-data-vendor/{id}', [PerencanaanDataController::class, 'getAllDataVendor']);
Route::post('/perencanaan-data/store-shortlist-vendor', [PerencanaanDataController::class, 'selectDataVendor']);
Route::get('/perencanaan-data/perencanaan-data-result', [PerencanaanDataController::class, 'perencanaanDataResult']);
Route::get('/perencanaan-data/shortlist-detail-identifikasi', [PerencanaanDataController::class, 'getShortlistVendorSumberDaya']);
Route::post('/perencanaan-data/adjust-identifikasi-kebutuhan', [PerencanaanDataController::class, 'adjustShortlistVendor']);
Route::post('/perencanaan-data/save-perencanaan-data/{id}', [PerencanaanDataController::class, 'changeStatusPerencanaan']);
Route::get('/perencanaan-data/table-list-prencanaan-data', [PerencanaanDataController::class, 'tableListPerencanaan']);
Route::get('/perencanaan-data/get-kuisioner-informasi-umum/{id}', [KuisionerController::class, 'getInformasiUmum']);
Route::get('/perencanaan-data/get-kuisioner-material/{id}', [KuisionerController::class, 'getMaterial']);
Route::get('/perencanaan-data/get-kuisioner-peralatan/{id}', [KuisionerController::class, 'getPeralatan']);
Route::get('/perencanaan-data/get-kuisioner-tenaga-kerja/{id}', [KuisionerController::class, 'getTenagaKerja']);
Route::get('/perencanaan-data/get-kuisioner-short-list-vendor/{id}', [KuisionerController::class, 'getShortListVendor']);
Route::post('perencanaan-data/generate-pdf-sunting/{id}', [KuisionerController::class, 'generatePdf']);


Route::get('/test-email', function () {
    Mail::raw('This is a test email', function ($message) {
        $message->to('bayuaditya0111@gmail.com')
            ->subject('Test Email');
    });

    return 'Email sent!';
});


Route::get('/verification/success', function () {
    return view('verification.success');
})->name('verification.success');

Route::get('/verification/already-verified', function () {
    return view('verification.already_verified');
})->name('verification.already_verified');

Route::get('/password/reset/{token}', function ($token) {
    return view('auth.password.reset', ['token' => $token]);
})->name('password.reset');

Route::get('/provinces-and-cities', [ProvinceAndCitiesController::class, 'getProvinceAndCities']);

Route::get('/pengumpulan-data/get-team-pengumpulan', [PengumpulanDataController::class, 'getTeamPengumpulanData']);
Route::post('/pengumpulan-data/assign-team-pengumpulan', [PengumpulanDataController::class, 'assignTeamPengumpulanData']);
Route::post('/pengumpulan-data/store-team-teknis', [PengumpulanDataController::class, 'storeTeamTeknisBalai']);

Route::get('/pengumpulan-data/table-list-pengumpulan', [PengumpulanDataController::class, 'listPengumpulanData']);
Route::get('/pengumpulan-data/list-user', [PengumpulanDataController::class, 'listUser']);
Route::get('/pengumpulan-data/list-pengumpulan-by-nama', [PengumpulanDataController::class, "listPengumpulanByNama"]);

Route::post('/pengumpulan-data/store-pengawas', [PengumpulanDataController::class, 'storePengawas']);
Route::post('/pengumpulan-data/store-petugas-lapangan', [PengumpulanDataController::class, 'storePetugasLapangan']);
Route::post('/pengumpulan-data/store-pengolah-data', [PengumpulanDataController::class, 'storepengolahData']);

Route::get('/pengumpulan-data/list-pengawas', [PengumpulanDataController::class, 'listPengawas']);
Route::get('/pengumpulan-data/list-pengolah-data', [PengumpulanDataController::class, 'listPengolahData']);
Route::get('/pengumpulan-data/list-petugas-lapangan', [PengumpulanDataController::class, 'listPetugasLapangan']);

Route::post('/pengumpulan-data/assign-pengawas', [PengumpulanDataController::class, 'assignPengawas']);
Route::post('/pengumpulan-data/assign-pengolah-data', [PengumpulanDataController::class, 'assignPengolahData']);
Route::post('/pengumpulan-data/assign-petugas-lapangan', [PengumpulanDataController::class, 'assignPetugasLapangan']);

Route::get('/pengumpulan-data/get-entri-data/{id}', [PengumpulanDataController::class, 'getEntriData']);
Route::get('/pengumpulan-data/view-pdf-kuisioner/{id}', [PengumpulanDataController::class, 'viewPdfKuisioner']);
Route::get('/pengumpulan-data/list-vendor-by-paket/{id}', [PengumpulanDataController::class, 'listVendorByPaket']);

Route::post('/pengumpulan-data/store-entri-data', [PengumpulanDataController::class, 'entriDataSave']);
Route::post('/pengumpulan-data/verifikasi-pengawas', [PengumpulanDataController::class, 'verifikasiPengawas']);

Route::get('/pengumpulan-data/generate-link/{id}', [SurveyKuisionerController::class, 'generateLinkKuisioner']);
Route::get('/survey-kuisioner/get-data-survey', [SurveyKuisionerController::class, 'getDataForSurveyKuisioner']);
Route::post('/survey-kuisioner/store-survey-kuisioner', [SurveyKuisionerController::class, 'storeSurveyKuisioner']);

Route::get('/pemeriksaan-rekonsiliasi/get-list-data', [PemeriksaanAndRekonsiliasiController::class, 'getAllDataPemeriksaanRekonsiliasi']);
Route::get('/pemeriksaan-rekonsiliasi/get-data-pemeriksaan-rekonsiliasi/{id}', [PemeriksaanAndRekonsiliasiController::class, 'eksternal/get-all-data-material']);
Route::post('/pemeriksaan-rekonsiliasi/store-verifikasi-validasi', [PemeriksaanAndRekonsiliasiController::class, 'storePemeriksaanRekonsiliasi']);

Route::get('/eksternal/get-all-data-material', [EksternalAppController::class, 'getAllDataMaterial']);
Route::get('/eksternal/get-all-data-peralatan', [EksternalAppController::class, 'getAllDataPeralatan']);
Route::get('/eksternal/get-all-data-tenaga-kerja', [EksternalAppController::class, 'getAllTenagaKerja']);

Route::get('/pj-balai/list_user', [UsersController::class, 'listByRoleAndByBalai']);