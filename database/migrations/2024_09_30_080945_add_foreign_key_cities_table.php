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
        // Schema::table('cities', function (Blueprint $table) {
        //     $table->foreign('provinsi_id')
        //     ->references('id')
        //     ->on('provinces')
        //     ->onDelete('cascade');
        // });

        // Schema::table('accounts', function (Blueprint $table) {
        //     $table->foreign('user_id')
        //     ->references('id')
        //     ->on('users')
        //     ->onDelete('cascade');
        // });

        // Schema::table('users', function (Blueprint $table) {
        //     $table->dropColumn('nip');
        // });
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
