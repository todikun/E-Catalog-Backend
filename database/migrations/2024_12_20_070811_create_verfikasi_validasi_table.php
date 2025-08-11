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
        Schema::create('verfikasi_validasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('data_vendor_id')->nullable();
            $table->unsignedBigInteger('shortlist_vendor_id')->nullable();
            $table->string('item_number')->nullable();
            $table->string('status_pemeriksaan')->nullable();
            $table->string('verified_by')->nullable();
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
        Schema::dropIfExists('verfikasi_validasi');
    }
};
