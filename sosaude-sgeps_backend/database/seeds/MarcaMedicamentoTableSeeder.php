<?php

use App\Models\Medicamento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MarcaMedicamentoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $medicamento_id = Medicamento::pluck('id')->first();
        
        DB::table('marca_medicamentos')->insert([
            ['marca' => 'Panado', 'codigo' => '0098', 'pais_origem' => 'India', 'medicamento_id' => $medicamento_id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['marca' => 'Paracetamol Azual', 'codigo' => '0087', 'pais_origem' => 'India', 'medicamento_id' => $medicamento_id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
