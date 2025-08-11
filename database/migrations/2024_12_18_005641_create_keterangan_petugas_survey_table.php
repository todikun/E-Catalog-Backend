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
        Schema::create('keterangan_petugas_survey', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('petugas_lapangan_id')->nullable();
            $table->unsignedBigInteger('pengawas_id')->nullable();
            $table->date('tanggal_survey')->nullable();
            $table->date('tanggal_pengawasan')->nullable();
            $table->string('nama_pemberi_informasi')->nullable();
            $table->unsignedBigInteger('identifikasi_kebutuhan_id')->nullable();
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
        Schema::dropIfExists('keterangan_petugas_survey');
    }
};
