<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('codigo_login', 100)->unique()->nullable();
            $table->string('password');
            $table->boolean('active')->default(0);
            $table->boolean('disbled_login_by_wrong_pass')->nullable()->default(0);
            $table->boolean('sent_disabled_login')->nullable()->default(0);
            $table->boolean('loged_once')->default(0);
            $table->smallInteger('login_attempts')->default(0);
            $table->unsignedBigInteger('role_id');
            // $table->unsignedBigInteger('utilizador_farmacia_id')->nullable();
            // $table->unsignedBigInteger('utilizador_clinica_id')->nullable();
            // $table->unsignedBigInteger('utilizador_empresa_id')->nullable();
            // $table->unsignedBigInteger('utilizador_administracao_id')->nullable();
            // $table->unsignedBigInteger('tenant_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('role_id')->references('id')->on('roles');
            // $table->foreign('utilizador_farmacia_id')->references('id')->on('utilizador_farmacias');
            // $table->foreign('utilizador_clinica_id')->references('id')->on('utilizador_clinicas');
            // $table->foreign('utilizador_empresa_id')->references('id')->on('utilizador_empresas');
            // $table->foreign('utilizador_administracao_id')->references('id')->on('utilizador_administracaos');
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
        Schema::dropIfExists('users');
    }
}
