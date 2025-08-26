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
        Schema::table('satuan_balai_kerja', function (Blueprint $table) {

            // TODO: 1. Ubah column menjadi nullable
            $table->unsignedBigInteger('unor_id')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('satuan_balai_kerja', function (Blueprint $table) {
            // TODO: 1. Ubah column menjadi not null
            $table->unsignedBigInteger('unor_id')->nullable(false)->change();
        });
    }
};
