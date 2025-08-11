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
        Schema::create('material_survey', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('material_id')->nullable();
            $table->string('satuan_setempat')->nullable();
            $table->bigInteger('satuan_setempat_panjang')->nullable();
            $table->bigInteger('satuan_setempat_lebar')->nullable();
            $table->bigInteger('satuan_setempat_tinggi')->nullable();
            $table->string('konversi_satuan_setempat')->nullable();
            $table->bigInteger('harga_satuan_setempat')->nullable();
            $table->bigInteger('harga_konversi_satuan_setempat')->nullable();
            $table->bigInteger('harga_khusus')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('peralatan_survey', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('peralatan_id')->nullable();
            $table->string('satuan_setempat')->nullable();
            $table->bigInteger('harga_sewa_satuan_setempat')->nullable();
            $table->bigInteger('harga_sewa_konversi')->nullable();
            $table->bigInteger('harga_pokok')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('tenaga_kerja_survey', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenaga_kerja_id')->nullable();
            $table->string('harga_per_satuan_setempat')->nullable();
            $table->string('harga_konversi_perjam')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('survey_kuisoner');
    }
};
