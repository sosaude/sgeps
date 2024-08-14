<?php

use Illuminate\Database\Seeder;

class FormaMedicamentoTableeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('forma_medicamentos')->insert([
            ['forma' => 'Líquido para inalação'],
            ['forma' => 'Aerossol para inalação'],
            ['forma' => 'Gás para inalação'],
            ['forma' => 'Injectável'],
            ['forma' => 'Spray'],
            ['forma' => 'Carpule para uso dentário'],
            ['forma' => 'Gel tópico'],
            ['forma' => 'Comprimido'],
            ['forma' => 'Solução oral'],
            ['forma' => 'Supositório'],
            ['forma' => 'Supensão oral'],
            ['forma' => 'Comprimido dispersível'],
            ['forma' => 'Xarope'],
            ['forma' => 'Comprimido de libertação imediata'],
            ['forma' => 'Granulado de libertação prologanda'],
            ['forma' => 'Comprimido de libertação prolongada'],
            ['forma' => 'Cápsula'],
            ['forma' => 'Solução Rectal'],
            ['forma' => 'Solução Injectável'],
            ['forma' => 'Líquido oral'],
            ['forma' => 'Cápsula'],
        ]);
    }
}
