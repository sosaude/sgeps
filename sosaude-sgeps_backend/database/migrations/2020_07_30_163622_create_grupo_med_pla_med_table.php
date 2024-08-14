<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrupoMedPlaMedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grupo_med_pla_med', function (Blueprint $table) { // grupo_med_pla_med => grupo_medicamento_plano_medicamento
            $table->bigIncrements('id');
            $table->unsignedBigInteger('grupo_medicamento_plano_id');
            $table->unsignedBigInteger('medicamento_id');
            $table->boolean('coberto')->default(false);
            $table->boolean('pre_autorizacao')->default(false);

            $table->foreign('grupo_medicamento_plano_id')->references('id')->on('grupo_medicamento_planos');
            $table->foreign('medicamento_id')->references('id')->on('medicamentos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grupo_med_pla_med');
    }
}
