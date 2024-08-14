<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBeneficiariosTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beneficiarios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('activo')->default(false);
            $table->string('nome', 100);
            $table->string('numero_identificacao', 50)->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('numero_beneficiario', 100)->nullable();
            $table->string('endereco');
            $table->string('bairro', 100);
            $table->string('telefone', 50)->nullable();
            $table->char('genero', 1);
            $table->date('data_nascimento');
            $table->string('ocupacao', 100);
            $table->boolean('aposentado')->default(0);
            $table->boolean('tem_dependentes')->default(0);
            $table->boolean('doenca_cronica')->default(0);            
            $table->string('doenca_cronica_nome')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('grupo_beneficiario_id');
            $table->unsignedBigInteger('user_id');
            // $table->unsignedBigInteger('tenant_id');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('grupo_beneficiario_id')->references('id')->on('grupo_beneficiarios');
            $table->foreign('user_id')->references('id')->on('users');
            // $table->foreign('tenant_id')->references('id')->on('tenants');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('beneficiarios');
    }
}
