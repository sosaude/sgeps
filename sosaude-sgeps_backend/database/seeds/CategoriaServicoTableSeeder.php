<?php

use Illuminate\Database\Seeder;

class CategoriaServicoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('categoria_servicos')->insert([
            ['nome' => 'Analises'],
            ['nome' => 'Consultas'],
            ['nome' => 'Ecografia'],
            ['nome' => 'Oftamologia'],
            ['nome' => 'Serviço 5'],
            ['nome' => 'Serviço 6'],
            ['nome' => 'Serviço 7'],
            ['nome' => 'Serviço 8'],
        ]);
    }
}
