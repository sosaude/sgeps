<?php

use App\Models\Seccao;
use Illuminate\Database\Seeder;

class PermissaoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seccao_empresa_id = Seccao::where('code', 2)->pluck('id')->first();
        $seccao_farmacia_id = Seccao::where('code', 3)->pluck('id')->first();
        $seccao_unidade_sanitaria_id = Seccao::where('code', 4)->pluck('id')->first();

        DB::table('permissaos')->insert([
            ['nome' => 'gerir utilizador', 'codigo' => 1, 'seccao_id' => $seccao_empresa_id],
            ['nome' => 'gerir grupo beneficiário', 'codigo' => 2, 'seccao_id' => $seccao_empresa_id],
            ['nome' => 'gerir beneficiário', 'codigo' => 3,  'seccao_id' => $seccao_empresa_id],
            ['nome' => 'gerir plano de saúde', 'codigo' => 4,  'seccao_id' => $seccao_empresa_id],
            ['nome' => 'gerir farmácia', 'codigo' => 5, 'seccao_id' => $seccao_empresa_id],
            ['nome' => 'gerir unidade sanitária', 'codigo' => 6, 'seccao_id' => $seccao_empresa_id],
            ['nome' => 'gerir baixa', 'codigo' => 7, 'seccao_id' => $seccao_empresa_id],
            ['nome' => 'gerir pedido aprovação', 'codigo' => 8, 'seccao_id' => $seccao_empresa_id],
            ['nome' => 'gerir pedido reembolso', 'codigo' => 9, 'seccao_id' => $seccao_empresa_id],
            ['nome' => 'gerir dependente beneficiário', 'codigo' => 10, 'seccao_id' => $seccao_empresa_id],

            ['nome' => 'gerir utilizador', 'codigo' => 100, 'seccao_id' => $seccao_farmacia_id],
            ['nome' => 'gerir perfil', 'codigo' => 101, 'seccao_id' => $seccao_farmacia_id],
            ['nome' => 'gerir stock', 'codigo' => 102, 'seccao_id' => $seccao_farmacia_id],
            ['nome' => 'gerir baixa', 'codigo' => 103, 'seccao_id' => $seccao_farmacia_id],
            ['nome' => 'gerir verificação beneficiário', 'codigo' => 104, 'seccao_id' => $seccao_farmacia_id],
            ['nome' => 'gerir pedido aprovação', 'codigo' => 105, 'seccao_id' => $seccao_farmacia_id],
            ['nome' => 'gerir ordem reserva', 'codigo' => 106, 'seccao_id' => $seccao_farmacia_id],
            ['nome' => 'gerir sugestão', 'codigo' => 107, 'seccao_id' => $seccao_farmacia_id],

            ['nome' => 'gerir utilizador', 'codigo' => 200, 'seccao_id' => $seccao_unidade_sanitaria_id],
            ['nome' => 'gerir perfil', 'codigo' => 201, 'seccao_id' => $seccao_unidade_sanitaria_id],
            ['nome' => 'gerir serviço', 'codigo' => 202, 'seccao_id' => $seccao_unidade_sanitaria_id],
            ['nome' => 'gerir baixa', 'codigo' => 203, 'seccao_id' => $seccao_unidade_sanitaria_id],
            ['nome' => 'gerir verificação beneficiário', 'codigo' => 204, 'seccao_id' => $seccao_unidade_sanitaria_id],
            ['nome' => 'gerir pedido aprovação', 'codigo' => 205, 'seccao_id' => $seccao_unidade_sanitaria_id],
            ['nome' => 'gerir ordem reserva', 'codigo' => 206, 'seccao_id' => $seccao_unidade_sanitaria_id],
            
        ]);
    }
}
