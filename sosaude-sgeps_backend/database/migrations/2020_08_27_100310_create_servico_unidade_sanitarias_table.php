<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicoUnidadeSanitariasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servico_unidade_sanitarias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('preco', 18, 2);
            $table->smallInteger('iva')->default(0);            
            $table->decimal('preco_iva', 18, 2);
            $table->unsignedBigInteger('servico_id');
            $table->unsignedBigInteger('unidade_sanitaria_id');
            $table->timestamps();

            $table->foreign('servico_id')->references('id')->on('servicos');
            $table->foreign('unidade_sanitaria_id')->references('id')->on('unidade_sanitarias');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servico_unidade_sanitarias');
    }
}
