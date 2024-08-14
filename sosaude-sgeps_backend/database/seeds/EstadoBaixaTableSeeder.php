<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoBaixaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('estado_baixas')->insert([
            ['codigo' => 7, 'referencia' => 'Pedido de Aprovação', 'nome' => 'Rejeitado'],
            ['codigo' => 8, 'referencia' => 'Pedido de Aprovação', 'nome' => 'Aguardando Aprovação'],
            ['codigo' => 9, 'referencia' => 'Pedido de Aprovação', 'nome' => 'Aguardando a inicialização do Gasto'],
            ['codigo' => 10, 'referencia' => 'Gasto', 'nome' => 'Aguardando Confirmação'],
            ['codigo' => 11, 'referencia' => 'Gasto', 'nome' => 'Aguardando Pagamento'],
            ['codigo' => 12, 'referencia' => 'Gasto', 'nome' => 'Pagamento Processado'],
            ['codigo' => 13, 'referencia' => 'Gasto', 'nome' => 'Devolvido'],
            ['codigo' => 20, 'referencia' => 'Ordem de Reserva', 'nome' => 'Aguardando inicialização do Gasto'],
        ]);
    }
}
