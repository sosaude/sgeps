<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrupoMedicamentoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('grupo_medicamentos')->insert([
            ['nome' => 'Anestésicos'],
            ['nome' => 'Medicamentos para dor e cuidados paliativos'],
            ['nome' => 'Anti-alérgicos e medicamentos para anafilaxia'],
            ['nome' => 'Antídotos e outras substâncias utilizadas em intoxicações'],
            ['nome' => 'Anticonvulsivantes/antiepilépticos'],
            ['nome' => 'Anti-infecciosos'],
            ['nome' => 'Medicamentos para enxaqueca'],
            ['nome' => 'Antineoplásicos e imunossupressores'],
            ['nome' => 'Medicamentos antiparkinsónicos'],
            ['nome' => 'Medicamentos que afectam o sangue'],
            ['nome' => 'Produtos de sangue de origem humana e substitutos do plasma'],
            ['nome' => 'Medicamentos cardiovasculares'],
            ['nome' => 'Medicamentos dermatológicos (tópicos)'],
            ['nome' => 'Agentes de diagnóstico'],
            ['nome' => 'Desinfectantes e antisépticos'],
            ['nome' => 'Diuréticos'],
            ['nome' => 'Medicamentos gastro-intestinais'],
            ['nome' => 'Hormonas, outros medicamentos endócrinos e contraceptivos'],
            ['nome' => 'Imunológicos'],
            ['nome' => 'Relaxantes musculares (de acção periférica) e inibidores da colinesterase'],
            ['nome' => 'Preparações oftalmológicas'],
            ['nome' => 'Oxitócicos e anti-oxitócicos'],
            ['nome' => 'Solução para diálise peritoneal'],
            ['nome' => 'Medicamentos para distúrbios mentais e comportamentais'],
            ['nome' => 'Medicamentos do tracto respiratório'],
            ['nome' => 'Equilíbrio hidro-electrolítico e ácido-base'],
            ['nome' => 'Vitaminas e minerais'],
            ['nome' => 'Medicamentos para ouvido, nariz e garganta'],
            ['nome' => 'Medicamentos específicos para cuidados neonatais'],
            ['nome' => 'Medicamentos para doenças das articulações'],
            ['nome' => 'Nutrição'],
        ]);
    }
}
