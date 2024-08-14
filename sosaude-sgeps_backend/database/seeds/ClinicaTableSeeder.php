<?php

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ClinicaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::create(['nome' => 'Clínica Marrar']);

        DB::table('clinicas')->insert([
            ['nome' => 'Clínica Marrar', 'endereco' => 'Rua Standar, Matola, 245', 'email' => 'clinicamarrar@gmail.com', 'nuit' => 123456789, 'contactos' => json_encode([8284734, 8273645]), 'tenant_id' => $tenant->id]
        ]);
    }
}
