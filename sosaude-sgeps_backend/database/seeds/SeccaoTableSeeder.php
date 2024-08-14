<?php

use Illuminate\Database\Seeder;

class SeccaoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('seccaos')->insert([
            ['nome' => 'Administração', 'code' => 1],
            ['nome' => 'Empresa',       'code' => 2],
            ['nome' => 'Farmácia',      'code' => 3],
            ['nome' => 'Unidade Sanitária',       'code' => 4],
            
        ]);
    }
}
