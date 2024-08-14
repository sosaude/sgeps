<?php

use Illuminate\Database\Seeder;

class CategoriaEmpresaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categoria_empresas')->insert([
            ['codigo' => 1, 'nome' => 'Empresa'],
            ['codigo' => 2, 'nome' => 'Seguradora']
        ]);
    }
}
