<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItenBaixaFarmaciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('iten_baixa_farmacias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('preco', 18, 2);
            $table->smallInteger('iva');
            $table->decimal('preco_iva', 18, 2);
            $table->integer('quantidade');
            $table->unsignedBigInteger('marca_medicamento_id');
            // $table->unsignedBigInteger('medicamento_id');
            $table->unsignedBigInteger('baixa_farmacia_id');
            $table->timestamps();

            // $table->foreign('medicamento_id')->references('id')->on('medicamentos');
            $table->foreign('marca_medicamento_id')->references('id')->on('marca_medicamentos');
            $table->foreign('baixa_farmacia_id')->references('id')->on('baixa_farmacias');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iten_baixa_farmacias');
    }
}
