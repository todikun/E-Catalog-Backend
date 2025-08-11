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
        Schema::create('shortlist_vendor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('data_vendor_id');
            $table->unsignedBigInteger('shortlist_vendor_id');
            $table->string('nama_vendor');
            $table->string('pemilik_vendor');
            $table->string('alamat');
            $table->string('kontak');
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
        Schema::dropIfExists('shortlist_vendor');
    }
};
