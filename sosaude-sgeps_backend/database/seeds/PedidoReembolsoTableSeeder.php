<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\EstadoPedidoReembolso;

class PedidoReembolsoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $estado_aguardando_confirmacao = EstadoPedidoReembolso::where('codigo', 10)->first();
        DB::table('pedido_reembolsos')->insert([
            [
                'unidade_sanitaria' => 'Pediatria São José',
                'servico_prestado' => 'Pediatria',
                'nr_comprovativo' => '12345',
                'valor' => 2000.09,
                'data' => '2020-08-09',
                'comprovativo' => null,
                'comentario' => null,
                'beneficio_proprio_beneficiario' => true,
                'beneficiario_id' => 1,
                'dependente_beneficiario_id' => null,
                'empresa_id' => 1,
                'estado_pedido_reembolso_id' => $estado_aguardando_confirmacao->id
            ],
            [
                'unidade_sanitaria' => 'Clinica Sant-George',
                'servico_prestado' => 'Radiografia',
                'nr_comprovativo' => '22347',
                'valor' => 5003.55,
                'data' => '2020-06-10',
                'comprovativo' => null,
                'comentario' => null,
                'beneficio_proprio_beneficiario' => true,
                'beneficiario_id' => 2,
                'dependente_beneficiario_id' => null,
                'empresa_id' => 1,
                'estado_pedido_reembolso_id' => $estado_aguardando_confirmacao->id
            ],
            [
                'unidade_sanitaria' => 'Hospital Luar',
                'servico_prestado' => 'Consulta Geral',
                'nr_comprovativo' => '22347',
                'valor' => 1000.10,
                'data' => '2020-07-13',
                'comprovativo' => null,
                'comentario' => null,
                'beneficio_proprio_beneficiario' => false,
                'beneficiario_id' => null,
                'dependente_beneficiario_id' => 3,
                'empresa_id' => 1,
                'estado_pedido_reembolso_id' => $estado_aguardando_confirmacao->id
            ],
        ]);
    }
}
