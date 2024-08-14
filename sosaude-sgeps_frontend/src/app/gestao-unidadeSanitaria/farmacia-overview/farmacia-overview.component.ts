import { Router } from '@angular/router';
import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { GastosService } from 'src/app/_services/gastos.service';
import { FormBuilder, Validators, FormGroup, FormArray } from '@angular/forms';
import { UsGestaoStockService } from 'src/app/_services/us-gestao-stock.service';
import Swal from 'sweetalert2';
import { FarmaciasService } from 'src/app/_services/farmacias.service';
import * as moment from 'moment';
import { SugestoesService } from 'src/app/_services/sugestoes.service';
import { element } from 'protractor';
import { AuthenticationService } from 'src/app/_services/authentication.service';
import { Location } from '@angular/common';
import { Chart } from 'node_modules/chart.js';
import { ExcelService } from 'src/app/_services/excel.service';

@Component({
  selector: 'app-farmacia-overview',
  templateUrl: './farmacia-overview.component.html',
  styleUrls: ['./farmacia-overview.component.scss']
})
export class FarmaciaOverviewComponent implements OnInit {
  
  selectedCardView: number = 0;
  model_pesquisa_beneficiario: string;
  pesquisa_beneficiario_form: FormGroup;
  spinner_all: boolean;
  servicos_unidade_sanitaria: any;
  submitted: boolean = false

  // Verificar se a ordem esta coberta
  show_coberto:  boolean = false;
  baixa: any;
  ordem: any;
  ordens_reserva = {};
  coberto: any;
  pre_autorizacao: any;
  message: string;

  // venda = {

  // }

  model_venda = {
    id: null,
    empresa_id: null,
    nr_comprovativo: '',
    beneficiario_id: null,
    valor: 0,
    itens_baixa: [],
    itens: [],
    beneficio_proprio_beneficiario: null,
    dependente_beneficiario_id: null,
    accao_codigo: null
  }

  model_ordem_reserva_preVenda = {
    empresa_id: null,
    nr_comprovativo: '',
    beneficiario_id: null,
    valor: 0,
    itens_baixa: [],
    beneficio_proprio_beneficiario: null,
    dependente_beneficiario_id: null
  }

  searchText: string = "";
  spinner: boolean = false;

  model_beneficiario_view = {
    beneficiario_id: null,
    beneficiario_nome: "",
    beneficiario_telefone: "",
    empresa_id: null,
    empresa_nome: "",
    foto_perfil: null,
    beneficio_proprio_beneficiario: true,
    dependente_beneficiario_id: null
  }
  model_dependente_view = {
    dependente_beneficiario_id: null,
    dependente_beneficiario_nome: "",
    beneficiario_nome: "",
    dependente_beneficiario_telefone: "",
    empresa_id: null,
    empresa_nome: "",
    foto_perfil: null,
    beneficio_proprio_beneficiario: false
  }
  marcas_medicamentos_farmacia: any[];
  p: number = 1;
  quantidade_venda_item: number = 0;
  model_medicamento_view = {
    marca: "",
    marca_codigo: "",
    marca_medicamento_id: null,
    marca_pais_origem: "",
    medicamento_codigo: "",
    medicamento_dosagem: "",
    medicamento_forma: "",
    medicamento_forma_id: null,
    medicamento_id: null,
    medicamento_nome_generico: "",
    medicamento_nome_generico_id: null,
    preco: null,
    iva: null,
    preco_iva: null,
    quantidade_disponivel: null
  }
  preco_sem_iva_view: any;
  preco_com_iva_view: any;
  form: FormGroup;
  form_sugestao: FormGroup;

  modelData = {
    dia: null,
    mes: null,
    ano: null
  }
  selectdiaMesAnoBoolean: boolean = false;
  editBtnFarmacia: boolean = false;
  horarios: any[] = [
    '--:--', '00:00', '00:30', '01:00', '01:30', '02:00', '02:30', '03:00', '03:30',
    '04:00', '04:30', '05:00', '05:30', '06:00', '06:30', '07:00', '07:30', '08:00',
    '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00',
    '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
    '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00', '21:30',
    '22:00', '22:30', '23:00', '23:30'
  ]
  dias: any[] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31]
  meses: any[] = [
    { nome: 'Janeiro', value: 1 }, { nome: 'Fevereiro', value: 2 },
    { nome: 'Março', value: 3 }, { nome: 'Abril', value: 4 },
    { nome: 'Maio', value: 5 }, { nome: 'Junho', value: 6 },
    { nome: 'Julho', value: 7 }, { nome: 'Agosto', value: 8 },
    { nome: 'Setembro', value: 9 }, { nome: 'Outubro', value: 10 },
    { nome: 'Novembro', value: 11 }, { nome: 'Dezembro', value: 12 }
  ]
  situacao_farmacia: any[] = [
    { nome: 'Activa', value: 1 }, { nome: 'Inativa', value: 0 }
  ]
 
  tipo_sugestoes: any[] = [
    { nome: 'Farmácia' }, { nome: 'Medicamento' },{ nome: 'Unidade Sanitária' }, { nome: 'Serviço' }, { nome: 'Outro' }
  ]

  currentYear = new Date().getFullYear();
  anos: any[] = [];
  searchTextOrdemReserva: any;
  switch_to_ordemReserva: boolean = false;
  switch_to_iniciarVenda: boolean = false;
  ordemReserva_array: any;

  model_baixa = {
    id: null,
    proveniencia: null,
    responsavel: null,
    nome_beneficiario: '',
    nome_instituicao: '',
    valor_baixa: '',
    estado_id: null,
    beneficiario_id: null,
    empresa_id: null,
    estado_nome: '',
    estado_codigo: '',
    data_baixa: '',
    updated_at: '',
    descricao: [],
    comprovativo: [],
    comentario_baixa: [],
    comentario_pedido_aprovacao: [],
    iten_ordem_reserva: [],
    nr_comprovativo: ''
  }

  sugestoesArray: any;
  authValue: any;
  searchTextTransacoes: string = '';
  userRole: number;
  selectedCardViewTransacao: number = 1;
  selectedCardViewPedidos: number = 1;
  show_By_Estado: any[] = [];
  clientes_baixas: any[];
  clientes_baixas_excel: any[];
  clientes_baixas_pedidos_excel: any[];
  spinner_download: boolean = false;
  selectedFiles: File[] = [];
  ordem_reserva_id = null;
  tem_cobertura: boolean = false;
  currentMonth = new Date().getMonth() + 1;

  constructor(private _venda: GastosService,
    private cd: ChangeDetectorRef,
    private formBuilder: FormBuilder,
    private _stock: UsGestaoStockService,
    private _router: Router,
    private _location: Location,
    private _farmacia: FarmaciasService, private _gastos: GastosService,
    private excelService: ExcelService,
    private _sugestoes: SugestoesService, private authenticationService: AuthenticationService) {
    if (this.authenticationService.currentUserValue) {
      this.authValue = this.authenticationService.currentUserValue;
      this.userRole = this.authenticationService.currentUserValue.user.role.codigo;
    }
  }

  ngOnInit() {
    // this.getGraficos();
    this.getResumo();
    this.initializeFormFarmacia();
    this.initializeFormSUgestao();
    this.listaMedicamentos();
    
    this.pesquisa_beneficiario_form = this.formBuilder.group({
      codigo: ['', Validators.required],
    });
    for (let index = 0; index < 80; index++) {
      this.anos.push(this.currentYear)
      this.currentYear = this.currentYear - 1;
    }
    
  }

  exportAsXLSX(): void { 
    this.spinner = true;
    this.excelService.exportAsExcelFile(this.clientes_baixas, 'Transacoes');
    this.spinner = false;
    // console.log(this.dependentes)
  }

  getResumo() {



  }



  // getGraficos(){
  //   const ctx = document.getElementById('myChart');
  //   const myChart = new Chart(ctx, {
  //     type: 'bar',
  //     data: {
  //       labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
  //       datasets: [{
  //         label: 'Número de Processos mensais',
  //         data: [20, 62, 31, 21, 41, 21, 28, 7, 12, 19, 38, 17],
  //         backgroundColor: [
  //           '#FF5100',
  //           '#FFD8E1',
  //           '#FF5100',
  //           '#FFD8E1',
  //           '#FF5100',
  //           '#FFD8E1',
  //           '#FF5100',
  //           '#FFD8E1',
  //           '#FF5100',
  //           '#FFD8E1',
  //           '#FF5100',
  //           '#FFD8E1',
  //         ],
  //         borderColor: [
  //           '#FF5100',
  //           '#FFD8E1',
  //           '#FF5100',
  //           '#FFD8E1',
  //           '#FF5100',
  //           '#FFD8E1',
  //           '#FF5100',
  //           '#FFD8E1',
  //           '#FF5100',
  //           '#FFD8E1',
  //           '#FF5100',
  //           '#FFD8E1',
  //         ],
  //         borderWidth: 1
  //       }]
  //     },
  //     options: {
  //       // title: {
  //       //   display: true,
  //       //   text: 'Número de valores'
  //       // },
  //       scales: {
  //         y: {
  //           beginAtZero: true
  //         }
  //       }
  //     }
  //   });
  // }

  reload(){
    let currentUrl = this._router.url;
    this._router.routeReuseStrategy.shouldReuseRoute = () => false;
    this._router.onSameUrlNavigation = 'reload';
    this._router.navigate([currentUrl]);
  }

  changeView(op) {
    
    this.selectedCardView = op;
    switch (op) {
      case 1:
        this.model_pesquisa_beneficiario = "";
        this.pesquisa_beneficiario_form.reset();
        this.model_beneficiario_view.beneficiario_id = null,
          this.model_beneficiario_view.beneficiario_nome = "",
          this.model_beneficiario_view.beneficiario_telefone = "",
          this.model_beneficiario_view.empresa_id = "",
          this.model_beneficiario_view.empresa_nome = "",
          this.model_beneficiario_view.foto_perfil = null,
          this.model_beneficiario_view.beneficio_proprio_beneficiario = null;
        this.model_beneficiario_view.dependente_beneficiario_id = null
        break;
      case 2:
        this.switch_to_ordemReserva = true;
        this.switch_to_iniciarVenda = false;

        this.model_pesquisa_beneficiario = "";
        this.pesquisa_beneficiario_form.reset();
        this.model_beneficiario_view.beneficiario_id = null,
          this.model_beneficiario_view.beneficiario_nome = "",
          this.model_beneficiario_view.beneficiario_telefone = "",
          this.model_beneficiario_view.empresa_id = "",
          this.model_beneficiario_view.empresa_nome = "",
          this.model_beneficiario_view.foto_perfil = null,
          this.model_beneficiario_view.beneficio_proprio_beneficiario = null;
        this.model_beneficiario_view.dependente_beneficiario_id = null

        this.getAllOrdemReserva();
        break;
      case 3:
        this.model_pesquisa_beneficiario = "";
        this.pesquisa_beneficiario_form.reset();
        this.model_beneficiario_view.beneficiario_id = null;
        this.model_beneficiario_view.beneficiario_nome = "";
        this.model_beneficiario_view.beneficiario_telefone = "";
        this.model_beneficiario_view.empresa_id = "";
        this.model_beneficiario_view.empresa_nome = "";
        this.model_beneficiario_view.foto_perfil = null;
        this.model_beneficiario_view.beneficio_proprio_beneficiario = null;
        this.model_beneficiario_view.dependente_beneficiario_id = null;
        break;

      case 4:
        this.getAllTransacoes();
        break;

      case 5:
        this.getFarmacia();
        break;

      case 6:

        break;

      case 7:

        break;

      case 8:
        this.spinner_all = true;
        this._sugestoes.getAllSugestoesFarmacias().subscribe(data => {
          // console.log(data);
          let res_data = Object(data)["data"]
          this.sugestoesArray = res_data.sugestoes
          this.spinner_all = false;
        }, error => {
          // console.log(error)
          this.spinner_all = false;
        })
        break;
      case 9:
        document.getElementById('verSugestoesID_sugestao').click();
        break;
      case 10:
        this.getAllPedidos();
        break; 
      case 11:
        this.getAllPedidos();  
        break;
      default:
        break;  

    }
  }

  backtoFarmaciaOverView() {
    this.reload();
    this.switch_to_ordemReserva = false;
    this.switch_to_iniciarVenda = false;
  }

  iniciarVendaViewSwitch() {
    this.switch_to_ordemReserva = false;
    this.switch_to_iniciarVenda = !this.switch_to_iniciarVenda;
  }

  listaMedicamentos() {
    // this.spinner_all = true;
    // this._stock.getStock().subscribe(data => {
    //   // console.log(data);
    //   let res_data = Object(data)["data"]
    //   this.marcas_medicamentos_farmacia = res_data.marcas_medicamentos;
    //   this.spinner_all = false;
    // }, error => {
    //   // console.log(error)
    //   this.spinner_all = false;
    // })
  }



  searchBeneficiario() {
    this.submitted = true;
    // console.log(this.pesquisa_beneficiario_form.value);
    if (this.pesquisa_beneficiario_form.invalid) {
      return
    }

    this.spinner_all = true;
    this._venda.verificacaoBeneficiarioFarmacia(this.pesquisa_beneficiario_form.value).subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.model_beneficiario_view.beneficio_proprio_beneficiario = res_data.beneficio_proprio_beneficiario;

      if (this.model_beneficiario_view.beneficio_proprio_beneficiario == false){
        // console.log("Dependente");
        this.model_dependente_view.dependente_beneficiario_id = res_data.dependente_beneficiario.dependente_beneficiario_id;
        this.model_dependente_view.beneficiario_nome = res_data.beneficiario.beneficiario_nome;
        this.model_dependente_view.dependente_beneficiario_nome = res_data.dependente_beneficiario.dependente_beneficiario_nome;
        this.model_dependente_view.dependente_beneficiario_telefone = res_data.dependente_beneficiario.dependente_beneficiario_telefone;
        this.model_beneficiario_view.empresa_id = res_data.beneficiario.empresa_id;
        this.model_beneficiario_view.empresa_nome = res_data.beneficiario.empresa_nome;
        this.model_beneficiario_view.foto_perfil = res_data.beneficiario.foto_perfil;
        // console.log(this.model_dependente_view.dependente_beneficiario_telefone);
      }
      else if (this.model_beneficiario_view.beneficio_proprio_beneficiario == true){
        // console.log("Beneficiario");
        this.model_beneficiario_view.beneficiario_id = res_data.beneficiario.beneficiario_id;
        this.model_beneficiario_view.beneficiario_nome = res_data.beneficiario.beneficiario_nome;
        this.model_beneficiario_view.beneficiario_telefone = res_data.beneficiario.beneficiario_telefone;
        this.model_beneficiario_view.empresa_id = res_data.beneficiario.empresa_id;
        this.model_beneficiario_view.empresa_nome = res_data.beneficiario.empresa_nome;
        this.model_beneficiario_view.foto_perfil = res_data.beneficiario.foto_perfil;

      }

      this.spinner_all = false;
    }, error => {
      // console.log(error)
      this.spinner_all = false;
    })
  }

  searchBen() {
    this.submitted = true;
    // console.log("Pesquisar beneficiario ordem de reserva",this.pesquisa_beneficiario_form.value);
    if (this.pesquisa_beneficiario_form.invalid) {
      return
    }

    this.spinner_all = true;
    this._venda.verificacaoBeneficiarioFarmacia(this.pesquisa_beneficiario_form.value).subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.model_beneficiario_view.beneficiario_id = res_data.beneficiario.beneficiario_id
      this.model_beneficiario_view.beneficiario_nome = res_data.beneficiario.beneficiario_nome
      this.model_beneficiario_view.beneficiario_telefone = res_data.beneficiario.beneficiario_telefone
      this.model_beneficiario_view.empresa_id = res_data.beneficiario.empresa_id
      this.model_beneficiario_view.empresa_nome = res_data.beneficiario.empresa_nome,
        this.model_beneficiario_view.foto_perfil = res_data.beneficiario.foto_perfil,
        this.model_beneficiario_view.beneficio_proprio_beneficiario = res_data.beneficio_proprio_beneficiario;
      this.model_beneficiario_view.dependente_beneficiario_id = res_data.dependente_beneficiario_id
      
      if(this.model_baixa.nome_beneficiario == this.model_beneficiario_view.beneficiario_nome){
        // console.log("Dono", this.model_baixa.nome_beneficiario)
        Swal.fire({
          title: 'Beneficiário  verificado',
          text: "Pode finalizar a venda",
          type: 'success',
          showCancelButton: false,
          confirmButtonColor: "#f15726",
          confirmButtonText: 'Ok'
        })
      }
      else if (this.model_baixa.nome_beneficiario != this.model_beneficiario_view.beneficiario_nome){
        // console.log("nao Dono",this.model_baixa.nome_beneficiario)
        Swal.fire({
          title: 'Beneficiário não verificado',
          text: "Não pode finalizar a venda para esse Beneficiário.",
          type: 'warning',
          showCancelButton: false,
          confirmButtonColor: "#f15726",
          confirmButtonText: 'Ok'
        })
        
      }
      this.spinner_all = false;
    }, error => {
      // console.log(error)
      this.spinner_all = false;
    })
  }

  proximo_venda() {
  
    document.getElementById('closePesquisaModalID').click()
    this.iniciarVendaViewSwitch();
    
    if (this.selectedCardView == 1) {
      this.model_venda.itens = [];
      this.model_venda.beneficiario_id = this.model_beneficiario_view.beneficiario_id;
      this.model_venda.empresa_id = this.model_beneficiario_view.empresa_id;
      this.model_venda.beneficio_proprio_beneficiario = this.model_beneficiario_view.beneficio_proprio_beneficiario;
      this.model_venda.dependente_beneficiario_id = this.model_beneficiario_view.dependente_beneficiario_id;
      this.model_venda.itens_baixa = [];
      this.model_venda.valor = null;
      this.model_venda.nr_comprovativo = '';
      this.model_venda.accao_codigo = null;
      this.getStockFarmacia();
    }
    else if (this.selectedCardView == 2) {
      this.model_venda.itens = [];

      this._stock.getStock_iniciar_venda(this.model_beneficiario_view.beneficiario_id).subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.marcas_medicamentos_farmacia = res_data.marcas_medicamentos;
        console.log("BAIXAS",this.model_venda.itens_baixa)


        this.model_venda.itens_baixa.forEach((item) => {
          this.ordem = item.marca_medicamento_id
          this.ordens_reserva = item
          // console.log(item)
          this.marcas_medicamentos_farmacia.forEach((element) => {
            this.baixa = element.marca_medicamento_id
            this.coberto = element.coberto
            this.pre_autorizacao = element.pre_autorizacao
  
            if(this.baixa == this.ordem){
              if(this.coberto == false && this.pre_autorizacao == false){
                // this.ordens_reserva.push()
                this.ordens_reserva["show_message"] = "Não esta coberto"
                this.model_venda.itens.push(
                  this.ordens_reserva
                )
                console.log(this.ordens_reserva)
                console.log("novo",this.model_venda.itens)
                console.log(element)

              }
              else if(this.coberto == true && this.pre_autorizacao == true) {
                this.ordens_reserva["show_message"] = "Precisa de autorização"
                this.model_venda.itens.push(
                  this.ordens_reserva
                )
                console.log(this.ordens_reserva)
                console.log("novo",this.model_venda.itens)
                console.log(element)
              }

            }

          })
        })
  
      })

      // console.log(this.marcas_medicamentos_farmacia)
      this.model_venda.beneficiario_id = this.model_beneficiario_view.beneficiario_id;
      this.model_venda.empresa_id = this.model_beneficiario_view.empresa_id;
      this.model_venda.beneficio_proprio_beneficiario = this.model_beneficiario_view.beneficio_proprio_beneficiario;
      this.model_venda.dependente_beneficiario_id = this.model_beneficiario_view.dependente_beneficiario_id;
      this.model_venda.nr_comprovativo = '';
      this.model_baixa.nr_comprovativo = ''
      this.model_venda.accao_codigo = null;
      this.getStockFarmacia();
      this.listaMedicamentos()

      
      // this.iniciarVendaViewSwitch();
      // console.log("ordem", this.model_venda.itens_baixa);




    }

    else if (this.selectedCardView == 3) {

      this.model_venda.itens = []

      this.model_venda.beneficiario_id = this.model_beneficiario_view.beneficiario_id;
      this.model_venda.empresa_id = this.model_beneficiario_view.empresa_id;
      this.model_venda.beneficio_proprio_beneficiario = this.model_beneficiario_view.beneficio_proprio_beneficiario;
      this.model_venda.dependente_beneficiario_id = this.model_beneficiario_view.dependente_beneficiario_id;
      this.model_venda.itens_baixa = [];
      this.model_venda.valor = null;
      this.model_venda.nr_comprovativo = '';
      this.model_venda.accao_codigo = null;
      this.getStockFarmacia_pedido_autorizacao();
    }
    // else if (this.selectedCardView == 4) {
    //   this.model_venda.beneficiario_id = this.model_beneficiario_view.beneficiario_id;
    //   this.model_venda.empresa_id = this.model_beneficiario_view.empresa_id;
    //   this.model_venda.beneficio_proprio_beneficiario = this.model_beneficiario_view.beneficio_proprio_beneficiario;
    //   this.model_venda.dependente_beneficiario_id = this.model_beneficiario_view.dependente_beneficiario_id;
    //   this.model_venda.itens_baixa = [];
    //   this.model_venda.valor = null;
    //   this.model_venda.nr_comprovativo = '';
    //   this.model_venda.accao_codigo = null;
    //   this.getStockFarmacia();
    // }
  }

  getStockFarmacia() {
    this.spinner_all = true;
    this._stock.getStock_iniciar_venda(this.model_beneficiario_view.beneficiario_id).subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.marcas_medicamentos_farmacia = res_data.marcas_medicamentos;
      this.spinner_all = false;
    }, error => {
      // console.log(error)
      this.spinner_all = false;
    })
  }

  getStockFarmacia_pedido_autorizacao() {
    this.spinner_all = true;
    this._stock.getStock_pedido_autorizacao(this.model_beneficiario_view.beneficiario_id).subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.marcas_medicamentos_farmacia = res_data.marcas_medicamentos;
      this.spinner_all = false;
    }, error => {
      // console.log(error)
      this.spinner_all = false;
    })
  }

  addItem(item) {
    // console.log("QUANTIDADE DISPONIVEL ITEM", this.model_medicamento_view.quantidade_disponivel)
    this.model_medicamento_view = item;
    this.quantidade_venda_item = null;
    this.preco_sem_iva_view = this.model_medicamento_view.preco;
    this.preco_com_iva_view = this.model_medicamento_view.preco_iva;

    if (this.selectedCardView == 2) {
      this.quantidade_venda_item = item.quantidade;
    }
    else if(this.selectedCardView == 1){
      // console.log("funcionando", this.model_medicamento_view.quantidade_disponivel)
      // console.log("quantidade", this.quantidade_venda_item)
      
    }

  }

  calculoIVA() {
    this.preco_sem_iva_view = this.model_medicamento_view.preco;
    this.preco_com_iva_view = this.model_medicamento_view.preco_iva;

    if (this.quantidade_venda_item) {
      this.preco_sem_iva_view = this.preco_sem_iva_view * this.quantidade_venda_item;
      let perc_valor = this.preco_sem_iva_view * (this.model_medicamento_view.iva / 100);
      this.preco_com_iva_view = this.preco_sem_iva_view + perc_valor;
      this.preco_sem_iva_view = this.preco_sem_iva_view.toFixed(2);
      this.preco_com_iva_view = this.preco_com_iva_view.toFixed(2);
    }
  }

  addToArray(item_to_add, quantidade) {
    // console.log(item_to_add);
    // console.log("quantidade", this.quantidade_venda_item == this.model_medicamento_view.quantidade_disponivel)
    if (quantidade) {
      if(this.quantidade_venda_item > this.model_medicamento_view.quantidade_disponivel){
        Swal.fire({
          title: '',
          text: "Quantidade acima do Stock disponivel",
          type: 'warning',
          showCancelButton: false,
          confirmButtonColor: "#f15726",
          confirmButtonText: 'Ok'
        })
      }
      else {
        this.model_venda.itens.push({
          marca_medicamento_id: item_to_add.marca_medicamento_id,
          preco: this.preco_sem_iva_view,
          iva: this.model_medicamento_view.iva,
          preco_iva: this.preco_com_iva_view,
          quantidade: quantidade
        // this.model_venda.itens_baixa.push({
        //   marca_medicamento_id: item_to_add.marca_medicamento_id,
        //   preco: this.preco_sem_iva_view,
        //   iva: this.model_medicamento_view.iva,
        //   preco_iva: this.preco_com_iva_view,
        //   quantidade: quantidade
        })
      }
    }


    let valor_temporario: number = 0;
    this.model_venda.itens.forEach(element => {
      valor_temporario = valor_temporario + Number(element.preco_iva)
    });
    this.model_venda.valor = Number(valor_temporario.toFixed(2));
    // let valor_temporario: number = 0;
    // this.model_venda.itens_baixa.forEach(element => {
    //   valor_temporario = valor_temporario + Number(element.preco_iva)
    // });
    // this.model_venda.valor = Number(valor_temporario.toFixed(2));


  }

  removeItem(item) {
    this.model_venda.itens.forEach((element, index) => {
      if (item.marca_medicamento_id == element.marca_medicamento_id) {
        this.model_venda.itens.splice(index, 1);
      }
    });
  }
  // removeItem(item) {
  //   this.model_venda.itens_baixa.forEach((element, index) => {
  //     if (item.marca_medicamento_id == element.marca_medicamento_id) {
  //       this.model_venda.itens_baixa.splice(index, 1);
  //     }
  //   });
  // }


  verifyName(marca_medicamento_id) {
    let nome = "";
    this.marcas_medicamentos_farmacia.forEach(element => {
      if (element.marca_medicamento_id == marca_medicamento_id) {
        nome = element.marca;
      }
    });
    return nome;
  }

  verifySubActiva(marca_medicamento_id) {
    let nome = "";
    this.marcas_medicamentos_farmacia.forEach(element => {

      if (element.marca_medicamento_id == marca_medicamento_id) {
        nome = element.medicamento_nome_generico;
      }
    });
    return nome;
  }

  verifyIfSelected(marca_medicamento_id) {
   return this.model_venda.itens.some(element => element['marca_medicamento_id'] === marca_medicamento_id)
    //  return this.model_venda.itens_baixa.some(element => element['marca_medicamento_id'] === marca_medicamento_id)
  }

  submeterIniciarVenda(){
    
    console.log(this.model_baixa)
    if (!this.model_baixa.nr_comprovativo) {
      Swal.fire({
        title: '',
        text: "Deve inserir o número do comprovativo da fatura.",
        type: 'warning',
        showCancelButton: false,
        confirmButtonColor: "#f15726",
        confirmButtonText: 'Ok'
      })
      return;
    }

    this.spinner_all = true;

    const formData = new FormData();
    if (this.selectedCardView != 7) {
      formData.append('accao_codigo', String(22)); // accao_codigo == 20
      formData.append('id', this.model_baixa.id);
      formData.append('nr_comprovativo', this.model_baixa.nr_comprovativo)
    } 
    

    this._venda.submeterVendaFarmacia(formData).subscribe(data => {
      // console.log(data);
      this.spinner_all = false;
      Swal.fire({
        title: 'Submetido.',
        text: "Venda Submetida com sucesso.",
        type: 'success',
        showCancelButton: false,
        confirmButtonColor: "#f15726",
        confirmButtonText: 'Ok'
      })
      document.getElementById('submeterVendaID').click();
      document.getElementById('closePesquisaModalID').click()
      this.iniciarVendaViewSwitch();
      this.getAllAguardaGasto();
      this.reloadCurrentRoute();
      
    }, error => {
      // console.log(error)
      this.spinner_all = false;
    })
  }
  submeterVenda() {

    if(this.selectedCardView == 11){
      this.submeterIniciarVenda();
    }

    if (this.selectedCardView == 1 || this.selectedCardView == 2 || this.selectedCardView == 4 ) {

      if(this.selectedCardView == 2){
        for (const element of this.model_venda.itens) {
          this.ordem = element.marca_medicamento_id
          // console.log(this.ordem)
          for(const item of this.marcas_medicamentos_farmacia){
            this.baixa = item.marca_medicamento_id
            this.ordens_reserva = item
            this.coberto = item.coberto
            this.pre_autorizacao = item.pre_autorizacao
  
            if(this.baixa == this.ordem && this.coberto == false){
  
              console.log("Coberto", this.show_coberto)
              this.message = "não esta coberto"
              // document.getElementById('msg').innerHTML = 'não esta coberto.'
              Swal.fire({
                title: '',
                text: `${item.marca} não esta coberto.`,
                type: 'warning',
                showCancelButton: false,
                confirmButtonColor: "#f15726",
                confirmButtonText: 'Ok'
              })
              return;
            }
            else if(this.baixa == this.ordem && this.coberto == true && this.pre_autorizacao == true){
              // document.getElementById('msg').innerHTML = ' precisa de autorização'
              this.message = "precisa de autorização"
  
              console.log("precisa de autorizacao",this.show_coberto)
              
              Swal.fire({
                title: '',
                text: `${item.marca} precisa de autorização.`,
                type: 'warning',
                showCancelButton: false,
                confirmButtonColor: "#f15726",
                confirmButtonText: 'Ok'
              })
             return;
            }
  
          }
        }
      }

      if (this.model_baixa.nr_comprovativo.length < 1) {
        Swal.fire({
          title: '',
          text: "Deve inserir o número do comprovativo da fatura.",
          type: 'warning',
          showCancelButton: false,
          confirmButtonColor: "#f15726",
          confirmButtonText: 'Ok'
        })
        return;
      }

      this.spinner_all = true;

      if (this.model_venda.beneficio_proprio_beneficiario) {
        this.model_venda.beneficio_proprio_beneficiario = 1;
      } else {
        this.model_venda.beneficio_proprio_beneficiario = 0;
        this.model_venda.dependente_beneficiario_id = 1;
      }
      const formData = new FormData();

      if (this.selectedCardView == 1) {
        formData.append('accao_codigo',String(20));
        
      } 
      else if (this.selectedCardView == 2) {
        formData.append('accao_codigo',String(21));
        formData.append('id',String(this.ordem_reserva_id));
      }
      // else if (this.selectedCardView == 3) {
      //   formData.append('accao_codigo', String(22)); // accao_codigo == 20
      //   formData.append('id', this.model_baixa.id);
      // } 
      else if(this.selectedCardView == 4){
        formData.append('accao_codigo', String(27))
        formData.append('id',String(this.model_baixa.id))
      }

      formData.append('empresa_id', this.model_venda.empresa_id);
      formData.append('nr_comprovativo', this.model_baixa.nr_comprovativo);
      formData.append('beneficiario_id', this.model_venda.beneficiario_id);
      formData.append('beneficio_proprio_beneficiario', this.model_venda.beneficio_proprio_beneficiario);
      formData.append('dependente_beneficiario_id', this.model_venda.dependente_beneficiario_id);
      formData.append('valor', String(this.model_venda.valor));
      formData.append('itens_baixa[]', JSON.stringify(this.model_venda.itens));
      // formData.append('itens_baixa[]', JSON.stringify(this.model_venda.itens_baixa));

      if (this.selectedFiles.length) {
        for (let i = 0; i < this.selectedFiles.length; i++)
          formData.append('ficheiros[]', this.selectedFiles[i], this.selectedFiles[i].name);
      }

      this._venda.submeterVendaFarmacia(formData).subscribe(data => {
        console.log(data);
        this.selectedFiles = [];
        this.spinner_all = false;
        Swal.fire({
          title: 'Submetido.',
          text: "Venda Submetida com sucesso.",
          type: 'success',
          showCancelButton: false,
          confirmButtonColor: "#f15726",
          confirmButtonText: 'Ok'
        })
        document.getElementById('submeterVendaID').click();
        this.iniciarVendaViewSwitch();

        if (this.selectedCardView == 2) {
          this.getAllOrdemReserva();
          this.switch_to_ordemReserva = true;
          this.switch_to_iniciarVenda = false;

          this.model_pesquisa_beneficiario = "";
          this.pesquisa_beneficiario_form.reset();
          this.model_beneficiario_view.beneficiario_id = null,
            this.model_beneficiario_view.beneficiario_nome = "",
            this.model_beneficiario_view.beneficiario_telefone = "",
            this.model_beneficiario_view.empresa_id = "",
            this.model_beneficiario_view.empresa_nome = "",
            this.model_beneficiario_view.foto_perfil = null,
            this.model_beneficiario_view.beneficio_proprio_beneficiario = null;
          this.model_beneficiario_view.dependente_beneficiario_id = null

        }
        else if (this.selectedCardView == 4) {
          this.getAllDevolvidos();
          this.model_pesquisa_beneficiario = "";
          this.pesquisa_beneficiario_form.reset();
          this.model_beneficiario_view.beneficiario_id = null,
            this.model_beneficiario_view.beneficiario_nome = "",
            this.model_beneficiario_view.beneficiario_telefone = "",
            this.model_beneficiario_view.empresa_id = "",
            this.model_beneficiario_view.empresa_nome = "",
            this.model_beneficiario_view.foto_perfil = null,
            this.model_beneficiario_view.beneficio_proprio_beneficiario = null;
          this.model_beneficiario_view.dependente_beneficiario_id = null

        }

      }, error => {
        // console.log(error)
        this.spinner_all = false;
      })
    }
    else if (this.selectedCardView == 3) {
      this.spinner_all = true;
      this.model_venda.accao_codigo = 40;
      this.model_venda.itens_baixa = this.model_venda.itens
      // formData.append('id', this.model_baixa.id)
      this._venda.submeterPedidoAprovacaoFarmacia(this.model_venda).subscribe(data => {
        console.log(data);
        this.spinner_all = false;
        Swal.fire({
          title: 'Submetido.',
          text: "Pedido de Aprovação submetido com sucesso!",
          type: 'success',
          showCancelButton: false,
          confirmButtonColor: "#f15726",
          confirmButtonText: 'Ok'
        })
        document.getElementById('submeterVendaID').click();
        this.iniciarVendaViewSwitch();

      }, error => {
        // console.log(error)
        this.spinner_all = false;
      })
    }
  }

  limparCampos(){
    this.pesquisa_beneficiario_form.reset()
    this.model_beneficiario_view.beneficiario_nome = "";
    this.model_beneficiario_view.beneficiario_telefone = "";
    this.model_beneficiario_view.empresa_nome = "";
    this.model_beneficiario_view.foto_perfil = ""
  }
  ResubmeterVenda() {
   

    if ( this.selectedCardView == 1 || this.selectedCardView == 2 || this.selectedCardView == 4) {
      if (this.model_venda.nr_comprovativo.length < 1) {
        // console.log( "Numero: " ,this.model_baixa.nr_comprovativo);
        // console.log("Bene", this.model_venda.beneficio_proprio_beneficiario)
        Swal.fire({
          title: '',
          text: "Deve inserir o número do comprovativo da fatura.",
          type: 'warning',
          showCancelButton: false,
          confirmButtonColor: "#f15726",
          confirmButtonText: 'Ok'
        })
        return;
      }
      this.spinner_all = true;

      if (this.model_venda.beneficio_proprio_beneficiario) {
        this.model_venda.beneficio_proprio_beneficiario = 1;
      } else {
        this.model_venda.beneficio_proprio_beneficiario = 0;
      }
      const formData = new FormData();

      if (this.selectedCardView == 1) {
        formData.append('accao_codigo',String(20));
        
      } else if (this.selectedCardView == 2) {
        formData.append('accao_codigo',String(21));
        formData.append('id',String(this.ordem_reserva_id));
      }
      else if(this.selectedCardView == 4){
        formData.append('accao_codigo', String(27))
      }

      formData.append('empresa_id', this.model_venda.empresa_id);
      formData.append('nr_comprovativo', this.model_venda.nr_comprovativo);
      formData.append('beneficiario_id', this.model_venda.beneficiario_id);
      formData.append('beneficio_proprio_beneficiario', this.model_venda.beneficio_proprio_beneficiario);
      formData.append('dependente_beneficiario_id', this.model_venda.dependente_beneficiario_id);
      formData.append('valor', String(this.model_venda.valor));
      formData.append('itens_baixa[]', JSON.stringify(this.model_venda.itens_baixa));

      if (this.selectedFiles.length) {
        for (let i = 0; i < this.selectedFiles.length; i++)
          formData.append('ficheiros[]', this.selectedFiles[i], this.selectedFiles[i].name);
      }

      this._venda.submeterVendaFarmacia(formData).subscribe(data => {
        // console.log(data);
        this.selectedFiles = [];
        this.spinner_all = false;
        Swal.fire({
          title: 'Submetido.',
          text: "Venda Submetida com sucesso.",
          type: 'success',
          showCancelButton: false,
          confirmButtonColor: "#f15726",
          confirmButtonText: 'Ok'
        })
        document.getElementById('submeterVendaID').click();
        this.iniciarVendaViewSwitch();

        if (this.selectedCardView == 2) {
          this.getAllOrdemReserva();
          this.switch_to_ordemReserva = true;
          this.switch_to_iniciarVenda = false;

          this.model_pesquisa_beneficiario = "";
          this.pesquisa_beneficiario_form.reset();
          this.model_beneficiario_view.beneficiario_id = null,
            this.model_beneficiario_view.beneficiario_nome = "",
            this.model_beneficiario_view.beneficiario_telefone = "",
            this.model_beneficiario_view.empresa_id = "",
            this.model_beneficiario_view.empresa_nome = "",
            this.model_beneficiario_view.foto_perfil = null,
            this.model_beneficiario_view.beneficio_proprio_beneficiario = null;
          this.model_beneficiario_view.dependente_beneficiario_id = null

        }

      }, error => {
        // console.log(error)
        this.spinner_all = false;
      })
    }
    else if (this.selectedCardView == 3) {
      this.spinner_all = true;
      this.model_venda.accao_codigo = 40;
      this._venda.submeterPedidoAprovacaoFarmacia(this.model_venda).subscribe(data => {
        // console.log(data);
        this.spinner_all = false;
        Swal.fire({
          title: 'Submetido.',
          text: "Pedido de Aprovação submetido com sucesso!",
          type: 'success',
          showCancelButton: false,
          confirmButtonColor: "#f15726",
          confirmButtonText: 'Ok'
        })
        document.getElementById('submeterVendaID').click();
        this.iniciarVendaViewSwitch();

      }, error => {
        // console.log(error)
        this.spinner_all = false;
      })
    }
  }

  // ================= FARMÁCIA =================

  updateFarmaciaSubmit() {

    let ano = new Date().getFullYear()
    if( this.modelData.mes > this.currentMonth && this.modelData.ano == ano ){
      this.selectdiaMesAnoBoolean = true;
      return;
    }

    this.submitted = true;
    if (!this.modelData.dia) {
      this.selectdiaMesAnoBoolean = true;
      return;
    }
    else if (!this.modelData.mes) {
      this.selectdiaMesAnoBoolean = true;
      return;
    }
    else if (!this.modelData.ano) {
      this.selectdiaMesAnoBoolean = true;
      return;
    }
    let stringDate = `${this.modelData.ano}-${this.modelData.mes}-${this.modelData.dia}`;
    this.form.value.data_alvara_emissao = stringDate;
    // console.log(this.form.value);
    if (this.form.invalid) {
      return
    }
    let texts = this.form.value.contactos.map(function (el) {
      return el.contacto;
    });
    this.form.value.contactos = texts;
    this.spinner = true;
    this._farmacia.updateFarmacia(this.form.value).subscribe(data => {
      this.spinner = false;
      document.getElementById('actualizarID_farmacia').click();
      this.modelData.ano = null;
      this.modelData.dia = null;
      this.modelData.mes = null;
      this.form.reset();
      this.editBtnFarmacia = false;

      Swal.fire({
        title: 'Submetido.',
        text: "Actualizado com sucesso.",
        type: 'success',
        showCancelButton: false,
        confirmButtonColor: "#f15726",
        confirmButtonText: 'Ok'
      })
    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
  }

  toUpdateFarmacia() {
    this.editBtnFarmacia = true;
    this.form.enable();
  }

  dateBinding() {
    this.selectdiaMesAnoBoolean = false;
  }

  addContacto() {
    this.arrayContactos.push(this.formBuilder.group({ contacto: '' }));
  }

  deleteContacto(index) {
    this.arrayContactos.removeAt(index);
  }

  get arrayHorarios() {
    return this.form.get('horario_funcionamento') as FormArray;
  }

  get arrayContactos() {
    return <FormArray>this.form.get('contactos');
  }

  getFarmacia() {
    this.spinner = true;
    this._farmacia.getFarmacia().subscribe(data => {
      let row = Object(data)["data"];
      this.spinner = false;

      let arrayValues: any[] = [];
      for (let index = 0; index < row.contactos.length; index++) {
        arrayValues.push({ contacto: row.contactos[index] });
      }

      row.contactos = arrayValues;
      this.form.patchValue({
        nome: row.nome,
        endereco: row.endereco,
        horario_funcionamento: row.horario_funcionamento,
        activa: row.activa,
        contactos: arrayValues,
        latitude: row.latitude,
        longitude: row.longitude,
        numero_alvara: row.numero_alvara,
        data_alvara_emissao: row.data_alvara_emissao,
        observacoes: row.observacoes,
        created_at: row.created_at,
        updated_at: row.updated_at,
        deleted_at: row.deleted_at,
        id: row.id
      });

      let check = moment(row.data_alvara_emissao, 'YYYY/MM/DD');
      this.modelData.dia = +check.format('D');
      this.modelData.mes = +check.format('M');
      this.modelData.ano = +check.format('YYYY');

      this.form.disable();

    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  initializeFormFarmacia() {
    this.form = this.formBuilder.group({
      nome: ['', Validators.required],
      endereco: ['', [Validators.required]],
      horario_funcionamento: this.formBuilder.array([
        this.formBuilder.group({ dia: ['Domingo'], estado: [false], abertura: [''], enceramento: [''] }),
        this.formBuilder.group({ dia: ['Segunda-feira'], estado: [false], abertura: [''], enceramento: [''] }),
        this.formBuilder.group({ dia: ['Terça-feira'], estado: [false], abertura: [''], enceramento: [''] }),
        this.formBuilder.group({ dia: ['Quarta-feira'], estado: [false], abertura: [''], enceramento: [''] }),
        this.formBuilder.group({ dia: ['Quinta-feira'], estado: [false], abertura: [''], enceramento: [''] }),
        this.formBuilder.group({ dia: ['Sexta-feira'], estado: [false], abertura: [''], enceramento: [''] }),
        this.formBuilder.group({ dia: ['Sábado'], estado: [false], abertura: [''], enceramento: [''] }),
        this.formBuilder.group({ dia: ['Feriados'], estado: [false], abertura: [''], enceramento: [''] })
      ], [Validators.required]),
      activa: ['', Validators.required],
      contactos: this.formBuilder.array([this.formBuilder.group({ contacto: '' })], Validators.required),
      latitude: [''],
      longitude: [''],
      numero_alvara: ['', Validators.required],
      data_alvara_emissao: [''],
      observacoes: [''],
      created_at: [''],
      updated_at: [''],
      deleted_at: [''],
      id: [''],
    });
  }

  //==================== ORDEM DE RESERVA ======================
  getAllOrdemReserva() {
    this.spinner_all = true;
    this._stock.getAllOrdemReservaFarmacia().subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.ordemReserva_array = res_data.ordens_reserva;
      this.spinner_all = false;
    }, error => {
      // console.log(error)
      this.spinner_all = false;
    })
  }

  iniciarVendaOrdemDeReserva(item) {
    this.pesquisa_beneficiario_form.reset()
    this.model_beneficiario_view.beneficiario_nome = ''
    this.model_beneficiario_view.beneficiario_telefone = ''
    this.model_beneficiario_view.empresa_nome = ''
    // console.log(item)
    
    
    this.ordem_reserva_id = item.id;
    this.model_venda.beneficiario_id = item.beneficiario_id;
    this.model_venda.empresa_id = item.empresa_id;
    this.model_venda.itens_baixa = [];

    item.iten_ordem_reserva.forEach(element => {
      this.model_venda.itens_baixa.push({
        marca_medicamento_id: element.marca_medicamento_id,
        preco: element.preco,
        iva: element.iva,
        preco_iva: element.preco_iva,
        quantidade: element.quantidade
      })
    });
    let valor_temporario: number = 0;
    this.model_venda.itens_baixa.forEach(element => {
      valor_temporario = valor_temporario + Number(element.preco_iva)
    });
    this.model_venda.valor = Number(valor_temporario.toFixed(2));

    this.model_beneficiario_view.beneficiario_id = item.beneficiario_id;
    // this.getStockFarmacia();
    // this.iniciarVendaViewSwitch();
    this.model_baixa.data_baixa = item.data_baixa;
    this.model_baixa.iten_ordem_reserva = item.iten_ordem_reserva;
    this.model_baixa.estado_id = item.estado_id;
    this.model_baixa.estado_codigo = item.estado_codigo;
    this.model_baixa.estado_nome = item.estado_nome;
    this.model_baixa.id = item.id;
    this.model_baixa.nome_beneficiario = item.nome_beneficiario;
    this.model_baixa.nome_instituicao = item.nome_instituicao;
    this.model_baixa.proveniencia = item.proveniencia;
    this.model_baixa.valor_baixa = item.valor_baixa;
    this.model_baixa.comprovativo = item.comprovativo;
    // console.log("nome", item.nome_beneficiario);
  }



  viewItemOrdemRserva(item) {
     console.log(item)
    this.model_baixa.data_baixa = item.data_baixa;
    this.model_baixa.iten_ordem_reserva = item.iten_ordem_reserva;
    this.model_baixa.estado_id = item.estado_id;
    this.model_baixa.estado_codigo = item.estado_codigo;
    this.model_baixa.estado_nome = item.estado_nome;
    this.model_baixa.id = item.id;
    this.model_baixa.nome_beneficiario = item.nome_beneficiario;
    this.model_baixa.nome_instituicao = item.nome_instituicao;
    this.model_baixa.proveniencia = item.proveniencia;
    this.model_baixa.valor_baixa = item.valor_baixa;
    this.model_baixa.comprovativo = item.comprovativo;
  }

  //============================ SUGESTÕES =====================
  initializeFormSUgestao() {
    this.form_sugestao = this.formBuilder.group({
      tipo: ['', Validators.required],
      descricao: ['', Validators.required],
    });
  }

  sugestaoSubmit() {
    this.submitted = true;
    // console.log(this.form_sugestao.value);
    if (this.form_sugestao.invalid) {
      return
    }

    let model = {
      conteudo: this.form_sugestao.value.tipo + ' ' + this.form_sugestao.value.descricao
    }

    this.spinner = true;
    this._sugestoes.registerSugestaoFarmacia(model).subscribe(data => {
      // console.log(data);
      Swal.fire({
        title: 'Submetido.',
        text: "Sugestão Submetida com sucesso.",
        type: 'success',
        showCancelButton: false,
        confirmButtonColor: "#f15726",
        confirmButtonText: 'Ok'
      })
      document.getElementById('actualizarID_sugestao').click();
      this.form_sugestao.reset();
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  // ======================== TRANSAÇÕES =========================

  changeViewTransacao(opcao) {
    switch (opcao) {
      case 1:
        this.selectedCardViewTransacao = 1;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '10') {
            this.show_By_Estado.push(element)
          }
        });
        break;
      case 2:
        this.selectedCardViewTransacao = 2;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '11') {
            this.show_By_Estado.push(element)
          }
        });
        break;
      case 3:
        this.selectedCardViewTransacao = 3;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '12') {
            this.show_By_Estado.push(element)
          }
        });
        break;
      case 4:
        this.selectedCardViewTransacao = 4;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '13') {
            this.show_By_Estado.push(element)
          }
        });
        break;
      default:
        break;

    }
  }
  // ======================== PEDIDOS DE AUTORIZAÇÕES =========================

  changeViewPedidos(opcao) {
    switch (opcao) {
      case 1:
        this.selectedCardViewPedidos = 1;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '8') {
            this.show_By_Estado.push(element)
          }
        });
        break;
      case 2:
        this.selectedCardViewPedidos = 2;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '7') {
            this.show_By_Estado.push(element)
          }
        });
        break;
      case 3:
        this.selectedCardViewPedidos = 3;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '9') {
            this.show_By_Estado.push(element)
          }
        });
        break;
      default:
        break;

    }
  }

  reloadCurrentRoute() {
    let currentUrl = this._router.url;
    this._router.routeReuseStrategy.shouldReuseRoute = () => false;
    this._router.onSameUrlNavigation = 'reload';
    this._router.navigate([currentUrl]);
  }

  getAllTransacoes() {
    this.spinner = true;
    if (this.userRole == 6) { // UNIDADE SANITÁRIA
      // console.log(this.userRole);
      this._gastos.getAllTransacoesUs().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas = res_data.baixas;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '10') {
            this.show_By_Estado.push(element)
          }
        });
        this.spinner = false;
      }, error => {
        // console.log(error)
        this.spinner = false;
      })
    }

    else if (this.userRole == 2 || this.userRole == 3) { // FARMACIA
      // console.log(this.userRole);
      this._gastos.getAllTransacoesFarmacia().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas = res_data.baixas;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if(this.selectedCardViewTransacao = 1){
            if (element.estado_codigo == '10') {       
              this.show_By_Estado.push(element)
            }
          }
        });
        this.spinner = false;

      }, error => {
        // console.log(error)
        this.spinner = false;
      })
    }
  }

  getAllTransacoesExcel() {
    this.spinner = true;
    if (this.userRole == 6) { // UNIDADE SANITÁRIA
      // console.log(this.userRole);
      this._gastos.getAllTransacoesUs().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas = res_data.baixas;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '10') {
            this.show_By_Estado.push(element)
          }
        });
        this.spinner = false;
      }, error => {
        // console.log(error)
        this.spinner = false;
      })
    }

    else if (this.userRole == 2 || this.userRole == 3) { // FARMACIA
      // console.log(this.userRole);
      this._gastos.getAllTransacoesFarmaciaExcel().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas_excel = res_data.baixas;
        this.excelService.exportAsExcelFile(this.clientes_baixas_excel, 'Transacoes_Farmacia');
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if(this.selectedCardViewTransacao = 1){
            if (element.estado_codigo == '10') {       
              this.show_By_Estado.push(element)
            }
          }
        });
        this.spinner = false;

      }, error => {
        // console.log(error)
        this.spinner = false;
      })
    }
  }

  getAllAguardaGasto() {
    this.spinner = true;
    if (this.userRole == 6) { // UNIDADE SANITÁRIA
      // console.log(this.userRole);
      this._gastos.getAllTransacoesUs().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas = res_data.baixas;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '9') {
            this.show_By_Estado.push(element)
          }
        });
        this.spinner = false;
      }, error => {
        // console.log(error)
        this.spinner = false;
      })
    }

    else if (this.userRole == 2 || this.userRole == 3) { // FARMACIA
      // console.log(this.userRole);
      this._gastos.getAllTransacoesFarmacia().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas = res_data.baixas;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '9') {
            this.show_By_Estado.push(element)
          }
        });
        this.spinner = false;
      }, error => {
        // console.log(error)
        this.spinner = false;
      })
    }
  }

  getAllDevolvidos() {
    this.spinner = true;
    if (this.userRole == 6) { // UNIDADE SANITÁRIA
      // console.log(this.userRole);
      this._gastos.getAllTransacoesUs().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas = res_data.baixas;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '13') {
            this.show_By_Estado.push(element)
          }
        });
        this.spinner = false;
      }, error => {
        // console.log(error)
        this.spinner = false;
      })
    }

    else if (this.userRole == 2 || this.userRole == 3) { // FARMACIA
      // console.log(this.userRole);
      this._gastos.getAllTransacoesFarmacia().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas = res_data.baixas;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '13') {
            this.show_By_Estado.push(element)
          }
        });
        this.spinner = false;
      }, error => {
        // console.log(error)
        this.spinner = false;
      })
    }
  }
  getAllPedidos() {
    this.spinner = true;
    if (this.userRole == 6) { // UNIDADE SANITÁRIA
      // console.log(this.userRole);
      this._gastos.getAllPedidosUni().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas = res_data.pedidos_aprovacao;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '8') {
            this.show_By_Estado.push(element)
          }
        });
        this.spinner = false;
      }, error => {
        // console.log(error)
        this.spinner = false;
      })
    }

    else if (this.userRole == 2 || this.userRole == 3) { // FARMACIA
      
      // console.log(this.userRole);
      this._gastos.getAllPedidosFarm().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas = res_data.pedidos_aprovacao;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if(this.selectedCardViewPedidos = 1){
            if (element.estado_codigo == '8') {
              this.show_By_Estado.push(element)
            }
          }
        });
        this.spinner = false;

      }, error => {
        // console.log(error)
        this.spinner = false;
      })
    }
  }
  getAllPedidosExcel() {
    this.spinner = true;
    if (this.userRole == 6) { // UNIDADE SANITÁRIA
      // console.log(this.userRole);
      this._gastos.getAllPedidosUni().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas_pedidos_excel = res_data.pedidos_aprovacao;
        this.excelService.exportAsExcelFile(this.clientes_baixas_pedidos_excel, 'Pedidos autorizacao');
        this.show_By_Estado = [];
        this.spinner = false;
      }, error => {
        // console.log(error)
        this.spinner = false;
      })
    }

    else if (this.userRole == 2 || this.userRole == 3) { // FARMACIA
      
      // console.log(this.userRole);
      this._gastos.getAllPedidosFarmExcel().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas_pedidos_excel = res_data.pedidos_aprovacao;
        this.excelService.exportAsExcelFile(this.clientes_baixas_pedidos_excel, 'Pedidos Autorizacao_Farmacia');
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if(this.selectedCardViewPedidos = 1){
            if (element.estado_codigo == '8') {
              this.show_By_Estado.push(element)
            }
          }
        });
        this.spinner = false;

      }, error => {
        // console.log(error)
        this.spinner = false;
      })
    }
  }

  viewCardBaixaModal(item) {
    this.model_baixa.data_baixa = item.data_baixa;
    this.model_baixa.updated_at = item.updated_at;
    this.model_baixa.descricao = item.descricao;
    this.model_baixa.beneficiario_id = item.beneficiario_id;
    this.model_baixa.empresa_id = item.empresa_id;
    this.model_baixa.estado_id = item.estado_id;
    this.model_baixa.estado_codigo = item.estado_codigo;
    this.model_baixa.estado_nome = item.estado_nome;
    this.model_baixa.id = item.id;
    this.model_baixa.nome_beneficiario = item.nome_beneficiario;
    this.model_baixa.nome_instituicao = item.nome_instituicao;
    this.model_baixa.proveniencia = item.proveniencia;
    this.model_baixa.valor_baixa = item.valor_baixa;
    this.model_baixa.comprovativo = item.comprovativo;
    this.model_baixa.comentario_baixa = item.comentario_baixa;
    this.model_baixa.comentario_pedido_aprovacao = item.comentario_pedido_aprovacao;
    this.model_baixa.nr_comprovativo = item.nr_comprovativo;
    this.model_baixa.responsavel = item.responsavel;
    // console.log("Dados do beneficiario", this.model_baixa);
  }

  getFileDownload(fileName: string) {
    this.spinner_download = true;
    this._gastos.baixarComprovativoFarmacia(this.model_baixa.proveniencia, this.model_baixa.id, fileName).subscribe(res => {
      this.spinner_download = false;
      var newBlob = new Blob([res], { type: "application/*" });
      if (window.navigator && window.navigator.msSaveOrOpenBlob) {
        window.navigator.msSaveOrOpenBlob(newBlob);
        return;
      }
      // For other browsers: 
      // Create a link pointing to the ObjectURL containing the blob.
      const data = window.URL.createObjectURL(newBlob);
      var link = document.createElement('a');
      link.href = data;
      link.download = `${fileName}`;
      // this is necessary as link.click() does not work on the latest firefox
      link.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, view: window }));
      setTimeout(function () {
        // For Firefox it is necessary to delay revoking the ObjectURL
        window.URL.revokeObjectURL(data);
      }, 100);
    }, error => {
      this.spinner_download = false;
      // console.log(error);
    })
  }

  resubmeterView() {
    // console.log(this.model_baixa);

    this.model_venda.beneficiario_id = this.model_baixa.beneficiario_id;
    this.model_venda.empresa_id = this.model_baixa.empresa_id;
    this.model_venda.itens_baixa = [];
    this.model_venda.itens = []

    this.model_baixa.descricao.forEach(element => {
      this.model_venda.itens.push({
        marca_medicamento_id: element.marca_medicamento_id,
        preco: element.preco,
        iva: element.iva,
        preco_iva: element.preco_iva,
        quantidade: element.quantidade
      })
      // this.model_venda.itens_baixa.push({
      //   marca_medicamento_id: element.marca_medicamento_id,
      //   preco: element.preco,
      //   iva: element.iva,
      //   preco_iva: element.preco_iva,
      //   quantidade: element.quantidade
      // })
    });
    let valor_temporario: number = 0;
    this.model_venda.itens.forEach(element => {
      valor_temporario = valor_temporario + Number(element.preco_iva)
    });
    // this.model_venda.itens_baixa.forEach(element => {
    //   valor_temporario = valor_temporario + Number(element.preco_iva)
    // });
    this.model_venda.valor = Number(valor_temporario.toFixed(2));

    this.model_beneficiario_view.beneficiario_id = this.model_baixa.beneficiario_id;
    this.getStockFarmacia();
    this.iniciarVendaViewSwitch();

    document.getElementById('actualizarID_resubmeter').click();
  }

  // =========== UPLOAD FILE =====================
  detectFiles(event) {
    if (event.target.files.length) {
      for (let i = 0; i < event.target.files.length; i++) {
        this.selectedFiles.push(<File>event.target.files[i]);
      }
    }
    this.cd.markForCheck();
    // console.log(this.selectedFiles);

  }

  deleteFile(item) {
    this.selectedFiles.splice(item, 1);
  }

}
function item(item: any) {
  throw new Error('Function not implemented.');
}

