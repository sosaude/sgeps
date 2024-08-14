<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\UnidadeSanitaria;
use App\Models\UtilizadorUnidadeSanitaria;

class UtilizadorUnidadeSanitariaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_gestor_unidade_sanitaria_id = Role::where('codigo', 6)->pluck('id')->first();
        $unidade_sanitaria = UnidadeSanitaria::first();

        $user_unidade_sanitaria = User::create(
            ['nome' => 'Utilizador Clínica 1', 'password' => bcrypt(1234567), 'active' => 1, 'loged_once' => 1, 'login_attempts' => 0, 'role_id' => $role_gestor_unidade_sanitaria_id]
        );
        
        $utilizador_unidade_sanitaria = UtilizadorUnidadeSanitaria::create(
            ['nome' => 'Utilizador Unidade Sanitária 1', 'contacto' => '828282822', 'activo' => 1, 'nacionalidade' => 'Moçambique', 'unidade_sanitaria_id' => $unidade_sanitaria->id, 'user_id' => $user_unidade_sanitaria->id, 'role_id' => $role_gestor_unidade_sanitaria_id]           
        );

        $codigo_login = 'UNIS' . sprintf("%04d", $utilizador_unidade_sanitaria->id);
        
        DB::table('users')
        ->where('id', $user_unidade_sanitaria->id)
        ->update(['codigo_login' => $codigo_login, 'password' => bcrypt(1234567)]);
    }
}
