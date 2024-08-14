<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriaServicoPlanosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categoria_servico_planos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('comparticipacao_factura')->default(false);
            $table->boolean('sujeito_limite_global')->default(false);
            $table->boolean('beneficio_ilimitado')->default(false);
            $table->double('valor_comparticipacao_factura')->nullable();
            $table->double('valor_beneficio_limitado')->nullable();
            $table->unsignedBigInteger('plano_saude_id');
            $table->unsignedBigInteger('categoria_servico_id');

            $table->foreign('plano_saude_id')->references('id')->on('plano_saudes');
            $table->foreign('categoria_servico_id')->references('id')->on('categoria_servicos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categoria_servico_planos');
    }
}
