<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContinenteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('continentes')->insert([
            ['nome' => 'Ásia'],
            ['nome' => 'América'],
            ['nome' => 'África'],
            ['nome' => 'Antártida'],
            ['nome' => 'Europa'],
            ['nome' => 'Oceania'],
        ]);
    }
}
