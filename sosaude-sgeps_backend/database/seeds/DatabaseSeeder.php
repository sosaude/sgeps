<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call(
            [
                ContinenteTableSeeder::class,
                PaisTableSeeder::class,
                SeccaoTableSeeder::class,
                PermissaoTableSeeder::class,
                RoleTableSeeder::class,
                EstadoBaixaTableSeeder::class,
                CategoriaEmpresaTableSeeder::class,
                CategoriaUnidadeSanitariaTableSeeder::class,
                AdministracaoTableSeeder::class,
                FarmaciaTableSeeder::class,
                ClinicaTableSeeder::class,
                UnidadeSanitariaTableSeeder::class,
                EmpresaTableSeeder::class,

                // Formas Marcas Medicamentos, Medicamento
                GrupoMedicamentoTableSeeder::class,
                SubGrupoMedicamentoTableSeeder::class,
                SubClasseMedicamentoTableSeeder::class,
                FormaMedicamentoTableeeder::class,
                NomeGenericoMedicamentoTableSeeder::class,
                MedicamentoTableSeeder::class,
                MarcaMedicamentoTableSeeder::class,
                CategoriaServicoTableSeeder::class,
                ServicoTableSeeder::class,


                // Tenant
                TenantTableSeeder::class,

                // Dependes on Tenant
                GrupoBeneficiarioTableSeeder::class,
                DoencaCronicaTableSeeder::class,

                // Utilizadores
                UtilizadorAdministracaoTableSeeder::class,
                UtilizadorFarmaciaTableSeeder::class,
                UtilizadorClinicaTableSeeder::class,
                UtilizadorEmpresaTableSeeder::class,
                UtilizadorUnidadeSanitariaTableSeeder::class,
                // UserTableSeeder::class,

                // Beneficiario & DependenteBeneficiario
                BeneficiarioTablaSeeder::class,

                // Cliente Mobile
                ClienteTableSeeder::class,

                // AccaoBaixa
                AccaoBaixaTableSeeder::class,


                //BaixaFarmacia
                BaixaFarmaciaTablaSeeder::class,
                ItenBaixaFarmaciaTablaSeeder::class,

                //BaixaUnidadeSanitaria
                BaixaUnidadeSanitariaTableSeeder::class,
                ItenBaixaUnidadeSanitariaTableSeeder::class,

                // GastoPedidoReembolso
                GastoReembolsoTableSeeder::class,
                
                // PedidoReembolso
                EstadoPedidoReembolsoTableSeeder::class,
                PedidoReembolsoTableSeeder::class,

            ]
        );
    }
}
