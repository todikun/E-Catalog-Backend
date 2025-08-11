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
        // Schema::table('users', function (Blueprint $table) {
        //     $table->foreign('satuan_kerja_id')
        //     ->references('id')
        //     ->on('satuan_kerja')
        //     ->onDelete('cascade');

        //     $table->foreign('balai_kerja_id')
        //     ->references('id')
        //     ->on('satuan_balai_kerja')
        //     ->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('users', function (Blueprint $table) {
        //     $table->dropForeign(['satuan_kerja_id']);
        //     $table->dropForeign(['balai_kerja_id']);
        // });
    }
};
