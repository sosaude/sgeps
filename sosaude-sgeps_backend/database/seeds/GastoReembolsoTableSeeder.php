<?php

use Illuminate\Database\Seeder;

class GastoReembolsoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('gasto_reembolsos')->insert([
            ['nome' => 'Medicamentos'],
            ['nome' => 'Consultas'],
            ['nome' => 'Exames Médicos'],
            ['nome' => 'Internamento'],
            ['nome' => 'Cirurgia'],
            ['nome' => 'Aquisição de Óculos de Vista'],
            ['nome' => 'Outros'],
        ]);
    }
}
