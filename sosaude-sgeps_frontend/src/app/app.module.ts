import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { AppRoutingModule } from './app.routing';
import { ComponentsModule } from './components/components.module';
import { AppComponent } from './app.component';
import { AdminLayoutComponent } from './layouts/admin-layout/admin-layout.component';
import { LoginComponent } from './login/login.component';
import { BrowserModule } from '@angular/platform-browser';
import {
  MatAutocompleteModule,
  MatButtonModule,
  MatButtonToggleModule,
  MatCardModule,
  MatCheckboxModule,
  MatChipsModule,
  MatDatepickerModule,
  MatDialogModule,
  MatExpansionModule, 
  MatGridListModule,
  MatIconModule,
  MatInputModule,
  MatListModule,
  MatMenuModule,
  MatNativeDateModule,
  MatPaginatorModule,
  MatProgressBarModule,
  MatProgressSpinnerModule,
  MatRadioModule,
  MatRippleModule,
  MatSelectModule,
  MatSidenavModule,
  MatSliderModule,
  MatSlideToggleModule,
  MatSnackBarModule,
  MatSortModule,
  MatTableModule,
  MatTabsModule,
  MatToolbarModule,
  MatTooltipModule,
  MatStepperModule,
  MatFormFieldModule,
} from '@angular/material';
import { AuthenticationService } from './_services/authentication.service';
import { AlertService } from './_services/alert.service';
import { JwtInterceptor } from './_helpers/jwt.interceptor';
import { ErrorInterceptor } from './_helpers/error.interceptor';
import { FarmaceuticosService } from './_services/farmaceuticos.service';
import { Ng2SearchPipeModule } from 'ng2-search-filter';
import { FarmaciasService } from './_services/farmacias.service';
import { MedicamentosService } from './_services/medicamentos.service';
import { EmpresasService } from './_services/empresas.service';
import { UtilizadorService } from './_services/utilizador.service';
import { SugestoesService } from './_services/sugestoes.service';
import { ClinicasService } from './_services/clinicas.service';
import { ServicosService } from './_services/servicos.service';
import { GruposService } from './_services/grupos.service';
import { ConfiguracoesEmpresaService } from './_services/configuracoes-empresa.service';
import { GastosService } from './_services/gastos.service';
import { PlanoSaudeService } from './_services/plano-saude.service';
import { UsGestaoStockService } from './_services/us-gestao-stock.service';
import { OrcamentosEmpresaService } from './_services/orcamentos-empresa.service';

@NgModule({
  imports: [
    BrowserAnimationsModule,
    BrowserModule,
    FormsModule,
    HttpClientModule,
    ComponentsModule,
    RouterModule,
    ReactiveFormsModule,
    AppRoutingModule,
    MatAutocompleteModule,
    MatButtonModule,
    MatButtonToggleModule,
    MatCardModule,
    MatCheckboxModule,
    MatChipsModule,
    MatDatepickerModule,
    MatDialogModule,
    MatExpansionModule,
    MatGridListModule,
    MatIconModule,
    MatInputModule,
    MatListModule,
    MatMenuModule,
    MatNativeDateModule,
    MatPaginatorModule,
    MatProgressBarModule,
    MatProgressSpinnerModule,
    MatRadioModule,
    MatRippleModule,
    MatSelectModule,
    MatSidenavModule,
    MatSliderModule,
    MatSlideToggleModule,
    MatSnackBarModule,
    MatSortModule,
    MatTableModule,
    MatTabsModule,
    MatToolbarModule,
    MatTooltipModule,
    MatStepperModule,
    MatFormFieldModule,
    Ng2SearchPipeModule
    ],
  declarations: [
    LoginComponent,
    AppComponent,
    AdminLayoutComponent,
    
    ],
    
  providers: [
    AuthenticationService, AlertService, FarmaceuticosService, FarmaciasService, MedicamentosService, EmpresasService, 
    UtilizadorService, SugestoesService, ClinicasService, ServicosService, GruposService, ConfiguracoesEmpresaService,
    GastosService, PlanoSaudeService, UsGestaoStockService,OrcamentosEmpresaService,
    { provide: HTTP_INTERCEPTORS, useClass: JwtInterceptor, multi: true },
    { provide: HTTP_INTERCEPTORS, useClass: ErrorInterceptor, multi: true }
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
