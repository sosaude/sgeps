import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { AdminLayoutRoutes } from './admin-layout.routing';
import { BrowserModule } from '@angular/platform-browser';
import { HttpClientModule } from '@angular/common/http';
import { MatProgressBarModule } from '@angular/material/progress-bar';
import { MatFormFieldModule, MatInputModule,MatCheckboxModule, MatSelectModule, MatOptionModule, MatStepperModule, MatMenuModule, MatIconModule, MatTooltipModule, MatButtonModule, MatRippleModule, MatTableModule, MatSortModule, MatDatepickerModule, MatNativeDateModule, MatPaginatorModule, MatSlideToggleModule, MatProgressSpinnerModule, MatTabsModule, MatDialogModule, MatRadioModule, MatAutocompleteModule } from '@angular/material';
import { ComponentsModule } from 'src/app/components/components.module';
import { ChangePasswordComponent } from 'src/app/change-password/change-password.component';
import { NgxMaskModule, IConfig } from 'ngx-mask';
import { OverviewComponent } from 'src/app/overview/overview.component';
import { FarmaciaComponent } from 'src/app/farmacia/farmacia.component';
import { FarmaceuticosComponent } from 'src/app/farmaceuticos/farmaceuticos.component';
import { MedicamentosComponent } from 'src/app/medicamentos/medicamentos.component';
import { Ng2SearchPipeModule } from 'ng2-search-filter';
import { EmpresasComponent } from 'src/app/empresas/empresas.component';
import { SugestoesComponent } from 'src/app/sugestoes/sugestoes.component';
import { UtilizadoresComponent } from 'src/app/utilizadores/utilizadores.component';
import { ClinicasComponent } from 'src/app/clinicas/clinicas.component';
import { ClinicasUtilizadorComponent } from 'src/app/clinicas-utilizador/clinicas-utilizador.component';
import { EmpresasUtilizadorComponent } from 'src/app/empresas-utilizador/empresas-utilizador.component';
import { ServicosClinicaComponent } from 'src/app/servicos-clinica/servicos-clinica.component';
import { BeneficiariosComponent } from 'src/app/gestao-empresa/beneficiarios/beneficiarios.component';
import { DependentesComponent } from 'src/app/gestao-empresa/dependentes/dependentes.component';
import { OverviewEmpresaComponent } from 'src/app/gestao-empresa/overview-empresa/overview-empresa.component';
import { NgbAlertModule, NgbPaginationModule } from '@ng-bootstrap/ng-bootstrap';
import { ConfiguracoesComponent } from 'src/app/gestao-empresa/configuracoes/configuracoes.component';
import { UtilizadorEmpresaComponent } from 'src/app/gestao-empresa/utilizador-empresa/utilizador-empresa.component';
import { BaixasEmpresaComponent } from 'src/app/gestao-empresa/gastos/baixas-empresa/baixas-empresa.component';
import { ReembolsosEmpresaComponent } from 'src/app/gestao-empresa/gastos/reembolsos-empresa/reembolsos-empresa.component';
import { AprovacoesEmpresaComponent } from 'src/app/gestao-empresa/gastos/aprovacoes-empresa/aprovacoes-empresa.component';
import { PlanoSaudeComponent } from 'src/app/gestao-empresa/plano-saude/plano-saude.component';
import { OrcamentosComponent } from 'src/app/gestao-empresa/orcamentos/orcamentos.component';
import { GestaoServicosComponent } from 'src/app/gestao-unidadeSanitaria/gestao-servicos/gestao-servicos.component';
import { UsOverviewComponent } from 'src/app/gestao-unidadeSanitaria/us-overview/us-overview.component';
import { GestaoStockComponent } from 'src/app/gestao-unidadeSanitaria/gestao-stock/gestao-stock.component';
import {NgxPaginationModule} from 'ngx-pagination';
import { FarmaciaOverviewComponent } from 'src/app/gestao-unidadeSanitaria/farmacia-overview/farmacia-overview.component';
import { TransacoesComponent } from 'src/app/gestao-unidadeSanitaria/transacoes/transacoes.component';
import { UsUtilizadoresComponent } from 'src/app/gestao-unidadeSanitaria/us-utilizadores/us-utilizadores.component';
import { ResumoGraficoComponent } from 'src/app/gestao-unidadeSanitaria/resumo-grafico/resumo-grafico.component';

export const options: Partial<IConfig> | (() => Partial<IConfig>) = {};
// options = null;

@NgModule({
  imports: [
    CommonModule,
    RouterModule.forChild(AdminLayoutRoutes),
    NgxMaskModule.forRoot(options),
    FormsModule,
    // BrowserModule,
    HttpClientModule,
    ComponentsModule,
    RouterModule,
    ReactiveFormsModule,
    MatButtonModule,
    MatRippleModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatTooltipModule,
    MatTableModule,
    MatSortModule,
    MatDatepickerModule,
    MatNativeDateModule,
    MatPaginatorModule,
    ReactiveFormsModule,
    MatStepperModule,
    MatStepperModule, MatProgressBarModule,
    MatSlideToggleModule,
    MatProgressSpinnerModule,
    MatTabsModule,
    MatOptionModule, MatMenuModule, MatIconModule,
    MatDialogModule,
    MatRadioModule,
    MatAutocompleteModule,
    MatCheckboxModule,
    Ng2SearchPipeModule,
    NgbPaginationModule,
    NgbAlertModule,
    NgxPaginationModule
  ],

  declarations: [
    ChangePasswordComponent,
    // ===========================
    OverviewComponent,
    FarmaciaComponent,
    FarmaceuticosComponent,
    MedicamentosComponent,
    EmpresasComponent,
    SugestoesComponent,
    UtilizadoresComponent,
    ClinicasComponent,
    ClinicasUtilizadorComponent,
    EmpresasUtilizadorComponent,
    ServicosClinicaComponent,
    BeneficiariosComponent,
    DependentesComponent,
    OverviewEmpresaComponent,
    ConfiguracoesComponent,
    UtilizadorEmpresaComponent,
    BaixasEmpresaComponent,
    ReembolsosEmpresaComponent,
    AprovacoesEmpresaComponent,
    PlanoSaudeComponent,
    OrcamentosComponent,
    GestaoStockComponent,
    GestaoServicosComponent,
    UsOverviewComponent,
    FarmaciaOverviewComponent,
    TransacoesComponent,
    UsUtilizadoresComponent,
    ResumoGraficoComponent
  ],
})

export class AdminLayoutModule { }
 