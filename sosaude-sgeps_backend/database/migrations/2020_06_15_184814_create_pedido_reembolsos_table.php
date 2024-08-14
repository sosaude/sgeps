<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoReembolsosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_reembolsos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('unidade_sanitaria');
            $table->string('servico_prestado');
            $table->string('nr_comprovativo');
            $table->text('responsavel')->nullable();
            $table->decimal('valor', 8, 2);
            $table->date('data');
            $table->string('comprovativo')->nullable();
            // $table->tinyInteger('estado');
            $table->text('comentario')->nullable();
            $table->boolean('beneficio_proprio_beneficiario')->default(true);
            $table->unsignedBigInteger('beneficiario_id')->nullable();
            $table->unsignedBigInteger('dependente_beneficiario_id')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('estado_pedido_reembolso_id');
            $table->timestamps();

            $table->foreign('beneficiario_id')->references('id')->on('beneficiarios');
            $table->foreign('dependente_beneficiario_id')->references('id')->on('dependente_beneficiarios');
            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('estado_pedido_reembolso_id')->references('id')->on('estado_pedido_reembolsos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedido_reembolsos');
    }
}
