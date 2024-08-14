<?php

use Illuminate\Database\Seeder;

class CategoriaUnidadeSanitariaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('categoria_unidade_sanitarias')->insert([
            ['nome' => 'Clínicas', 'codigo' => 1],
            ['nome' => 'Laboratórios', 'codigo' => 2],
            ['nome' => 'Ambulâncias', 'codigo' => 3],
        ]);
    }
}
