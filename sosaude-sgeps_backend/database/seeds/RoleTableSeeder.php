<?php

use App\Models\Seccao;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seccao_administracao = Seccao::where('code', 1)->first();
        $seccao_empresa       = Seccao::where('code', 2)->first();
        $seccao_farmacia      = Seccao::where('code', 3)->first();
        $seccao_clinica       = Seccao::where('code', 4)->first();

        DB::table('roles')->insert([
            ['role' => 'Administrador',     'codigo' => 1, 'seccao_id' => $seccao_administracao->id],
            ['role' => 'Gestor Farmácia',   'codigo' => 2, 'seccao_id' => $seccao_farmacia->id],
            ['role' => 'Farmaceutico',      'codigo' => 3, 'seccao_id' => $seccao_farmacia->id],
            ['role' => 'Gestor Empresa',    'codigo' => 4, 'seccao_id' => $seccao_empresa->id],
            ['role' => 'Utilizador Comum',  'codigo' => 5, 'seccao_id' => $seccao_empresa->id],
            ['role' => 'Gestor Clinica',    'codigo' => 6, 'seccao_id' => $seccao_clinica->id],
            ['role' => 'Utilizador Comum',  'codigo' => 7, 'seccao_id' => $seccao_clinica->id],
            ['role' => 'Beneficiario',      'codigo' => 8, 'seccao_id' => $seccao_empresa->id],
            ['role' => 'Dependente do Beneficiário',      'codigo' => 9, 'seccao_id' => $seccao_empresa->id],
            
        ]);
    }
}
