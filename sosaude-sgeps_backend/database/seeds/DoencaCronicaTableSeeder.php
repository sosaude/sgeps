<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DoencaCronicaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('doenca_cronicas')->insert([
            ['nome' => 'Doença Arterial Coronariana'],
            ['nome' => 'Disritmias'],
            ['nome' => 'Hipertensão'],
            ['nome' => 'Anemia'],
            ['nome' => 'Esclerose Múltipla'],
            ['nome' => 'Asma'],
            ['nome' => 'Doença pulmonar obstrutiva crônica'],
            ['nome' => 'Diabetes Mellitus Tipo I'],
            ['nome' => 'Diabetes Mellitus Tipo II'],
            ['nome' => 'Diabetes Insipidus'],
            ['nome' => 'Hiperlipidemia'],
            ['nome' => 'Doença Renal Crônica'],
            ['nome' => 'Doença de Parkinson'],
            ['nome' => 'Hipotireoidismo'],
            ['nome' => 'Artrite Reumatóide'],
            ['nome' => 'Hemofilia'],
            ['nome' => 'Doença falciforme'],
        ]);
    }
}
