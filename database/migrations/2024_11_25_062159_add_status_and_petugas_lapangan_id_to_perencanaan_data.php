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
        Schema::table('perencanaan_data', function (Blueprint $table) {
            //$table->string('status')->nullable();
            $table->unsignedBigInteger('team_teknis_balai_id')->nullable();
            $table->json('pengawas_id')->nullable();
            $table->json('petugas_lapangan_id')->nullable();
            $table->json('pengolah_data_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('perencanaan_data', function (Blueprint $table) {
            //
        });
    }
};
