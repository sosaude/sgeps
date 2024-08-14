<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanoSaudesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plano_saudes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('beneficio_anual_segurando_limitado')->default(false);
            $table->decimal('valor_limite_anual_segurando', 18, 2)->nullable();
            $table->boolean('limite_fora_area_cobertura')->default(false);
            $table->decimal('valor_limite_fora_area_cobertura', 18, 2)->nullable();
            $table->text('regiao_cobertura');
            $table->unsignedBigInteger('grupo_beneficiario_id')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();

            $table->foreign('grupo_beneficiario_id')->references('id')->on('grupo_beneficiarios');
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
        Schema::dropIfExists('plano_saudes');
    }
}
