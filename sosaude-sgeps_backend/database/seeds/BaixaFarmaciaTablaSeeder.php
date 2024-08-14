<?php

use Carbon\Carbon;
use App\Models\EstadoBaixa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BaixaFarmaciaTablaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $estado_10_confirmacao = EstadoBaixa::where('codigo', '10')->first();
        $estado_8_aprovacao_pedido = EstadoBaixa::where('codigo', '8')->first();
        DB::table('baixa_farmacias')->insert([
            [
                'valor' => 1000,
                'responsavel' => 'João',
                'proveniencia' => 1,
                'comprovativo' => null,
                'data_criacao_pedido_aprovacao' => null,
                'data_aprovacao_pedido_aprovacao' => null,
                'resposavel_aprovacao_pedido_aprovacao' => null,
                'comentario_pedido_aprovacao' => null,
                'beneficiario_id' => 1,
                'farmacia_id' => 1,
                'empresa_id' => 1,
                'estado_baixa_id' => $estado_10_confirmacao->id,
                'comentario_pedido_aprovacao' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'valor' => 2000,
                'responsavel' => 'Félix',
                'proveniencia' => 1,
                'comprovativo' => null,
                'data_criacao_pedido_aprovacao' => null,
                'data_aprovacao_pedido_aprovacao' => null,
                'resposavel_aprovacao_pedido_aprovacao' => null,
                'comentario_pedido_aprovacao' => null,
                'beneficiario_id' => 2,
                'farmacia_id' => 1,
                'empresa_id' => 1,
                'estado_baixa_id' => $estado_10_confirmacao->id,
                'comentario_pedido_aprovacao' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            





            [
                'valor' => 3000,
                'responsavel' => 'Santos Lucas',
                'proveniencia' => 1,
                'comprovativo' => null,
                'data_criacao_pedido_aprovacao' => now(),
                'data_aprovacao_pedido_aprovacao' => null,
                'resposavel_aprovacao_pedido_aprovacao' => null,
                'comentario_pedido_aprovacao' => json_encode([]),
                'beneficiario_id' => 1,
                'farmacia_id' => 1,
                'empresa_id' => 1,
                'estado_baixa_id' => $estado_8_aprovacao_pedido->id,
                'comentario_pedido_aprovacao' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'valor' => 4000,
                'responsavel' => 'João Saraiva',
                'proveniencia' => 1,
                'comprovativo' => null,
                'data_criacao_pedido_aprovacao' => now(),
                'data_aprovacao_pedido_aprovacao' => null,
                'resposavel_aprovacao_pedido_aprovacao' => null,
                'comentario_pedido_aprovacao' => json_encode([]),
                'beneficiario_id' => 2,
                'farmacia_id' => 1,
                'empresa_id' => 1,
                'estado_baixa_id' => $estado_8_aprovacao_pedido->id,
                'comentario_pedido_aprovacao' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
        ]);
    }
}
