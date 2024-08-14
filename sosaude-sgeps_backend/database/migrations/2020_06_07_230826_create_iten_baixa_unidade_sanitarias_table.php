<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItenBaixaUnidadeSanitariasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('iten_baixa_unidade_sanitarias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('preco', 18, 2);
            $table->smallInteger('iva');
            $table->decimal('preco_iva', 18, 2);
            $table->integer('quantidade');
            $table->unsignedBigInteger('servico_id');
            $table->unsignedBigInteger('baixa_unidade_sanitaria_id');
            $table->timestamps();

            $table->foreign('servico_id')->references('id')->on('servicos');
            $table->foreign('baixa_unidade_sanitaria_id')->references('id')->on('baixa_unidade_sanitarias');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iten_baixa_unidade_sanitarias');
    }
}
