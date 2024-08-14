<?php

use App\Models\Administracao;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UtilizadorAdministracaoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $administracao = Administracao::first();
        $role_admin_id = Role::where('codigo', 1)->pluck('id')->first();

        $user_1 = User::create(
            ['nome' => 'Marrar LDA', 'email' => 'marrarteste@gmail.com', 'password' => bcrypt('ifarmacias'), 'active' => 1, 'loged_once' => 1, 'login_attempts' => 0, 'role_id' => $role_admin_id]
        );

        $user_2 = User::create(
            ['nome' => 'Osorio Malache', 'email' => 'osoriocassiano@gmail.com', 'password' => bcrypt('ifarmacias'), 'active' => 1, 'loged_once' => 1, 'login_attempts' => 0, 'role_id' => $role_admin_id]
        );

        $user_3 = User::create(
            ['nome' => 'Virgilio Mutondo', 'email' => 'v.mutondo@gmail.com', 'password' => bcrypt('ifarmacias'), 'active' => 1, 'loged_once' => 1, 'login_attempts' => 0, 'role_id' => $role_admin_id]
        );

        $user_4 = User::create(
            ['nome' => 'Manuel de Azevedo', 'email' => 'manuel.azevedoteste@marrar.co.mz', 'password' => bcrypt('ifarmacias'), 'active' => 1, 'loged_once' => 1, 'login_attempts' => 0, 'role_id' => $role_admin_id]
        );

        $user_5 = User::create(
            ['nome' => 'Rui Ceita', 'email' => 'ruijceitateste@gmail.com', 'password' => bcrypt('ifarmacias'), 'active' => 1, 'loged_once' => 1, 'login_attempts' => 0, 'role_id' => $role_admin_id]
        );
        
        DB::table('utilizador_administracaos')->insert([
            ['nome' => 'Marrar LDA', 'contacto' => '828282822', 'email' => 'marrarteste@gmail.com', 'activo' => 1, 'administracao_id' => $administracao->id, 'role_id' => $role_admin_id, 'user_id' => $user_1->id],         
            ['nome' => 'Osorio Malache', 'contacto' => '828282822', 'email' => 'osoriocassiano@gmail.com', 'activo' => 1, 'administracao_id' => $administracao->id, 'role_id' => $role_admin_id, 'user_id' => $user_2->id],            
            ['nome' => 'Virgilio Mutondo', 'contacto' => '828282822', 'email' => 'v.mutondo@gmail.com', 'activo' => 1, 'administracao_id' => $administracao->id, 'role_id' => $role_admin_id, 'user_id' => $user_3->id],           
            ['nome' => 'Manuel de Azevedo', 'contacto' => '828282822', 'email' => 'manuel.azevedoteste@marrar.co.mz', 'activo' => 1, 'administracao_id' => $administracao->id, 'role_id' => $role_admin_id, 'user_id' => $user_4->id],            
            ['nome' => 'Rui Ceita', 'contacto' => '828282822', 'email' => 'ruijceitateste@gmail.com', 'activo' => 1, 'administracao_id' => $administracao->id, 'role_id' => $role_admin_id, 'user_id' => $user_5->id]            
        ]);

        DB::table('users')
        ->where('email', 'marrarteste@gmail.com')
        ->update(['password' => bcrypt(1234567)]);

        DB::table('users')
        ->where('email', 'osoriocassiano@gmail.com')
        ->update(['password' => bcrypt(1234567)]);

        DB::table('users')
        ->where('email', 'v.mutondo@gmail.com')
        ->update(['password' => bcrypt(1234567)]);

        DB::table('users')
        ->where('email', 'manuel.azevedoteste@marrar.co.mz')
        ->update(['password' => bcrypt(1234567)]);

        DB::table('users')
        ->where('email', 'ruijceitateste@gmail.com')
        ->update(['password' => bcrypt(1234567)]);
    }
}
