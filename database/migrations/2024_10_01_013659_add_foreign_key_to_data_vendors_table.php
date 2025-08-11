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
        //     $table->foreign('jenis_vendor_id')
        //     ->references('id')
        //     ->on('jenis_vendors')
        //     ->onDelete('cascade');

        //     $table->foreign('kategori_vendor_id')
        //     ->references('id')
        //     ->on('kategori_vendors')
        //     ->onDelete('cascade');
            
        //     $table->foreign('provinsi_id')
        //     ->references('id')
        //     ->on('provinces')
        //     ->onDelete('cascade');

        //     $table->foreign('kota_id')
        //     ->references('id')
        //     ->on('cities')
        //     ->onDelete('cascade');

        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('data_vendor', function (Blueprint $table) {
        //     //
        // });
    }
};
