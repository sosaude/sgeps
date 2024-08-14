<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrupoMedicamentoPlanosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grupo_medicamento_planos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('comparticipacao_factura')->default(false);
            $table->boolean('sujeito_limite_global')->default(false);
            $table->boolean('beneficio_ilimitado')->default(false);
            $table->double('valor_comparticipacao_factura')->nullable();
            $table->double('valor_beneficio_limitado')->nullable();
            $table->unsignedBigInteger('plano_saude_id');
            $table->unsignedBigInteger('grupo_medicamento_id');

            $table->foreign('plano_saude_id')->references('id')->on('plano_saudes');
            $table->foreign('grupo_medicamento_id')->references('id')->on('grupo_medicamentos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grupo_medicamento_planos');
    }
}
