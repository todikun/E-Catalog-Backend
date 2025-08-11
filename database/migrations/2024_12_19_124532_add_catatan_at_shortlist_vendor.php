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
            $table->text('catatan_blok_1')->nullable();
            $table->text('catatan_blok_2')->nullable();
            $table->text('catatan_blok_3')->nullable();
            $table->text('catatan_blok_4')->nullable();
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
