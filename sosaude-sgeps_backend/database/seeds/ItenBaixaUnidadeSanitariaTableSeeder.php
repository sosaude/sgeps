<?php

use Illuminate\Database\Seeder;

class ItenBaixaUnidadeSanitariaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('iten_baixa_unidade_sanitarias')->insert([
            [
                'preco' => 2500,
                'iva' => 0,
                'preco_iva' => 2500,
                'quantidade' => 1,
                'servico_id' => 1,
                'baixa_unidade_sanitaria_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'preco' => 2500,
                'iva' => 0,
                'preco_iva' => 2500,
                'quantidade' => 1,
                'servico_id' => 2,
                'baixa_unidade_sanitaria_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'preco' => 3000,
                'iva' => 0,
                'preco_iva' => 3000,
                'quantidade' => 1,
                'servico_id' => 3,
                'baixa_unidade_sanitaria_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'preco' => 3000,
                'iva' => 0,
                'preco_iva' => 3000,
                'quantidade' => 1,
                'servico_id' => 4,
                'baixa_unidade_sanitaria_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],



            [
                'preco' => 3500,
                'iva' => 0,
                'preco_iva' => 3500,
                'quantidade' => 1,
                'servico_id' => 5,
                'baixa_unidade_sanitaria_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'preco' => 3500,
                'iva' => 0,
                'preco_iva' => 3500,
                'quantidade' => 1,
                'servico_id' => 6,
                'baixa_unidade_sanitaria_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'preco' => 4000,
                'iva' => 0,
                'preco_iva' => 4000,
                'quantidade' => 1,
                'servico_id' => 7,
                'baixa_unidade_sanitaria_id' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'preco' => 4000,
                'iva' => 0,
                'preco_iva' => 4000,
                'quantidade' => 1,
                'servico_id' => 8,
                'baixa_unidade_sanitaria_id' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
