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
        Schema::create('informasi_umum', function (Blueprint $table) {
            $table->id();
            $table->string('kode_rup')->nullable();
            $table->string('nama_paket');
            $table->string('nama_ppk');
            $table->string('jabatan_ppk');
            $table->string('nama_balai')->nullable();
            $table->string('tipologi')->nullable();
            $table->enum('jenis_informasi', ['sipasti', 'manual']);
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
        Schema::dropIfExists('informasi_umum');
    }
};
