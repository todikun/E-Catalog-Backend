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
        Schema::table('shortlist_vendor', function (Blueprint $table) {
            $table->unsignedBigInteger('petugas_lapangan_id')->nullable();
            $table->unsignedBigInteger('pengawas_id')->nullable();
            $table->string('nama_pemberi_informasi')->nullable();
            $table->date('tanggal_survei')->nullable();
            $table->date('tanggal_pengawasan')->nullable();
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
