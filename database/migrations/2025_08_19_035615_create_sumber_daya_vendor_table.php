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
        Schema::create('sumber_daya_vendor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('data_vendor_id')->nullable();
            $table->string('jenis')->nullable();
            $table->string('nama')->nullable();
            $table->string('spesifikasi')->nullable();
            $table->timestamps();

            $table->foreign('data_vendor_id')->references('id')->on('data_vendors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sumber_daya_vendor');
    }
};
