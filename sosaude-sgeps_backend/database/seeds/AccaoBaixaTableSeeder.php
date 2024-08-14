<?php

use Illuminate\Database\Seeder;

class AccaoBaixaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('accao_baixas')->insert([
            ['codigo' => 1, 'accao' => 'Submeter Ordem de Reserva'],
            ['codigo' => 2, 'accao' => 'Aprovar Ordem de Reserva'],
            ['codigo' => 3, 'accao' => 'Devolver Ordem de Reserva'],
            ['codigo' => 4, 'accao' => 'Rejeitar Ordem de Reserva'],

            ['codigo' => 20, 'accao' => 'Submeter Baixa Normal'],
            ['codigo' => 21, 'accao' => 'Submeter Baixa a partir da Ordem de Reserva'],
            ['codigo' => 22, 'accao' => 'Submeter Baixa a partir do Pedido de Autorização'],
            ['codigo' => 23, 'accao' => 'Confirmar Baixa'],
            ['codigo' => 24, 'accao' => 'Devolver Baixa'],
            ['codigo' => 25, 'accao' => 'Rejeitar Baixa'],
            ['codigo' => 26, 'accao' => 'Processar Pagamento da Baixa'],

            ['codigo' => 40, 'accao' => 'Submeter Pedido de Autorização'],
            ['codigo' => 41, 'accao' => 'Aprovar Pedido de Autorização'],
            ['codigo' => 42, 'accao' => 'Devolver Pedido de Aprovação'],
            ['codigo' => 43, 'accao' => 'Rejeitar Pedido de Aprovação'],
        ]);
    }
}
