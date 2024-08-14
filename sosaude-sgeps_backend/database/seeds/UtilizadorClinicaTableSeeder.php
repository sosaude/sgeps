<?php

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Clinica;
use App\Models\UtilizadorClinica;
use Illuminate\Database\Seeder;

class UtilizadorClinicaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_gestor_clinica_id = Role::where('codigo', 6)->pluck('id')->first();
        $clinica = Clinica::first();

        $user_clinica = User::create(
            ['nome' => 'Utilizador Clínica 1', 'password' => bcrypt(1234567), 'active' => 1, 'loged_once' => 1, 'login_attempts' => 0, 'role_id' => $role_gestor_clinica_id]
        );
        
        $utilizador_clinica = UtilizadorClinica::create(
            ['nome' => 'Utilizador Clínica 1', 'contacto' => '828282822', 'activo' => 1, 'nacionalidade' => 'Moçambique', 'clinica_id' => $clinica->id, 'user_id' => $user_clinica->id, 'role_id' => $role_gestor_clinica_id]           
        );

        $codigo_login = 'CLI' . sprintf("%04d", $utilizador_clinica->id);
        
        DB::table('users')
        ->where('id', $user_clinica->id)
        ->update(['codigo_login' => $codigo_login, 'password' => bcrypt(1234567)]);
    }
}
