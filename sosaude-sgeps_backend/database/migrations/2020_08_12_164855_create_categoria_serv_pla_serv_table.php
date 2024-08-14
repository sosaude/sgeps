<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriaServPlaServTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categoria_serv_pla_serv', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('categoria_servico_plano_id');
            $table->unsignedBigInteger('servico_id');
            $table->boolean('coberto')->default(false);
            $table->boolean('pre_autorizacao')->default(false);

            $table->foreign('categoria_servico_plano_id')->references('id')->on('categoria_servico_planos');
            $table->foreign('servico_id')->references('id')->on('servicos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categoria_serv_pla_serv');
    }
}
