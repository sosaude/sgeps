<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFarmaciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('farmacias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome');
            $table->string('email')->nullable()->unique();
            $table->string('endereco');
            $table->text('horario_funcionamento');
            $table->boolean('activa');
            $table->string('contactos');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('numero_alvara');
            $table->date('data_alvara_emissao')->nullable();
            $table->string('observacoes')->nullable();
            $table->unsignedBigInteger('tenant_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants');

            $table->index('nome');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('farmacias');
    }
}
