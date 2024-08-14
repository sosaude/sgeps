<?php

use Illuminate\Database\Seeder;
use App\Models\FormaMedicamento;
use App\Models\GrupoMedicamento;
use App\Models\NomeGenericoMedicamento;
use App\Models\SubClasseMedicamento;
use App\Models\SubGrupoMedicamento;

class MedicamentoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Grupos
        $anestesicos = GrupoMedicamento::where('nome', 'Anestésicos')->first();
        // Sub-Grupo
        $anestesicos_gerais_e_oxigenio = SubGrupoMedicamento::where('nome', 'Anestésicos gerais e oxigénio')->first();
        // Sub-Classe
        $medicamentos_para_inalacao = SubClasseMedicamento::where('nome', 'Medicamentos para inalação')->first();
        // Formas Medicamento
        $liquido_para_inalacao = FormaMedicamento::where('forma', 'Líquido para inalação')->first();
        $aerossol_para_inalacao = FormaMedicamento::where('forma', 'Aerossol para inalação')->first();
        $gas_para_inalacao = FormaMedicamento::where('forma', 'Gás para inalação')->first();
        // Nome Generico
        $holotano = NomeGenericoMedicamento::where('nome', 'Halotano')->first();
        $isoflurano = NomeGenericoMedicamento::where('nome', 'Isoflurano')->first();
        $protoxido_de_azoto_ou_oxido_nitroso = NomeGenericoMedicamento::where('nome', 'Protóxido de azoto ou óxido nitroso')->first();
        $oxigenio = NomeGenericoMedicamento::where('nome', 'Oxigénio')->first();




        // Grupos
        $anestesicos = GrupoMedicamento::where('nome', 'Anestésicos')->first();
        // Sub-Classe
        $medicamentos_injectaveis = SubClasseMedicamento::where('nome', 'Medicamentos injectáveis')->first();
        // Formas Medicamento
        $injectavel = FormaMedicamento::where('forma', 'Injectável')->first();

        // Nome Generico
        $ketamina = NomeGenericoMedicamento::where('nome', 'Ketamina')->first();
        $propofol = NomeGenericoMedicamento::where('nome', 'Propofol')->first();
        $tiopental = NomeGenericoMedicamento::where('nome', 'Tiopental')->first();
        $bupivacaina = NomeGenericoMedicamento::where('nome', 'Bupivacaína')->first();


        // Sub-Grupo
        // Sub-Grupo
        $anestesicos_locais = SubGrupoMedicamento::where('nome', 'Anestésicos locais')->first();
        // Formas Medicamento
        $spray = FormaMedicamento::where('forma', 'Spray')->first();
        $carpule_para_uso_dentario = FormaMedicamento::where('forma', 'Carpule para uso dentário')->first();
        // Nome Generico
        $Bupivacaina_hiperbarica = NomeGenericoMedicamento::where('nome', 'Bupivacaína hiperbárica')->first();
        $bupivacaina_adrenalina = NomeGenericoMedicamento::where('nome', 'Bupivacaína + Adrenalina')->first();
        $cloreto_e_etilo = NomeGenericoMedicamento::where('nome', 'Cloreto de etilo')->first();
        $lidocaina = NomeGenericoMedicamento::where('nome', 'Lidocaína')->first();


        // Sub-Grupo
        $medicacao_pre_operatoria_e_sedacao_para_procedimentos_de_curta_duracao = SubGrupoMedicamento::where('nome', 'Medicação pré-operatória e sedação para procedimentos de curta duração')->first();
        // Sub-Classe
        $adjuvante_na_anestesia = SubClasseMedicamento::where('nome', 'Adjuvante na anestesia')->first();
        $adjuvante_na_anestesia_benzodiazepina = SubClasseMedicamento::where('nome', 'Adjuvante na anestesia - Benzodiazepina')->first();
        $adjuvante_na_anestesia_analgesico_opioide = SubClasseMedicamento::where('nome', 'Adjuvante na anestesia - Analgésico opióide')->first();
        // Formas Medicamento
        $comprimido = FormaMedicamento::where('forma', 'Comprimido')->first();
        // Nome Generico
        $atropina = NomeGenericoMedicamento::where('nome', 'Atropina')->first();
        $efedrina = NomeGenericoMedicamento::where('nome', 'Efedrina')->first();
        $midazolam = NomeGenericoMedicamento::where('nome', 'Midazolam')->first();
        $fentanil = NomeGenericoMedicamento::where('nome', 'Fentanil')->first();


        
        
        //Grupos
        $medicamentos_para_dor_e_cuidados_paliativos = GrupoMedicamento::where('nome', 'Medicamentos para dor e cuidados paliativos')->first();
        // Sub-Grupo
        $analgesicos_e_anti_inflamatorios_nao_esteroides = SubGrupoMedicamento::where('nome', 'Analgésicos e anti-inflamatórios não esteróides')->first();
        // NomeGenerico
        $acido_acetilsalicilico = NomeGenericoMedicamento::where('nome', 'Ácido Acetilsalicílico')->first();
        $diclofenac = NomeGenericoMedicamento::where('nome', 'Diclofenac')->first();
        $ibuprofeno = NomeGenericoMedicamento::where('nome', 'Ibuprofeno')->first();
        $paracetamol = NomeGenericoMedicamento::where('nome', 'Paracetamol')->first();




        DB::table('medicamentos')->insert([
            [
                'codigo' => '0765',
                'dosagem' => '100% v/v',
                'nome_generico_medicamento_id' => $holotano->id,
                'forma_medicamento_id' => $liquido_para_inalacao->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $anestesicos_gerais_e_oxigenio->id,
                'sub_classe_medicamento_id' => $medicamentos_para_inalacao->id
            ],
            [
                'codigo' => '0766',
                'dosagem' => '100% v/v',
                'nome_generico_medicamento_id' => $isoflurano->id,
                'forma_medicamento_id' => $liquido_para_inalacao->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $anestesicos_gerais_e_oxigenio->id,
                'sub_classe_medicamento_id' => $medicamentos_para_inalacao->id
            ],
            [
                'codigo' => '0767',
                'dosagem' => 'Não especificado',
                'nome_generico_medicamento_id' => $protoxido_de_azoto_ou_oxido_nitroso->id,
                'forma_medicamento_id' => $aerossol_para_inalacao->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $anestesicos_gerais_e_oxigenio->id,
                'sub_classe_medicamento_id' => $medicamentos_para_inalacao->id
            ],
            [
                'codigo' => '0768',
                'dosagem' => 'Não especificado',
                'nome_generico_medicamento_id' => $oxigenio->id,
                'forma_medicamento_id' => $gas_para_inalacao->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $anestesicos_gerais_e_oxigenio->id,
                'sub_classe_medicamento_id' => $medicamentos_para_inalacao->id
            ],


            [
                'codigo' => '0769',
                'dosagem' => '50 mg (cloridrato)/ ml, frasco de 10 ml',
                'nome_generico_medicamento_id' => $ketamina->id,
                'forma_medicamento_id' => $injectavel->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $anestesicos_gerais_e_oxigenio->id,
                'sub_classe_medicamento_id' => $medicamentos_injectaveis->id
            ],
            [
                'codigo' => '0770',
                'dosagem' => '10 mg/ml, ampola de 20 ml ou frasco de 50 ml',
                'nome_generico_medicamento_id' => $propofol->id,
                'forma_medicamento_id' => $injectavel->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $anestesicos_gerais_e_oxigenio->id,
                'sub_classe_medicamento_id' => $medicamentos_injectaveis->id
            ],
            [
                'codigo' => '0771',
                'dosagem' => '1g / 20ml',
                'nome_generico_medicamento_id' => $tiopental->id,
                'forma_medicamento_id' => $injectavel->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $anestesicos_gerais_e_oxigenio->id,
                'sub_classe_medicamento_id' => $medicamentos_injectaveis->id
            ],
            [
                'codigo' => '0772',
                'dosagem' => '0,5% (cloridrato) sem conservante, 20 ml',
                'nome_generico_medicamento_id' => $bupivacaina->id,
                'forma_medicamento_id' => $injectavel->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $anestesicos_locais->id,
                'sub_classe_medicamento_id' => null
            ],


            [
                'codigo' => '0773',
                'dosagem' => '0,5% (cloridrato) com glicose a 7,5%, ampola 4 ml',
                'nome_generico_medicamento_id' => $Bupivacaina_hiperbarica->id,
                'forma_medicamento_id' => $injectavel->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $anestesicos_locais->id,
                'sub_classe_medicamento_id' => null
            ],
            [
                'codigo' => '0774',
                'dosagem' => '100 mg/ 20 ml 1:200.000',
                'nome_generico_medicamento_id' => $bupivacaina_adrenalina->id,
                'forma_medicamento_id' => $injectavel->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $anestesicos_locais->id,
                'sub_classe_medicamento_id' => null
            ],
            [
                'codigo' => '0775',
                'dosagem' => '100 ml',
                'nome_generico_medicamento_id' => $cloreto_e_etilo->id,
                'forma_medicamento_id' => $spray->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $anestesicos_locais->id,
                'sub_classe_medicamento_id' => $medicamentos_injectaveis->id
            ],
            [
                'codigo' => '0776',
                'dosagem' => '1,8 ml',
                'nome_generico_medicamento_id' => $lidocaina->id,
                'forma_medicamento_id' => $carpule_para_uso_dentario->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $anestesicos_locais->id,
                'sub_classe_medicamento_id' => null
            ],


            [
                'codigo' => '0777',
                'dosagem' => '0,5 mg(sulfato), ampola de 1 ml',
                'nome_generico_medicamento_id' => $atropina->id,
                'forma_medicamento_id' => $injectavel->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $medicacao_pre_operatoria_e_sedacao_para_procedimentos_de_curta_duracao->id,
                'sub_classe_medicamento_id' => $adjuvante_na_anestesia->id
            ],
            [
                'codigo' => '0778',
                'dosagem' => '50 mg / ml',
                'nome_generico_medicamento_id' => $efedrina->id,
                'forma_medicamento_id' => $injectavel->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $medicacao_pre_operatoria_e_sedacao_para_procedimentos_de_curta_duracao->id,
                'sub_classe_medicamento_id' => $adjuvante_na_anestesia->id
            ],
            [
                'codigo' => '0779',
                'dosagem' => '0,5 mg / 10 ml',
                'nome_generico_medicamento_id' => $midazolam->id,
                'forma_medicamento_id' => $comprimido->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $medicacao_pre_operatoria_e_sedacao_para_procedimentos_de_curta_duracao->id,
                'sub_classe_medicamento_id' => $adjuvante_na_anestesia_benzodiazepina->id
            ],
            [
                'codigo' => '0780',
                'dosagem' => '25mg / 5mg',
                'nome_generico_medicamento_id' => $fentanil->id,
                'forma_medicamento_id' => $injectavel->id,
                'grupo_medicamento_id' => $anestesicos->id,
                'sub_grupo_medicamento_id' => $medicacao_pre_operatoria_e_sedacao_para_procedimentos_de_curta_duracao->id,
                'sub_classe_medicamento_id' => $adjuvante_na_anestesia_analgesico_opioide->id
            ],



            [
                'codigo' => '0781',
                'dosagem' => '100 mg',
                'nome_generico_medicamento_id' => $acido_acetilsalicilico->id,
                'forma_medicamento_id' => $comprimido->id,
                'grupo_medicamento_id' => $medicamentos_para_dor_e_cuidados_paliativos->id,
                'sub_grupo_medicamento_id' => $analgesicos_e_anti_inflamatorios_nao_esteroides->id,
                'sub_classe_medicamento_id' => null
            ],
            [
                'codigo' => '0782',
                'dosagem' => '75 mg / 3 ml',
                'nome_generico_medicamento_id' => $diclofenac->id,
                'forma_medicamento_id' => $comprimido->id,
                'grupo_medicamento_id' => $medicamentos_para_dor_e_cuidados_paliativos->id,
                'sub_grupo_medicamento_id' => $analgesicos_e_anti_inflamatorios_nao_esteroides->id,
                'sub_classe_medicamento_id' => null
            ],
            [
                'codigo' => '0783',
                'dosagem' => '200 mg',
                'nome_generico_medicamento_id' => $ibuprofeno->id,
                'forma_medicamento_id' => $comprimido->id,
                'grupo_medicamento_id' => $medicamentos_para_dor_e_cuidados_paliativos->id,
                'sub_grupo_medicamento_id' => $analgesicos_e_anti_inflamatorios_nao_esteroides->id,
                'sub_classe_medicamento_id' => null
            ],
            [
                'codigo' => '0784',
                'dosagem' => '500 mg',
                'nome_generico_medicamento_id' => $paracetamol->id,
                'forma_medicamento_id' => $comprimido->id,
                'grupo_medicamento_id' => $medicamentos_para_dor_e_cuidados_paliativos->id,
                'sub_grupo_medicamento_id' => $analgesicos_e_anti_inflamatorios_nao_esteroides->id,
                'sub_classe_medicamento_id' => null
            ]

        ]);
    }
}
