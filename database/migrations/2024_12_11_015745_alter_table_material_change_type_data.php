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
        Schema::table('material', function (Blueprint $table) {
            $table->bigInteger('satuan_setempat_panjang')->nullable()->change();
            $table->bigInteger('satuan_setempat_lebar')->nullable()->change();
            $table->bigInteger('satuan_setempat_tinggi')->nullable()->change();
            $table->bigInteger('harga_satuan_setempat')->nullable()->change();
            $table->bigInteger('harga_konversi_satuan_setempat')->nullable()->change();
            $table->bigInteger('harga_khusus')->nullable()->change();
        });

        Schema::table('peralatan', function (Blueprint $table) {
            $table->bigInteger('harga_sewa_satuan_setempat')->nullable()->change();
            $table->bigInteger('harga_sewa_konversi')->nullable()->change();
            $table->bigInteger('harga_pokok')->nullable()->change();
        });

        Schema::table('tenaga_kerja', function (Blueprint $table) {
            $table->bigInteger('harga_per_satuan_setempat')->nullable()->change();
            $table->bigInteger('harga_konversi_perjam')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
