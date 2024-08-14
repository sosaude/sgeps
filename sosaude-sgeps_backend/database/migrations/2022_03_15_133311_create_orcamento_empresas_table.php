<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrcamentoEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orcamento_empresas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('orcamento_laboratorio', 18, 2)->nullable();
            $table->decimal('orcamento_farmacia', 18, 2)->nullable();
            $table->decimal('orcamento_clinica', 18, 2)->nullable();
            $table->string('tipo_orcamento')->nullable();
            $table->boolean('executado')->nullable()->default(false);
            $table->string('ano_de_referencia')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('empresa_id')->references('id')->on('empresas');
        });
    }

    /**
     * Reverse the migrations.
     *  
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orcamento_empresas');
    }
}
