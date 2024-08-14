<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedicamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo', 100);
            $table->string('dosagem', 100);
            $table->unsignedBigInteger('nome_generico_medicamento_id');
            $table->unsignedBigInteger('forma_medicamento_id');
            $table->unsignedBigInteger('grupo_medicamento_id');
            $table->unsignedBigInteger('sub_grupo_medicamento_id');
            $table->unsignedBigInteger('sub_classe_medicamento_id')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('nome_generico_medicamento_id')->references('id')->on('nome_generico_medicamentos');
            $table->foreign('forma_medicamento_id')->references('id')->on('forma_medicamentos');
            $table->foreign('grupo_medicamento_id')->references('id')->on('grupo_medicamentos');
            $table->foreign('sub_grupo_medicamento_id')->references('id')->on('sub_grupo_medicamentos');
            $table->foreign('sub_classe_medicamento_id')->references('id')->on('sub_classe_medicamentos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medicamentos');
    }
}
