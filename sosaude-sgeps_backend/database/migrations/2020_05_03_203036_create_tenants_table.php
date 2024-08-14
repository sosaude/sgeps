<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome');
            // $table->unsignedBigInteger('farmacia_id')->nullable();
            // $table->unsignedBigInteger('clinica_id')->nullable();
            // $table->unsignedBigInteger('empresa_id')->nullable();
            // $table->unsignedBigInteger('administracao_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // $table->foreign('administracao_id')->references('id')->on('administracaos');
            // $table->foreign('farmacia_id')->references('id')->on('farmacias');
            // $table->foreign('clinica_id')->references('id')->on('clinicas');
            // $table->foreign('empresa_id')->references('id')->on('empresas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenants');
    }
}
