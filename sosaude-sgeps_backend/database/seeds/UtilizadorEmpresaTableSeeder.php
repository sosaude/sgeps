<?php

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Permissao;
use Illuminate\Database\Seeder;
use App\Models\UtilizadorEmpresa;

class UtilizadorEmpresaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_gestor_empresa_id = Role::where('codigo', 4)->pluck('id')->first();
        $permissoes = Permissao::where('seccao_id', '2')->pluck('id');
        $empresa = Empresa::first();

        /* $user_empresa = User::create(
            ['nome' => 'Utilizador Empresa 1', 'password' => bcrypt('ifarmacias'), 'active' => 1, 'loged_once' => 1, 'login_attempts' => 0, 'role_id' => $role_gestor_empresa_id]
        ); */
        $user_empresa = User::create(
            ['nome' => 'Utilizador Empresa 1', 'password' => bcrypt('ifarmacias'), 'active' => 1, 'loged_once' => 1, 'login_attempts' => 0, 'role_id' => $role_gestor_empresa_id]
        );
        
        $utilizador_empresa = UtilizadorEmpresa::create(
            ['nome' => 'Utilizador Empresa 1', 'contacto' => '828282822', 'activo' => 1, 'nacionalidade' => 'Moçambique', 'empresa_id' => $empresa->id, 'user_id'=>$user_empresa->id, 'role_id' => $role_gestor_empresa_id]           
        );

        $codigo_login = 'EMP' . sprintf("%04d", $utilizador_empresa->id);

        
        DB::table('users')
        ->where('id', $user_empresa->id)
        ->update(['codigo_login' => $codigo_login, 'password' => bcrypt(1234567)]);

        $user_empresa->permissaos()->detach();
        $user_empresa->permissaos()->attach($permissoes);
    }
}
