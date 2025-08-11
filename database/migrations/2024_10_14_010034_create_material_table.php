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
        Schema::create('material', function (Blueprint $table) {
            $table->id();
            $table->string('nama_material');
            $table->string('satuan');
            $table->string('spesifikasi');
            $table->string('ukuran');
            $table->string('kodefikasi');
            $table->string('kelompok_material');
            $table->string('jumlah_kebutuhan');
            $table->string('merk');
            $table->unsignedBigInteger('provincies_id');
            $table->unsignedBigInteger('cities_id');
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
        Schema::dropIfExists('material');
    }
};
