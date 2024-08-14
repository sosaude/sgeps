<?php

use App\Models\UtilizadorAdministracao;
use App\Models\UtilizadorClinica;
use App\Models\UtilizadorEmpresa;
use App\Models\UtilizadorFarmacia;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin1 = UtilizadorAdministracao::where('email', 'marrarteste@gmail.com')->first();
        $admin2 = UtilizadorAdministracao::where('email', 'osoriocassiano@gmail.com')->first();
        $admin3 = UtilizadorAdministracao::where('email', 'v.mutondo@gmail.com')->first();
        $admin4 = UtilizadorAdministracao::where('email', 'manuel.azevedo@marrar.co.mz')->first();
        $admin5 = UtilizadorAdministracao::where('email', 'ruijceita@gmail.com')->first();

        // Utilizadores Farmacia, Clinica, Empresa
        $utilizador_farmacia = UtilizadorFarmacia::first();
        $utilizador_clinica = UtilizadorClinica::first();
        $utilizador_empresa = UtilizadorEmpresa::first();

        DB::table('users')->insert([
            [
                'nome' => $admin1->nome,
                'email' => $admin1->email,
                'codigo_login' => null,
                'password' => bcrypt(1234567),
                'role_id' => $admin1->role_id,
                'active' => 1,
                'loged_once' => 1,
                'utilizador_administracao_id' => $admin1->id,
                'utilizador_empresa_id' => null,
                'utilizador_clinica_id' => null,
                'utilizador_farmacia_id' => null,
                // 'tenant_id' => $admin1->tenant_id,
            ],
            [
                'nome' => $admin2->nome,
                'email' => $admin2->email,
                'codigo_login' => null,
                'password' => bcrypt(1234567),
                'role_id' => $admin2->role_id,
                'active' => 1,
                'loged_once' => 1,
                'utilizador_administracao_id' => $admin2->id,
                'utilizador_empresa_id' => null,
                'utilizador_clinica_id' => null,
                'utilizador_farmacia_id' => null,
                // 'tenant_id' => $admin2->tenant_id,
            ],
            [
                'nome' => $admin3->nome,
                'email' => $admin3->email,
                'codigo_login' => null,
                'password' => bcrypt(1234567),
                'role_id' => $admin3->role_id,
                'active' => 1,
                'loged_once' => 1,
                'utilizador_administracao_id' => $admin3->id,
                'utilizador_empresa_id' => null,
                'utilizador_clinica_id' => null,
                'utilizador_farmacia_id' => null,
                // 'tenant_id' => $admin3->tenant_id,
            ],
            [
                'nome' => $admin4->nome,
                'email' => $admin4->email,
                'codigo_login' => null,
                'password' => bcrypt(1234567),
                'role_id' => $admin4->role_id,
                'active' => 1,
                'loged_once' => 1,
                'utilizador_administracao_id' => $admin4->id,
                'utilizador_empresa_id' => null,
                'utilizador_clinica_id' => null,
                'utilizador_farmacia_id' => null,
                // 'tenant_id' => $admin4->tenant_id,
            ],
            [
                'nome' => $admin5->nome,
                'email' => $admin5->email,
                'codigo_login' => null,
                'password' => bcrypt(1234567),
                'role_id' => $admin5->role_id,
                'active' => 1,
                'loged_once' => 1,
                'utilizador_administracao_id' => $admin5->id,
                'utilizador_empresa_id' => null,
                'utilizador_clinica_id' => null,
                'utilizador_farmacia_id' => null,
                // 'tenant_id' => $admin5->tenant_id,
            ],
            // Utilizador Farmacia
            [
                'nome' => $utilizador_farmacia->nome,
                'email' => null,
                'codigo_login' => 'FAR' . sprintf("%04d", $utilizador_farmacia->id),
                'password' => bcrypt(1234567),
                'role_id' => $utilizador_farmacia->role_id,
                'active' => 1,
                'loged_once' => 1,
                'utilizador_farmacia_id' => $utilizador_farmacia->id,
                'utilizador_administracao_id' => null,
                'utilizador_empresa_id' => null,
                'utilizador_clinica_id' => null,
                // 'tenant_id' => $utilizador_farmacia->tenant_id,
            ],
            // Utilizador Clinica
            [
                'nome' => $utilizador_clinica->nome,
                'email' => null,
                'codigo_login' => 'CLI' . sprintf("%04d", $utilizador_clinica->id),
                'password' => bcrypt(1234567),
                'role_id' => $utilizador_clinica->role_id,
                'active' => 1,
                'loged_once' => 1,
                'utilizador_clinica_id' => $utilizador_clinica->id,
                'utilizador_administracao_id' => null,
                'utilizador_empresa_id' => null,
                'utilizador_farmacia_id' => null,
                // 'tenant_id' => $utilizador_clinica->tenant_id,
            ],
            // Utilizador Empresa
            [
                'nome' => $utilizador_empresa->nome,
                'email' => null,
                'codigo_login' => 'EMP' . sprintf("%04d", $utilizador_empresa->id),
                'password' => bcrypt(1234567),
                'role_id' => $utilizador_empresa->role_id,
                'active' => 1,
                'loged_once' => 1,
                'utilizador_empresa_id' => $utilizador_empresa->id,
                'utilizador_administracao_id' => null,
                'utilizador_farmacia_id' => null,
                'utilizador_clinica_id' => null,
                // 'tenant_id' => $utilizador_empresa->tenant_id,
            ],

        ]);
    }
}
