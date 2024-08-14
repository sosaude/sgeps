<?php

use Illuminate\Database\Seeder;

class EstadoPedidoReembolsoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('estado_pedido_reembolsos')->insert([
            ['nome' => 'Aguardando confirmação', 'codigo' => 10],
            ['nome' => 'Aguardando Pagamento', 'codigo' => 11],
            ['nome' => 'Pagamento Processado', 'codigo' => 12],
            ['nome' => 'Aguardando Correcção', 'codigo' => 13],
        ]);
    }
}
