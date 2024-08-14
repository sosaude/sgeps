<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NomeGenericoMedicamentoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('nome_generico_medicamentos')->insert([
            ['nome' => 'Halotano'],
            ['nome' => 'Isoflurano'],
            ['nome' => 'Protóxido de azoto ou óxido nitroso'],
            ['nome' => 'Oxigénio'],
            ['nome' => 'Ketamina'],
            ['nome' => 'Propofol'],
            ['nome' => 'Tiopental'],
            ['nome' => 'Bupivacaína'],
            ['nome' => 'Bupivacaína hiperbárica'],
            ['nome' => 'Bupivacaína + Adrenalina'],
            ['nome' => 'Cloreto de etilo'],
            ['nome' => 'Lidocaína'],
            ['nome' => 'Lidocaína + Adrenalina'],
            ['nome' => 'Atropina'],
            ['nome' => 'Efedrina'],
            ['nome' => 'Midazolam'],
            ['nome' => 'Fentanil'],
            ['nome' => 'Morfina'],
            ['nome' => 'Ácido Acetilsalicílico'],
            ['nome' => 'Diclofenac'],
            ['nome' => 'Ibuprofeno'],
            ['nome' => 'Paracetamol'],
            ['nome' => 'Codeína'],
            ['nome' => 'Tramadol'],
            ['nome' => 'Ciclizina'],
            ['nome' => 'Metoclopramida'],
            ['nome' => 'Dexametasona'],
            ['nome' => 'Diazepam'],
            ['nome' => 'Docusato de sódio'],
            ['nome' => 'Lactulose'],
            ['nome' => 'Sene'],
            ['nome' => 'Butilescopolamina (Hioscine Butilbromide)'],
            ['nome' => 'Haloperidol'],
            ['nome' => 'Loperamida'],
        ]);
    }
}
