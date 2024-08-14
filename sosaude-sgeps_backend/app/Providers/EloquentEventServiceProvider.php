<?php

namespace App\Providers;

use App\Models\BaixaFarmacia;
use App\Models\BaixaUnidadeSanitaria;
use App\Models\User;
use App\Models\Clinica;
use App\Models\Empresa;
use App\Models\Farmacia;
use App\Models\Beneficiario;
use App\Observers\UserObserver;
use App\Models\UtilizadorClinica;
use App\Models\UtilizadorEmpresa;
use App\Models\UtilizadorFarmacia;
use App\Observers\ClinicaObserver;
use App\Observers\EmpresaObserver;
use App\Observers\FarmaciaObserver;
use App\Models\DependenteBeneficiario;
use App\Models\EmpresaFarmaciaPivot;
use App\Models\Medicamento;
use App\Models\PedidoReembolso;
use App\Models\PlanoSaude;
use App\Models\Servico;
use App\Models\UnidadeSanitaria;
// use App\Observers\ClinicaObserver\Clinica;
use App\Models\UtilizadorAdministracao;
use App\Observers\BeneficiarioObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\UtilizadorUnidadeSanitaria;
use App\Observers\BaixaFarmaciaObserver;
use App\Observers\BaixaUnidadeSanitariaObserver;
use App\Observers\UtilizadorClinicaObserver;
use App\Observers\UtilizadorEmpresaObserver;
use App\Observers\UtilizadorFarmaciaObserver;
use App\Observers\DependenteBeneficiarioObserver;
use App\Observers\EmpresaFarmaciaPivotObserver;
use App\Observers\MedicamentoObserver;
use App\Observers\PedidoReembolsoObserver;
use App\Observers\PlanoSaudeObserver;
use App\Observers\ServicoObserver;
use App\Observers\UnidadeSanitariaObserver;
use App\Observers\UtilizadorAdministracaoObserver;
use App\Observers\UtilizadorUnidadeSanitariaObserver;

class EloquentEventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        
        // Tenants
        Empresa::observe(EmpresaObserver::class);
        Farmacia::observe(FarmaciaObserver::class);
        Clinica::observe(ClinicaObserver::class);
        UnidadeSanitaria::observe(UnidadeSanitariaObserver::class);
        Medicamento::observe(MedicamentoObserver::class);
        Servico::observe(ServicoObserver::class);
        PlanoSaude::observe(PlanoSaudeObserver::class);
        // Empresa::observe(EmpresaObserver::class);

        // Baixas
        BaixaFarmacia::observe(BaixaFarmaciaObserver::class);
        BaixaUnidadeSanitaria::observe(BaixaUnidadeSanitariaObserver::class);
        PedidoReembolso::observe(PedidoReembolsoObserver::class);

        // Utilizadores
        UtilizadorEmpresa::observe(UtilizadorEmpresaObserver::class);
        UtilizadorFarmacia::observe(UtilizadorFarmaciaObserver::class);
        UtilizadorClinica::observe(UtilizadorClinicaObserver::class);
        UtilizadorUnidadeSanitaria::observe(UtilizadorUnidadeSanitariaObserver::class);
        UtilizadorAdministracao::observe(UtilizadorAdministracaoObserver::class);
        Beneficiario::observe(BeneficiarioObserver::class);
        DependenteBeneficiario::observe(DependenteBeneficiarioObserver::class);
        User::observe(UserObserver::class);

        // Pivots
        EmpresaFarmaciaPivot::observe(EmpresaFarmaciaPivotObserver::class);
    }
}
