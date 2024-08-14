<?php

use App\Models\Clinica;
use App\Models\Empresa;
use App\Models\Farmacia;
use App\Models\Administracao;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $administracao = Administracao::first();
        // $farmacia = Farmacia::first();
        // $clinica = Clinica::first();
        // $empresa = Empresa::first();

        /* DB::table('tenants')->insert([
            // ['nome' => $administracao->nome, 'administracao_id' => $administracao->id],
            // ['nome' => $farmacia->nome, 'farmacia_id' => $farmacia->id, 'clinica_id' => null, 'administracao_id' => null],
            // ['nome' => $clinica->nome, 'clinica_id' => $clinica->id, 'administracao_id' => null],
            // ['nome' => $empresa->nome, 'farmacia_id' => null, 'clinica_id' => null, 'empresa_id' => $empresa->id, 'administracao_id' => null],
        ]); */
    }
}
