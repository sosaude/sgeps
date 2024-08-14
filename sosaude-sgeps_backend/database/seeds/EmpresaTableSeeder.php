<?php

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use App\Models\CategoriaEmpresa;

class EmpresaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::create(['nome' => 'Empresa Marrar']);
        $categoria_empresa = CategoriaEmpresa::where('nome', 'Empresa')->first();

        DB::table('empresas')->insert([
            ['nome' => 'Empresa Marrar','categoria_empresa_id' => $categoria_empresa->id, 'endereco' => 'Rua Milagre Mabote, 1245, Maputo', 'email' => 'empresamarrar@gmail.com', 'nuit' => 123904788, 'contactos' => json_encode([8284734, 8273645]), 'delegacao' => 'Maputo', 'latitude' => '-25.855802', 'longitude' => '32.473578', 'tenant_id' => $tenant->id]
        ]);
    }
}
