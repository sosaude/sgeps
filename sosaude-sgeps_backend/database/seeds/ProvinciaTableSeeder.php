<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('provincias')->insert([
            ['codigo' => null, 'nome' => 'Cabo Delgado'],
            ['codigo' => null, 'nome' => 'Gaza'],
            ['codigo' => null, 'nome' => 'Inhambane'],
            ['codigo' => null, 'nome' => 'Manica'],
            ['codigo' => null, 'nome' => 'Maputo (Cidade)'],
            ['codigo' => null, 'nome' => 'Maputo'],
            ['codigo' => null, 'nome' => 'Nampula'],
            ['codigo' => null, 'nome' => 'Niassa'],
            ['codigo' => null, 'nome' => 'Sofala'],
            ['codigo' => null, 'nome' => 'Tete'],
            ['codigo' => null, 'nome' => 'Zambeze'],
        ]);
    }
}
