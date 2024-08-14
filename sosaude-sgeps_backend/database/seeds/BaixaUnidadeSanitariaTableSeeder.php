<?php

use App\Models\EstadoBaixa;
use Illuminate\Database\Seeder;

class BaixaUnidadeSanitariaTableSeeder extends Seeder
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
        DB::table('baixa_unidade_sanitarias')->insert([
            [
                'valor' => 5000,
                'responsavel' => 'Figueredo Martins',
                'proveniencia' => 2,
                'comprovativo' => null,
                'data_criacao_pedido_aprovacao' => null,
                'data_aprovacao_pedido_aprovacao' => null,
                'resposavel_aprovacao_pedido_aprovacao' => null,
                'comentario_pedido_aprovacao' => null,
                'beneficiario_id' => 1,
                'unidade_sanitaria_id' => 1,
                'empresa_id' => 1,
                'estado_baixa_id' => $estado_10_confirmacao->id,
                'comentario_pedido_aprovacao' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'valor' => 6000,
                'responsavel' => 'Maria Luis',
                'proveniencia' => 2,
                'comprovativo' => null,
                'data_criacao_pedido_aprovacao' => null,
                'data_aprovacao_pedido_aprovacao' => null,
                'resposavel_aprovacao_pedido_aprovacao' => null,
                'comentario_pedido_aprovacao' => null,
                'beneficiario_id' => 2,
                'unidade_sanitaria_id' => 1,
                'empresa_id' => 1,
                'estado_baixa_id' => $estado_10_confirmacao->id,
                'comentario_pedido_aprovacao' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            





            [
                'valor' => 7000,
                'responsavel' => 'Roberto Nobre',
                'proveniencia' => 2,
                'comprovativo' => null,
                'data_criacao_pedido_aprovacao' => now(),
                'data_aprovacao_pedido_aprovacao' => null,
                'resposavel_aprovacao_pedido_aprovacao' => null,
                'comentario_pedido_aprovacao' => json_encode([]),
                'beneficiario_id' => 1,
                'unidade_sanitaria_id' => 1,
                'empresa_id' => 1,
                'estado_baixa_id' => $estado_8_aprovacao_pedido->id,
                'comentario_pedido_aprovacao' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'valor' => 8000,
                'responsavel' => 'Silvestre Buca',
                'proveniencia' => 2,
                'comprovativo' => null,
                'data_criacao_pedido_aprovacao' => now(),
                'data_aprovacao_pedido_aprovacao' => null,
                'resposavel_aprovacao_pedido_aprovacao' => null,
                'comentario_pedido_aprovacao' => json_encode([]),
                'beneficiario_id' => 2,
                'unidade_sanitaria_id' => 1,
                'empresa_id' => 1,
                'estado_baixa_id' => $estado_8_aprovacao_pedido->id,
                'comentario_pedido_aprovacao' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
        ]);
    }
}
