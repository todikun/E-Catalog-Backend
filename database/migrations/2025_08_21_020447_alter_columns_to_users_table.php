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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('balai_kerja_id')->nullable()->change();
            $table->unsignedBigInteger('satuan_kerja_id')->nullable()->change();
            $table->string('no_handphone')->nullable()->change();
            $table->string('nik')->nullable()->change();
            $table->string('nrp')->nullable()->change();
            $table->string('surat_penugasan_url')->nullable()->change();
            $table->string('nip')->nullable()->change();

            $table->uuid('user_id_sipasti')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('balai_kerja_id')->nullable(false)->change();
            $table->unsignedBigInteger('satuan_kerja_id')->nullable(false)->change();
            $table->string('no_handphone')->nullable(false)->change();
            $table->string('nik')->nullable(false)->change();
            $table->string('nrp')->nullable(false)->change();
            $table->string('surat_penugasan_url')->nullable(false)->change();
            $table->string('nip')->nullable(false)->change();

            $table->dropIndex(['user_id_sipasti']);
            $table->dropColumn('user_id_sipasti');
        });
    }
};
