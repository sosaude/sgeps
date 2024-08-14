<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUtilizadorFarmaciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('utilizador_farmacias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome', 100);
            $table->string('email')->nullable();
            $table->boolean('email_verificado')->default(false);
            $table->string('contacto', 100)->nullable();
            $table->integer('numero_caderneta')->nullable();
            $table->boolean('activo')->default(0);
            $table->string('categoria_profissional', 100)->nullable();
            $table->string('nacionalidade', 100);
            $table->string('observacoes')->nullable();
            $table->unsignedBigInteger('farmacia_id');
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');
            // $table->unsignedBigInteger('tenant_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('farmacia_id')->references('id')->on('farmacias');
            $table->foreign('role_id')->references('id')->on('roles');
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
        Schema::dropIfExists('utilizador_farmacias');
    }
}
