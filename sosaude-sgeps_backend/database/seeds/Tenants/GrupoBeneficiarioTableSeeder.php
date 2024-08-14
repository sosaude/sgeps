<?php

use App\Models\Empresa;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GrupoBeneficiarioTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $empresa = Empresa::first();

        DB::table('grupo_beneficiarios')->insert([
            ['nome' => 'Grupo 1', 'empresa_id' => $empresa->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nome' => 'Grupo 2', 'empresa_id' => $empresa->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nome' => 'Grupo 3', 'empresa_id' => $empresa->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nome' => 'Grupo 4', 'empresa_id' => $empresa->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nome' => 'Grupo 5', 'empresa_id' => $empresa->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
