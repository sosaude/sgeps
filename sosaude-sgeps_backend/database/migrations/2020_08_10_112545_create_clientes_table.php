<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome');
            $table->float('peso')->nullable();
            // $table->float('altura')->nullable(); severino asked
            $table->smallInteger('altura')->nullable();
            $table->boolean('e_benefiairio_plano_saude')->nullable()->default(false);
            $table->boolean('tem_doenca_cronica')->nullable()->default(false);
            $table->string('doenca_cronica_nome')->nullable()->default("[]");
            $table->string('tipo_sanguineo')->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('activo')->default(true);
            $table->boolean('logado_uma_vez')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('foto_perfil')->nullable();
            $table->string('foto_documento')->nullable();
            $table->unsignedBigInteger('beneficiario_id')->nullable();
            $table->unsignedBigInteger('dependente_beneficiario_id')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('beneficiario_id')->references('id')->on('beneficiarios');
            $table->foreign('dependente_beneficiario_id')->references('id')->on('dependente_beneficiarios');

            $table->index('tipo_sanguineo');
            $table->index('provincia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clientes');
    }
}
