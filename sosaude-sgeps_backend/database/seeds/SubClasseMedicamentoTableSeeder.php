<?php

use App\Models\SubGrupoMedicamento;
use Illuminate\Database\Seeder;

class SubClasseMedicamentoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $anestesicos_gerais_e_oxigénio = SubGrupoMedicamento::where('nome', 'Anestésicos gerais e oxigénio')->first();
        $medicacao_pre_operatoria_e_sedacao_para_procedimentos_de_curta_duracao = SubGrupoMedicamento::where('nome', 'Medicação pré-operatória e sedação para procedimentos de curta duração')->first();
        $medicamentos_para_cuidados_paliativos = SubGrupoMedicamento::where('nome', 'Medicamentos para cuidados paliativos')->first();

        DB::table('sub_classe_medicamentos')->insert([
            ['nome' => 'Medicamentos para inalação', 'sub_grupo_medicamento_id' => $anestesicos_gerais_e_oxigénio->id],
            ['nome' => 'Medicamentos injectáveis', 'sub_grupo_medicamento_id' => $anestesicos_gerais_e_oxigénio->id],
            ['nome' => 'Adjuvante na anestesia', 'sub_grupo_medicamento_id' => $medicacao_pre_operatoria_e_sedacao_para_procedimentos_de_curta_duracao->id],
            ['nome' => 'Adjuvante na anestesia - Benzodiazepina', 'sub_grupo_medicamento_id' => $medicacao_pre_operatoria_e_sedacao_para_procedimentos_de_curta_duracao->id],
            ['nome' => 'Adjuvante na anestesia - Analgésico opióide', 'sub_grupo_medicamento_id' => $medicacao_pre_operatoria_e_sedacao_para_procedimentos_de_curta_duracao->id],
            ['nome' => 'Anti-histamínico/Anti-emético', 'sub_grupo_medicamento_id' => $medicamentos_para_cuidados_paliativos->id],
            ['nome' => 'Corticosteróide', 'sub_grupo_medicamento_id' => $medicamentos_para_cuidados_paliativos->id],
            ['nome' => 'Benzodiazepina', 'sub_grupo_medicamento_id' => $medicamentos_para_cuidados_paliativos->id],
            ['nome' => 'Laxantes', 'sub_grupo_medicamento_id' => $medicamentos_para_cuidados_paliativos->id],
            ['nome' => 'Anti-espasmódico', 'sub_grupo_medicamento_id' => $medicamentos_para_cuidados_paliativos->id],
            ['nome' => 'Antipsicótico', 'sub_grupo_medicamento_id' => $medicamentos_para_cuidados_paliativos->id],
            ['nome' => 'Anti-diarreico', 'sub_grupo_medicamento_id' => $medicamentos_para_cuidados_paliativos->id],
            ['nome' => 'Adjuvante na anestesia -Benzodiazepina', 'sub_grupo_medicamento_id' => $medicamentos_para_cuidados_paliativos->id],
        ]);
    }
}
