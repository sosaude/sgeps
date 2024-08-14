<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissaos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('codigo')->unique();
            $table->string('nome', 100);
            $table->unsignedBigInteger('seccao_id');

            $table->foreign('seccao_id')->references('id')->on('seccaos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permisssaos');
    }
}
