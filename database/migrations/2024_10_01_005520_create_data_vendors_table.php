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
        Schema::create('data_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('nama_vendor');
            $table->json('jenis_vendor_id');
            $table->json('kategori_vendor_id');
            $table->text('alamat');
            $table->string('no_telepon');
            $table->string('no_hp');
            $table->string('nama_pic');
            $table->unsignedBigInteger('provinsi_id');
            $table->unsignedBigInteger('kota_id');
            $table->string('koordinat');
            $table->string('logo_url');
            $table->string('dok_pendukung_url');
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
        Schema::dropIfExists('data_vendors');
    }
};
