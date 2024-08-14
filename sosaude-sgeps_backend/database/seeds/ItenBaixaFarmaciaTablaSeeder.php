<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItenBaixaFarmaciaTablaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('iten_baixa_farmacias')->insert([
            [
                'preco' => 500,
                'iva' => 0,
                'preco_iva' => 500,
                'quantidade' => 1,
                // 'medicamento_id' => 1,
                'marca_medicamento_id' => 1,
                'baixa_farmacia_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'preco' => 500,
                'iva' => 0,
                'preco_iva' => 500,
                'quantidade' => 1,
                // 'medicamento_id' => 2,
                'marca_medicamento_id' => 1,
                'baixa_farmacia_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'preco' => 1000,
                'iva' => 0,
                'preco_iva' => 1000,
                'quantidade' => 2,
                // 'medicamento_id' => 3,
                'marca_medicamento_id' => 2,
                'baixa_farmacia_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'preco' => 1000,
                'iva' => 0,
                'preco_iva' => 1000,
                'quantidade' => 3,
                // 'medicamento_id' => 4,
                'marca_medicamento_id' => 2,
                'baixa_farmacia_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],



            [
                'preco' => 1500,
                'iva' => 0,
                'preco_iva' => 1500,
                'quantidade' => 1,
                // 'medicamento_id' => 5,
                'marca_medicamento_id' => 2,
                'baixa_farmacia_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'preco' => 1500,
                'iva' => 0,
                'preco_iva' => 1500,
                'quantidade' => 1,
                // 'medicamento_id' => 6,
                'marca_medicamento_id' => 2,
                'baixa_farmacia_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'preco' => 2000,
                'iva' => 0,
                'preco_iva' => 2000,
                'quantidade' => 1,
                // 'medicamento_id' => 7,
                'marca_medicamento_id' => 2,
                'baixa_farmacia_id' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'preco' => 2000,
                'iva' => 0,
                'preco_iva' => 2000,
                'quantidade' => 1,
                // 'medicamento_id' => 8,
                'marca_medicamento_id' => 2,
                'baixa_farmacia_id' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
