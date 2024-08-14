<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarcaMedicamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marca_medicamentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('marca');
            $table->string('codigo', 100);
            // $table->string('dosagem', 100);
            $table->string('pais_origem', 100);
            $table->unsignedBigInteger('medicamento_id');
            // $table->unsignedBigInteger('forma_marca_medicamento_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('medicamento_id')->references('id')->on('medicamentos');
            // $table->foreign('forma_marca_medicamento_id')->references('id')->on('forma_marca_medicamentos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marca_medicamentos');
    }
}
