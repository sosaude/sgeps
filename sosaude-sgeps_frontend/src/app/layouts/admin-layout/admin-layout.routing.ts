import { Routes, RouterModule } from '@angular/router';
import { AuthGuard } from 'src/app/_guards/auth.guard';
import { NgModule } from '@angular/core';
import { OverviewComponent } from 'src/app/overview/overview.component';
import { FarmaciaComponent } from 'src/app/farmacia/farmacia.component';
import { FarmaceuticosComponent } from 'src/app/farmaceuticos/farmaceuticos.component';
import { MedicamentosComponent } from 'src/app/medicamentos/medicamentos.component';
import { EmpresasComponent } from 'src/app/empresas/empresas.component';
import { SugestoesComponent } from 'src/app/sugestoes/sugestoes.component';
import { UtilizadoresComponent } from 'src/app/utilizadores/utilizadores.component';
import { ClinicasComponent } from 'src/app/clinicas/clinicas.component';
import { EmpresasUtilizadorComponent } from 'src/app/empresas-utilizador/empresas-utilizador.component';
import { ClinicasUtilizadorComponent } from 'src/app/clinicas-utilizador/clinicas-utilizador.component';
import { ServicosClinicaComponent } from 'src/app/servicos-clinica/servicos-clinica.component';
import { OverviewEmpresaComponent } from 'src/app/gestao-empresa/overview-empresa/overview-empresa.component';
import { BeneficiariosComponent } from 'src/app/gestao-empresa/beneficiarios/beneficiarios.component';
import { DependentesComponent } from 'src/app/gestao-empresa/dependentes/dependentes.component';
import { ConfiguracoesComponent } from 'src/app/gestao-empresa/configuracoes/configuracoes.component';
import { UtilizadorEmpresaComponent } from 'src/app/gestao-empresa/utilizador-empresa/utilizador-empresa.component';
import { AprovacoesEmpresaComponent } from 'src/app/gestao-empresa/gastos/aprovacoes-empresa/aprovacoes-empresa.component';
import { ReembolsosEmpresaComponent } from 'src/app/gestao-empresa/gastos/reembolsos-empresa/reembolsos-empresa.component';
import { BaixasEmpresaComponent } from 'src/app/gestao-empresa/gastos/baixas-empresa/baixas-empresa.component';
import { GestaoStockComponent } from 'src/app/gestao-unidadeSanitaria/gestao-stock/gestao-stock.component';
import { UsOverviewComponent } from 'src/app/gestao-unidadeSanitaria/us-overview/us-overview.component';
import { GestaoServicosComponent } from 'src/app/gestao-unidadeSanitaria/gestao-servicos/gestao-servicos.component';
import { FarmaciaOverviewComponent } from 'src/app/gestao-unidadeSanitaria/farmacia-overview/farmacia-overview.component';
export const AdminLayoutRoutes: Routes = [
  { path: '', redirectTo: '/farmacias', pathMatch: 'full' },
 
  // ======== ROUTES ====================

  // ======== ADMINISTRADOR ====================
  { path: 'overview', component: OverviewComponent, canActivate: [AuthGuard] },
  { path: 'farmacias', component: FarmaciaComponent, canActivate: [AuthGuard] },
  { path: 'farmaceuticos', component: FarmaceuticosComponent, canActivate: [AuthGuard] },
  { path: 'clinicas', component: ClinicasComponent, canActivate: [AuthGuard] },
  { path: 'medicamentos', component: MedicamentosComponent, canActivate: [AuthGuard] },
  { path: 'empresas', component: EmpresasComponent, canActivate: [AuthGuard] },
  { path: 'sugestoes', component: SugestoesComponent, canActivate: [AuthGuard] },
  { path: 'utilizadores', component: UtilizadoresComponent, canActivate: [AuthGuard] },
  { path: 'utilizador-empresas', component: EmpresasUtilizadorComponent, canActivate: [AuthGuard] },
  { path: 'utilizador-clinicas', component: ClinicasUtilizadorComponent, canActivate: [AuthGuard] },
  { path: 'servicos-clinicas', component: ServicosClinicaComponent, canActivate: [AuthGuard] },

  // ======== EMPRESA ====================
  {
    path: 'empresa', children: [
      { path: 'overview', component: OverviewEmpresaComponent, canActivate: [AuthGuard] },
      { path: 'beneficiarios', component: BeneficiariosComponent, canActivate: [AuthGuard]  },
      { path: 'dependentes', component: DependentesComponent, canActivate: [AuthGuard]  },
      { path: 'configuracoes', component: ConfiguracoesComponent, canActivate: [AuthGuard]  },
      {
        path: 'gastos', children: [
          { path: 'baixas', component: BaixasEmpresaComponent, canActivate: [AuthGuard] },
          { path: 'reembolsos', component: ReembolsosEmpresaComponent, canActivate: [AuthGuard] },
          { path: 'aprovacoes', component: AprovacoesEmpresaComponent },
        ]
      },
    ]
  },

  // ======== UNIDADE SANITÁRIA ====================
  {
    path: 'unidade-sanitaria', children: [
      { path: 'farm-geral', component: FarmaciaOverviewComponent, canActivate: [AuthGuard] },
      { path: 'us-geral', component: UsOverviewComponent, canActivate: [AuthGuard] },
      { path: 'servicos', component: GestaoServicosComponent, canActivate: [AuthGuard]  },
      { path: 'stock', component: GestaoStockComponent, canActivate: [AuthGuard]  },
    ]
  }

];

@NgModule({
  imports: [RouterModule.forChild(AdminLayoutRoutes)],
  exports: [RouterModule]
})
export class AdminLayoutRoutingModule { }
