<?php

use Illuminate\Database\Seeder;
use App\Models\CategoriaServico;
use Illuminate\Support\Facades\DB;

class ServicoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $analises = CategoriaServico::where('nome', 'Analises')->first();
        $consultas = CategoriaServico::where('nome', 'Consultas')->first();
        $ecografia = CategoriaServico::where('nome', 'Ecografia')->first();
        $oftamologia = CategoriaServico::where('nome', 'Oftamologia')->first();
        $servico_5 = CategoriaServico::where('nome', 'Serviço 5')->first();
        $servico_6 = CategoriaServico::where('nome', 'Serviço 6')->first();
        $servico_7 = CategoriaServico::where('nome', 'Serviço 7')->first();
        $servico_8 = CategoriaServico::where('nome', 'Serviço 8')->first();
        DB::table('servicos')->insert([
            ['nome' => 'Consulta de clinica geral', 'categoria_servico_id' => $analises->id],
            ['nome' => 'Controlo de Especialidade', 'categoria_servico_id' => $consultas->id],
            ['nome' => 'Controlo de resultados de especialista', 'categoria_servico_id' => $ecografia->id],
            ['nome' => 'Oftamologia', 'categoria_servico_id' => $oftamologia->id],
            ['nome' => 'Serviço Cinco', 'categoria_servico_id' => $servico_5->id],
            ['nome' => 'Serviço Seis', 'categoria_servico_id' => $servico_6->id],
            ['nome' => 'Serviço Sete', 'categoria_servico_id' => $servico_7->id],
            ['nome' => 'Serviço Oito', 'categoria_servico_id' => $servico_8->id],
        ]);
    }
}
