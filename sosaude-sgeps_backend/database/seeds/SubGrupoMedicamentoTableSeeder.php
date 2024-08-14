<?php

use App\Models\GrupoMedicamento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubGrupoMedicamentoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $grupo_anastesicos = GrupoMedicamento::where('nome', 'Anestésicos')->first();
        $grupo_medicamentos_para_dor_e_cuidados_paliativos = GrupoMedicamento::where('nome', 'Medicamentos para dor e cuidados paliativos')->first();
        DB::table('sub_grupo_medicamentos')->insert([
            ['nome' => 'Anestésicos gerais e oxigénio', 'grupo_medicamento_id' => $grupo_anastesicos->id],
            ['nome' => 'Anestésicos locais', 'grupo_medicamento_id' => $grupo_anastesicos->id],
            ['nome' => 'Medicação pré-operatória e sedação para procedimentos de curta duração', 'grupo_medicamento_id' => $grupo_anastesicos->id],
            ['nome' => 'Analgésicos e anti-inflamatórios não esteróides', 'grupo_medicamento_id' => $grupo_medicamentos_para_dor_e_cuidados_paliativos->id],
            ['nome' => 'Analgésicos opióides', 'grupo_medicamento_id' => $grupo_medicamentos_para_dor_e_cuidados_paliativos->id],
            ['nome' => 'Medicamentos para cuidados paliativos', 'grupo_medicamento_id' => $grupo_medicamentos_para_dor_e_cuidados_paliativos->id],
        ]);
    }
}
