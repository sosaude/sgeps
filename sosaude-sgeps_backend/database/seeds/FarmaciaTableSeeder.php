<?php

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class FarmaciaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::create(['nome' => 'Farmácia Marrar']);
        $tenant_vulcain = Tenant::create(['nome' => 'Vulcain']);
        
        DB::table('farmacias')->insert([
            ['nome' => 'Farmácia Marrar', 'endereco' => 'Av Samora Machel 278', 'activa' => 1, 'contactos' => json_encode(['840000000', '820000000']), 'latitude' => '-25.955802', 'longitude' => '32.673578', 'numero_alvara' => '755744', 'data_alvara_emissao' => '2002-04-12', 'observacoes' => '', 'tenant_id' => $tenant->id,
                'horario_funcionamento' => json_encode([
                    ['dia' => 'Domingo', 'estado' => 0],
                    ['dia' => 'Segunda-Feira', 'estado' => 1, 'abertura' => '08:00', 'enceramento' => '17:30'],
                    ['dia' => 'Terca-Feira', 'estado' => 1, 'abertura' => '08:00', 'enceramento' => '17:30'],
                    ['dia' => 'Quarta-Feira', 'estado' => 1, 'abertura' => '08:00', 'enceramento' => '17:30'],
                    ['dia' => 'Quinta-Feira', 'estado' => 1, 'abertura' => '08:00', 'enceramento' => '17:30'],
                    ['dia' => 'Sexta-Feira', 'estado' => 1, 'abertura' => '08:00', 'enceramento' => '17:30'],
                    ['dia' => 'Sabado-Feira', 'estado' => 1, 'abertura' => '08:00', 'enceramento' => '17:30'],
                ]),
            ],

            ['nome' => 'Vulcain', 'endereco' => 'Av Lurdes Mutola', 'activa' => 1, 'contactos' => json_encode(['840000000', '820000000']), 'latitude' => '-25.155802', 'longitude' => '32.173578', 'numero_alvara' => '855744', 'data_alvara_emissao' => '2002-04-12', 'observacoes' => '', 'tenant_id' => $tenant_vulcain->id,
                'horario_funcionamento' => json_encode([
                    ['dia' => 'Domingo', 'estado' => 0],
                    ['dia' => 'Segunda-Feira', 'estado' => 1, 'abertura' => '08:00', 'enceramento' => '17:30'],
                    ['dia' => 'Terca-Feira', 'estado' => 1, 'abertura' => '08:00', 'enceramento' => '17:30'],
                    ['dia' => 'Quarta-Feira', 'estado' => 1, 'abertura' => '08:00', 'enceramento' => '17:30'],
                    ['dia' => 'Quinta-Feira', 'estado' => 1, 'abertura' => '08:00', 'enceramento' => '17:30'],
                    ['dia' => 'Sexta-Feira', 'estado' => 1, 'abertura' => '08:00', 'enceramento' => '17:30'],
                    ['dia' => 'Sabado-Feira', 'estado' => 1, 'abertura' => '08:00', 'enceramento' => '17:30'],
                ]),
            ],
        ]);
    }
}
