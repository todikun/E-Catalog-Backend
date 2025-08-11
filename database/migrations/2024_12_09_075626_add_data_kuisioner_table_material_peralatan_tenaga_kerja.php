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
            $table->string('satuan_setempat')->nullable();
            $table->decimal('satuan_setempat_panjang')->nullable();
            $table->decimal('satuan_setempat_lebar')->nullable();
            $table->decimal('satuan_setempat_tinggi')->nullable();
            $table->string('konversi_satuan_setempat')->nullable();
            $table->decimal('harga_satuan_setempat')->nullable();
            $table->decimal('harga_konversi_satuan_setempat')->nullable();
            $table->decimal('harga_khusus')->nullable();
            $table->string('keterangan')->nullable();
        });

        Schema::table('peralatan', function (Blueprint $table) {
            $table->string('satuan_setempat')->nullable();
            $table->decimal('harga_sewa_satuan_setempat')->nullable();
            $table->decimal('harga_sewa_konversi')->nullable();
            $table->decimal('harga_pokok')->nullable();
            $table->string('keterangan')->nullable();
        });

        Schema::table('tenaga_kerja', function (Blueprint $table) {
            $table->decimal('harga_per_satuan_setempat')->nullable();
            $table->decimal('harga_konversi_perjam')->nullable();
            $table->string('keterangan')->nullable();
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
