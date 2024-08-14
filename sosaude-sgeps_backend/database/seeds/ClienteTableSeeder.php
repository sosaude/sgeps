<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ClienteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('clientes')->insert([
            [
                'nome' => 'Marrar Teste',
                'email' => 'marrarteste@gmail.com',
                'password' => bcrypt(1234567),
                'peso' => 67,
                'altura' => 1.67,
                'e_benefiairio_plano_saude' => true,
                'beneficiario_id' => 1,
                'dependente_beneficiario_id' => null,
                'tem_doenca_cronica' => false,
                'doenca_cronica_nome' => json_encode([]),
                'tipo_sanguineo' => 'A',
                'provincia' => 'Nampula',
                'cidade' => 'Nampula',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Cliente1',
                'email' => 'cliente1@gmail.com',
                'password' => bcrypt(1234567),
                'peso' => 80,
                'altura' => 1.55,
                'e_benefiairio_plano_saude' => true,
                'beneficiario_id' => 2,
                'dependente_beneficiario_id' => null,
                'tem_doenca_cronica' => false,
                'doenca_cronica_nome' => json_encode([]),
                'tipo_sanguineo' => 'AB',
                'provincia' => 'Maputo Cidade',
                'cidade' => 'Maputo Cidade',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Cliente2',
                'email' => 'cliente2@gmail.com',
                'password' => bcrypt(1234567),
                'peso' => 80,
                'altura' => 1.55,
                'e_benefiairio_plano_saude' => true,
                'beneficiario_id' => 3,
                'dependente_beneficiario_id' => null,
                'tem_doenca_cronica' => false,
                'doenca_cronica_nome' => json_encode([]),
                'tipo_sanguineo' => 'B',
                'provincia' => 'Maputo Cidade',
                'cidade' => 'Maputo Cidade',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Cliente3',
                'email' => 'cliente3@gmail.com',
                'password' => bcrypt(1234567),
                'peso' => 80,
                'altura' => 1.55,
                'e_benefiairio_plano_saude' => true,
                'beneficiario_id' => 4,
                'dependente_beneficiario_id' => null,
                'tem_doenca_cronica' => false,
                'doenca_cronica_nome' => json_encode([]),
                'tipo_sanguineo' => 'B',
                'provincia' => 'Maputo Cidade',
                'cidade' => 'Maputo Cidade',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nome' => 'Cliente4',
                'email' => 'cliente4@gmail.com',
                'password' => bcrypt(1234567),
                'peso' => 52,
                'altura' => 1.75,
                'e_benefiairio_plano_saude' => true,
                'beneficiario_id' => 5,
                'dependente_beneficiario_id' => null,
                'tem_doenca_cronica' => true,
                'doenca_cronica_nome' => json_encode(["Hipertensão"]),
                'tipo_sanguineo' => 'B',
                'provincia' => 'Maputo Cidade',
                'cidade' => 'Maputo Cidade',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
