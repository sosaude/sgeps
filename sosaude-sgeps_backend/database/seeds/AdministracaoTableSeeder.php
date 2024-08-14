<?php

use Carbon\Carbon;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class AdministracaoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::create(['nome' => 'Administração']);

        DB::table('administracaos')->insert([
            ['nome' => 'Administração', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tenant_id' => $tenant->id]
        ]);
    }
}
