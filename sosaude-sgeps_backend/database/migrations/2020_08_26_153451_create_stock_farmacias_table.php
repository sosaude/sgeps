<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockFarmaciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_farmacias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('preco', 18, 2);
            $table->smallInteger('iva')->default(0);
            $table->decimal('preco_iva', 18, 2);
            $table->unsignedBigInteger('quantidade_disponivel');
            $table->unsignedBigInteger('medicamento_id')->nullable();
            $table->unsignedBigInteger('marca_medicamento_id');
            $table->unsignedBigInteger('farmacia_id');
            $table->timestamps();

            $table->foreign('medicamento_id')->references('id')->on('medicamentos');
            $table->foreign('marca_medicamento_id')->references('id')->on('marca_medicamentos');
            $table->foreign('farmacia_id')->references('id')->on('farmacias');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_farmacias');
    }
}
