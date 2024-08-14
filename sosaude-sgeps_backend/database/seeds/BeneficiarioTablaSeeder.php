<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BeneficiarioTablaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_id = Role::where('codigo', 8)->pluck('id')->first(); // beneficiario
        $role_benef_dependente_id = Role::where('codigo', 9)->pluck('id')->first(); // dependente

        $user_benef_um = User::create(['nome' => 'Beneficiario Um', 'codigo_login' => 'BENE0001', 'password' => bcrypt(1234567), 'active' => true, 'loged_once' => true, 'role_id' => $role_id]);
        $user_dependent_benef_um = User::create(['nome' => 'Beneficiario Um', 'codigo_login' => 'DEBENE0001', 'password' => bcrypt(1234567), 'active' => true, 'loged_once' => true, 'role_id' => $role_benef_dependente_id]);

        $user_benef_dois = User::create(['nome' => 'Beneficiario Dois', 'codigo_login' => 'BENE0002', 'password' => bcrypt(1234567), 'active' => true, 'loged_once' => true, 'role_id' => $role_id]);
        $user_dependent_benef_dois = User::create(['nome' => 'Beneficiario Dois', 'codigo_login' => 'DEBENE0002', 'password' => bcrypt(1234567), 'active' => true, 'loged_once' => true, 'role_id' => $role_benef_dependente_id]);
        $user_dependent_benef_dois_um = User::create(['nome' => 'Beneficiario Dois Um', 'codigo_login' => 'DEBENE0003', 'password' => bcrypt(1234567), 'active' => true, 'loged_once' => true, 'role_id' => $role_benef_dependente_id]);

        $user_benef_tres = User::create(['nome' => 'Beneficiario Três', 'codigo_login' => 'BENE0003', 'password' => bcrypt(1234567), 'active' => true, 'loged_once' => true, 'role_id' => $role_id]);
        $user_dependent_benef_tres = User::create(['nome' => 'Beneficiario Três', 'codigo_login' => 'DEBENE0004', 'password' => bcrypt(1234567), 'active' => true, 'loged_once' => true, 'role_id' => $role_benef_dependente_id]);
        $user_dependent_benef_tres_um = User::create(['nome' => 'Beneficiario Três Um', 'codigo_login' => 'DEBENE0005', 'password' => bcrypt(1234567), 'active' => true, 'loged_once' => true, 'role_id' => $role_benef_dependente_id]);

        $user_benef_quatro = User::create(['nome' => 'Beneficiario Quatro', 'codigo_login' => 'BENE0004', 'password' => bcrypt(1234567), 'active' => true, 'loged_once' => true, 'role_id' => $role_id]);

        $user_benef_cinco = User::create(['nome' => 'Beneficiario Cinco', 'codigo_login' => 'BENE0005', 'password' => bcrypt(1234567), 'active' => true, 'loged_once' => true, 'role_id' => $role_id]);
        
        $user_benef_seis = User::create(['nome' => 'Beneficiario Seis', 'codigo_login' => 'BENE0006', 'password' => bcrypt(1234567), 'active' => true, 'loged_once' => true, 'role_id' => $role_id]);
        
        $user_benef_sete = User::create(['nome' => 'Beneficiario Sete', 'codigo_login' => 'BENE0007', 'password' => bcrypt(1234567), 'active' => true, 'loged_once' => true, 'role_id' => $role_id]);



        DB::table('beneficiarios')->insert([
            [
                'empresa_id' => 1,
                'activo' => true,
                'nome' => 'Beneficiario Um',
                'numero_beneficiario' => '12345',
                'endereco' => 'Rua das mangueiras, Maputo',
                'bairro' => 'Luis Cabral',
                'telefone' => '825648276',
                'genero' => 'F',
                'data_nascimento' => '1981-08-12',
                'ocupacao' => 'Contabilista',
                'aposentado' => false,
                'doenca_cronica' => true,
                'doenca_cronica_nome' => json_encode(["Bronquite", "Asma"]),
                'grupo_beneficiario_id' => 1,
                'user_id' => $user_benef_um->id,
                'tem_dependentes' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => 1,
                'activo' => true,
                'nome' => 'Beneficiario Dois',
                'numero_beneficiario' => '12346',
                'endereco' => 'Joao Vicente, No 4657, Matola-Maputo',
                'bairro' => 'C700',
                'telefone' => '848648276',
                'genero' => 'F',
                'data_nascimento' => '1978-03-17',
                'ocupacao' => 'Gestor de Recursos Humanos',
                'aposentado' => false,
                'doenca_cronica' => false,
                'doenca_cronica_nome' => json_encode([]),
                'grupo_beneficiario_id' => 1,
                'user_id' => $user_benef_dois->id,
                'tem_dependentes' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => 1,
                'activo' => true,
                'nome' => 'Beneficiario Três',
                'numero_beneficiario' => '12347',
                'endereco' => 'Av Moçambique, no 6755, Maputo',
                'bairro' => 'Bagamoio',
                'telefone' => '844648273',
                'genero' => 'M',
                'data_nascimento' => '1975-03-15',
                'ocupacao' => 'Gestor deMarketing',
                'aposentado' => false,
                'doenca_cronica' => true,
                'doenca_cronica_nome' => json_encode(["Hipertensão"]),
                'grupo_beneficiario_id' => 1,
                'user_id' => $user_benef_tres->id,
                'tem_dependentes' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => 1,
                'activo' => true,
                'nome' => 'Beneficiario Quatro',
                'numero_beneficiario' => '12348',
                'endereco' => 'Av Angola, no 6955, Maputo',
                'bairro' => 'Marcos Andrade',
                'telefone' => '87908272',
                'genero' => 'M',
                'data_nascimento' => '1990-10-11',
                'ocupacao' => 'Gestor de Vendas',
                'aposentado' => false,
                'doenca_cronica' => true,
                'doenca_cronica_nome' => json_encode(["Diabete"]),
                'grupo_beneficiario_id' => 2,
                'user_id' => $user_benef_quatro->id,
                'tem_dependentes' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => 1,
                'activo' => true,
                'nome' => 'Beneficiario Cinco',
                'numero_beneficiario' => '12349',
                'endereco' => 'Av 25 Setembro, no 2589, Maputo',
                'bairro' => 'Cupe',
                'telefone' => '826784532',
                'genero' => 'M',
                'data_nascimento' => '1980-11-12',
                'ocupacao' => 'Arquitecto',
                'aposentado' => false,
                'doenca_cronica' => true,
                'doenca_cronica_nome' => json_encode(["Osteoporose"]),
                'grupo_beneficiario_id' => 2,
                'user_id' => $user_benef_cinco->id,
                'tem_dependentes' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => 1,
                'activo' => true,
                'nome' => 'Beneficiario Seis',
                'numero_beneficiario' => '12350',
                'endereco' => 'Av Samuel Magaia, no 3898, Maputo',
                'bairro' => 'Jorge Cabral',
                'telefone' => '824784500',
                'genero' => 'F',
                'data_nascimento' => '1976-08-23',
                'ocupacao' => 'Gestor Financeiro',
                'aposentado' => false,
                'doenca_cronica' => true,
                'doenca_cronica_nome' => json_encode(["Osteoporose"]),
                'grupo_beneficiario_id' => 3,
                'user_id' => $user_benef_seis->id,
                'tem_dependentes' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => 1,
                'activo' => true,
                'nome' => 'Beneficiario Sete',
                'numero_beneficiario' => '12351',
                'endereco' => 'Av Samuel Magaia, no 3898, Maputo',
                'bairro' => 'Julio Lopes',
                'telefone' => '825784509',
                'genero' => 'F',
                'data_nascimento' => '1977-03-28',
                'ocupacao' => 'Director Geral',
                'aposentado' => false,
                'doenca_cronica' => true,
                'doenca_cronica_nome' => json_encode(["Hipertensão"]),
                'grupo_beneficiario_id' => 3,
                'user_id' => $user_benef_sete->id,
                'tem_dependentes' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);


        DB::table('dependente_beneficiarios')->insert([
            [
                'empresa_id' => 1,
                'activo' => true,
                'beneficiario_id' => 1,
                'user_id' => $user_dependent_benef_um->id,
                'nome' => 'Dependente Beneficiario Um',
                'endereco' => 'Rua das mangueiras, Maputo',
                'bairro' => 'Luis Cabral',
                'telefone' => '876756543',
                'genero' => 'M',
                'data_nascimento' => '1994-08-09',
                'doenca_cronica' => true,
                'doenca_cronica_nome' => json_encode(["Asma"]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'empresa_id' => 1,
                'activo' => true,
                'beneficiario_id' => 2,
                'user_id' => $user_dependent_benef_dois->id,
                'nome' => 'Dependente Beneficiario Dois',
                'endereco' => 'Joao Vicente, No 4657, Matola-Maputo',
                'bairro' => 'C700',
                'telefone' => '876758098',
                'genero' => 'M',
                'data_nascimento' => '1995-05-02',
                'doenca_cronica' => true,
                'doenca_cronica_nome' => json_encode(["Bronquite"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'empresa_id' => 1,
                'activo' => true,
                'beneficiario_id' => 2,
                'user_id' => $user_dependent_benef_dois_um->id,
                'nome' => 'Dependente Beneficiario Dois Um',
                'endereco' => 'Joao Vicente, No 4657, Matola-Maputo',
                'bairro' => 'C700',
                'telefone' => '876758032',
                'genero' => 'F',
                'data_nascimento' => '1993-06-14',
                'doenca_cronica' => true,
                'doenca_cronica_nome' => json_encode(["Bronquite"]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'empresa_id' => 1,
                'activo' => true,
                'beneficiario_id' => 3,
                'user_id' => $user_dependent_benef_tres->id,
                'nome' => 'Dependente Beneficiario Três',
                'endereco' => 'Luis Cabral, No 3447, Cidade-Maputo',
                'bairro' => 'Luis Cabral',
                'telefone' => '867865220',
                'genero' => 'M',
                'data_nascimento' => '1990-06-08',
                'doenca_cronica' => false,
                'doenca_cronica_nome' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'empresa_id' => 1,
                'activo' => true,
                'beneficiario_id' => 3,
                'user_id' => $user_dependent_benef_tres_um->id,
                'nome' => 'Dependente Beneficiario Três Um',
                'endereco' => 'Luis Cabral, No 3447, Cidade-Maputo',
                'bairro' => 'Luis Cabral',
                'telefone' => '827865228',
                'genero' => 'M',
                'data_nascimento' => '1988-02-25',
                'doenca_cronica' => false,
                'doenca_cronica_nome' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
