<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('data_vendors', function (Blueprint $table) {
        //     $table->dropForeign(['jenis_vendor_id']);
        //     $table->dropForeign(['kategori_vendor_id']);
        //     $table->dropForeign(['provinsi_id']);
        //     $table->dropForeign(['kota_id']);
        // });

        // Schema::table('data_vendors', function (Blueprint $table) {
        //     $table->dropColumn('jenis_vendor_id');
        // });

        // Schema::table('data_vendors', function (Blueprint $table) {
        //     $table->dropColumn('kategori_vendor_id');
        // });

        // Schema::table('data_vendors', function (Blueprint $table) {
        //     $table->json('jenis_vendor_id')->after('nama_vendor');
        // });

        // Schema::table('data_vendors', function (Blueprint $table) {
        //     $table->json('kategori_vendor_id')->after('jenis_vendor_id');
        // });

        Schema::table('material', function (Blueprint $table) {
            $table->string('identifikasi_kebutuhan_id')->after('id')->nullable();
        });

        Schema::table('peralatan', function (Blueprint $table) {
            $table->string('identifikasi_kebutuhan_id')->after('id')->nullable();
        });

        Schema::table('tenaga_kerja', function (Blueprint $table) {
            $table->string('identifikasi_kebutuhan_id')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_vendors', function (Blueprint $table) {
            $table->dropColumn('jenis_vendor_id');
        });

        Schema::table('data_vendors', function (Blueprint $table) {
            $table->unsignedBigInteger('jenis_vendor_id')->after('nama_vendor');
        });
    }
};
