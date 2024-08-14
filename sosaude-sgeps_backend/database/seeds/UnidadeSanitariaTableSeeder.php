<?php

use App\Models\CategoriaUnidadeSanitaria;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnidadeSanitariaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::create(['nome' => 'Clínica Marrar']);
        $tenant_simi = Tenant::create(['nome' => 'Simi']);
        $categoria_unidade_sanitaria_id = CategoriaUnidadeSanitaria::where('codigo', 1)->pluck('id')->first();

        DB::table('unidade_sanitarias')->insert([
            ['categoria_unidade_sanitaria_id' => $categoria_unidade_sanitaria_id, 'nome' => 'Clínica Marrar', 'endereco' => 'Rua Standar, Matola, 245', 'email' => 'clinicamarrar@gmail.com', 'nuit' => 123456789, 'contactos' => json_encode([8284734, 8273645]), 'latitude' => '-25.975802', 'longitude' => '32.573578', 'tenant_id' => $tenant->id],
            ['categoria_unidade_sanitaria_id' => $categoria_unidade_sanitaria_id, 'nome' => 'Simi', 'endereco' => 'Rua Jó, Maputo, 245', 'email' => 'simi@gmail.com', 'nuit' => 123456789, 'contactos' => json_encode([8284734, 8273645]), 'latitude' => '-24.88802', 'longitude' => '31.573578', 'tenant_id' => $tenant_simi->id]
        ]);
    }
}
