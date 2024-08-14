<?php

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Farmacia;
use App\Models\UtilizadorFarmacia;
use Illuminate\Database\Seeder;

class UtilizadorFarmaciaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_gestor_farmacia_id = Role::where('codigo', 2)->pluck('id')->first();
        $farmacia = Farmacia::first();
        
        /* $user_farmacia = User::create(
            ['nome' => 'Utilizador Farmacia 1', 'password' => bcrypt('ifarmacias'), 'active' => 1, 'loged_once' => 1, 'login_attempts' => 0, 'role_id' => $role_gestor_farmacia_id]
        ); */
        $user_farmacia = User::create(
            ['nome' => 'Utilizador Farmacia 1', 'password' => bcrypt('ifarmacias'), 'active' => 1, 'loged_once' => 1, 'login_attempts' => 0, 'role_id' => $role_gestor_farmacia_id]
        );

        /* $utilizador_farmacia = UtilizadorFarmacia:: create(
            ['nome' => 'Utilizador Farmacia 1', 'contacto' => '828282822', 'numero_caderneta' => 1234546, 'activo' => 1, 'categoria_profissional' => 'Bacharel', 'nacionalidade' => 'Moçambique', 'farmacia_id' => $farmacia->id, 'role_id' => $role_gestor_farmacia_id, 'user_id' => $user_farmacia->id, created_at' => Carbon::now(), 'updated_at' => Carbon::now()]           
        ); */

        $utilizador_farmacia = UtilizadorFarmacia:: create(
            ['nome' => 'Utilizador Farmacia 1', 'contacto' => '828282822', 'numero_caderneta' => 1234546, 'activo' => 1, 'categoria_profissional' => 'Bacharel', 'nacionalidade' => 'Moçambique', 'farmacia_id' => $farmacia->id, 'role_id' => $role_gestor_farmacia_id, 'user_id' => $user_farmacia->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]           
        );

        $codigo_login = 'FAR' . sprintf("%04d", $utilizador_farmacia->id);

        DB::table('users')
        ->where('id', $user_farmacia->id)
        ->update(['codigo_login' => $codigo_login,'password' => bcrypt(1234567)]);
    }
}
