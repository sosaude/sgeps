<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBaixaFarmaciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baixa_farmacias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('valor', 8, 2)->nullable();
            $table->text('responsavel')->nullable();
            // $table->tinyInteger('estado')->nullable();
            $table->tinyInteger('proveniencia');
            $table->string('comprovativo')->nullable();
            $table->string('nr_comprovativo', 100)->nullable();
            $table->timestamp('data_criacao_pedido_aprovacao')->nullable();
            $table->timestamp('data_aprovacao_pedido_aprovacao')->nullable();
            $table->string('resposavel_aprovacao_pedido_aprovacao', 100)->nullable();
            $table->text('comentario_pedido_aprovacao')->nullable();
            $table->text('comentario_baixa')->nullable();
            $table->boolean('beneficio_proprio_beneficiario')->default(true);
            $table->unsignedBigInteger('beneficiario_id');
            $table->unsignedBigInteger('dependente_beneficiario_id')->nullable();
            $table->unsignedBigInteger('farmacia_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('estado_baixa_id');
            $table->timestamps();

            $table->foreign('beneficiario_id')->references('id')->on('beneficiarios');
            $table->foreign('dependente_beneficiario_id')->references('id')->on('dependente_beneficiarios');
            $table->foreign('farmacia_id')->references('id')->on('farmacias');
            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('estado_baixa_id')->references('id')->on('estado_baixas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('baixa_farmacias');
    }
}
