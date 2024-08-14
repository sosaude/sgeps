import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { GastosService } from 'src/app/_services/gastos.service';
import { FormBuilder, Validators, FormGroup, FormArray } from '@angular/forms';
import { UsGestaoStockService } from 'src/app/_services/us-gestao-stock.service';
import Swal from 'sweetalert2';
import { AuthenticationService } from 'src/app/_services/authentication.service';
import { ClinicasService } from 'src/app/_services/clinicas.service';
import { SugestoesService } from 'src/app/_services/sugestoes.service';
import { ExcelService } from 'src/app/_services/excel.service';

@Component({
  selector: 'app-us-overview',
  templateUrl: './us-overview.component.html',
  styleUrls: ['./us-overview.component.scss']
})
export class UsOverviewComponent implements OnInit {

  selectedCardView: number = 0;
  model_pesquisa_beneficiario: string;
  pesquisa_beneficiario_form: FormGroup;
  spinner_all: boolean;
  servicos_unidade_sanitaria: any;
  submitted: boolean = false
  model_venda = {
    empresa_id: null,
    nr_comprovativo: '',
    beneficiario_id: null,
    valor: null,
    itens_baixa: [],
    beneficio_proprio_beneficiario: null,
    dependente_beneficiario_id: null,
    accao_codigo: null
  }

  searchText: string = "";
  spinner: boolean = false;

  model_beneficiario_view = {
    beneficiario_id: null,
    beneficiario_nome: "",
    beneficiario_telefone: "",
    empresa_id: null,
    foto_perfil: null,
    empresa_nome: "",
    beneficio_proprio_beneficiario: true,
    dependente_beneficiario_id: null
  }
  servicos_us: any;
  p: number = 1;
  quantidade_venda_item: number = 0;
  model_medicamento_view = {
    id: null,
    preco: null,
    iva: null,
    preco_iva: null,
    servico_id: null,
    servico_nome: ""
  }
  preco_sem_iva_view: any;
  preco_com_iva_view: any;
  authValue: any;
  userRole: number;
  form: FormGroup;
  submitBtn: boolean = false;
  form_sugestao: FormGroup;
  // beneficiario  ===>   BENE0001
  tipo_sugestoes: any[] = [
    { nome: 'Unidade Sanitária' }, { nome: 'Serviço' }, { nome: 'Outro' }
  ]
  perfil_roles_unidade_sanitaria: any;
  sugestoesArray: any;
  switch_to_iniciarVenda: boolean = false;
  selectedCardViewTransacao: number = 1;
  selectedCardViewPedidos: number = 1;
  show_By_Estado: any[] = [];
  pedidos_autorizacao: any[] = ['Teste', 'Teste 2'];
  clientes_baixas: any[];
  clientes_baixas_excel: any[];
  clientes_baixas_pedidos_excel: any[];
  spinner_download: boolean = false;

  selectdComprovativo: boolean = false;

  model_clientes = {
    id: null,
    proveniencia: null,
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
    iten_ordem_reserva: []
  }
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
    nr_comprovativo: '',
  }
  selectedFiles: File[] = [];
  ordem_reserva_id: null;
  private _router: any;

  constructor(private _venda: GastosService, private authenticationService: AuthenticationService,
    private cd: ChangeDetectorRef,
    private formBuilder: FormBuilder,
    private _stock: UsGestaoStockService,
    private excelService: ExcelService,
    private _clinica: ClinicasService, private _gastos: GastosService,
    private _sugestoes: SugestoesService) {
    if (this.authenticationService.currentUserValue) {
      this.authValue = this.authenticationService.currentUserValue;
      this.userRole = this.authenticationService.currentUserValue.user.role.codigo;
    }
  }

  ngOnInit() {
    this.pesquisa_beneficiario_form = this.formBuilder.group({
      codigo: ['', Validators.required],
    });
    this.initializeFormSUgestao();
    this.initializeFormUsSubmit();
    // this.getRolesUnidadeSanitaria();
  }
  getAllTransacoesExcel() {
    this.spinner = true;
    if (this.userRole == 6) { // UNIDADE SANITÁRIA
      // console.log(this.userRole);
      this._gastos.getAllTransacoesUsExcel().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas_excel = res_data.baixas;
        this.excelService.exportAsExcelFile(this.clientes_baixas_excel, 'Transacoes_US');
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

  getAllPedidosExcel() {
    this.spinner = true;
    if (this.userRole == 6) { // UNIDADE SANITÁRIA
      // console.log(this.userRole);
      this._gastos.getAllPedidosUniExcel().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas_pedidos_excel = res_data.pedidos_aprovacao;
        this.excelService.exportAsExcelFile(this.clientes_baixas_pedidos_excel, 'Pedidos autorizacao_US');
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

        break;
      case 3:
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

      case 4:
        this.getAllTransacoes();
        break;
      case 41:
        this.getAllPedidos();
        break;

      case 5:
        this.getUnidadeSanitaria();
        break;

      case 6:

        break;

      case 7:

        break;

      case 8:
        this.spinner_all = true;
        this._sugestoes.getAllSugestoesUs().subscribe(data => {
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

    }
  }

  iniciarVendaViewSwitch() {
    this.switch_to_iniciarVenda = !this.switch_to_iniciarVenda;
  }

  reloadCurrentRoute() {
    let currentUrl = this._router.url;
    this._router.routeReuseStrategy.shouldReuseRoute = () => false;
    this._router.onSameUrlNavigation = 'reload';
    this._router.navigate([currentUrl]);
  }

  listaMedicamentos() {

  }

  verifyName(servico_id) {
    let nome = "";
    this.servicos_us.forEach(element => {
      if (element.servico_id == servico_id) {
        nome = element.servico_nome;
      }
    });
    return nome;
  }

  verifyIfSelected(servico_id) {
    return this.model_venda.itens_baixa.some(element => element['servico_id'] === servico_id)
  }

  searchBeneficiario() {
    this.submitted = true;
    // console.log("Pesquisar beneficiario",this.pesquisa_beneficiario_form.value);
    if (this.pesquisa_beneficiario_form.invalid) {
      return
    }

    this.spinner_all = true;
    this._venda.verificacaoBeneficiarioUs(this.pesquisa_beneficiario_form.value).subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.model_beneficiario_view.beneficiario_id = res_data.beneficiario.beneficiario_id
      // console.log(res_data.beneficiario.beneficiario_id);
      this.model_beneficiario_view.beneficiario_nome = res_data.beneficiario.beneficiario_nome
      this.model_beneficiario_view.beneficiario_telefone = res_data.beneficiario.beneficiario_telefone
      this.model_beneficiario_view.empresa_id = res_data.beneficiario.empresa_id
      this.model_beneficiario_view.empresa_nome = res_data.beneficiario.empresa_nome,
        this.model_beneficiario_view.foto_perfil = res_data.beneficiario.foto_perfil,
        this.model_beneficiario_view.beneficio_proprio_beneficiario = res_data.beneficio_proprio_beneficiario;
      this.model_beneficiario_view.dependente_beneficiario_id = res_data.dependente_beneficiario_id;

      this.spinner_all = false;
    }, error => {
      // console.log(error)
      this.spinner_all = false;
    })
  }

  limparCampos(){
    this.pesquisa_beneficiario_form.reset()
    this.model_beneficiario_view.beneficiario_nome = "";
    this.model_beneficiario_view.beneficiario_telefone = "";
    this.model_beneficiario_view.empresa_nome = "";
    this.model_beneficiario_view.foto_perfil = ""
  }
  searchBen() {
    this.submitted = true;
    // console.log("Pesquisar beneficiario",this.pesquisa_beneficiario_form.value);
    if (this.pesquisa_beneficiario_form.invalid) {
      return
    }

    this.spinner_all = true;
    this._venda.verificacaoBeneficiarioUs(this.pesquisa_beneficiario_form.value).subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.model_beneficiario_view.beneficiario_id = res_data.beneficiario.beneficiario_id
      // console.log(res_data.beneficiario.beneficiario_id);
      this.model_beneficiario_view.beneficiario_nome = res_data.beneficiario.beneficiario_nome
      this.model_beneficiario_view.beneficiario_telefone = res_data.beneficiario.beneficiario_telefone
      this.model_beneficiario_view.empresa_id = res_data.beneficiario.empresa_id
      this.model_beneficiario_view.empresa_nome = res_data.beneficiario.empresa_nome,
        this.model_beneficiario_view.foto_perfil = res_data.beneficiario.foto_perfil,
        this.model_beneficiario_view.beneficio_proprio_beneficiario = res_data.beneficio_proprio_beneficiario;
      this.model_beneficiario_view.dependente_beneficiario_id = res_data.dependente_beneficiario_id;

      if(this.model_baixa.nome_beneficiario == this.model_beneficiario_view.beneficiario_nome){
        // this.selectdComprovativo = true;
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
    this.model_venda.beneficiario_id = this.model_beneficiario_view.beneficiario_id;
    this.model_venda.empresa_id = this.model_beneficiario_view.empresa_id;
    this.model_venda.beneficio_proprio_beneficiario = this.model_beneficiario_view.beneficio_proprio_beneficiario;
    this.model_venda.dependente_beneficiario_id = this.model_beneficiario_view.dependente_beneficiario_id;
    this.model_venda.valor = null;
    this.model_venda.nr_comprovativo = '';
    this.model_venda.accao_codigo = null;
    this.model_venda.itens_baixa = [];
    document.getElementById('closePesquisaModalID').click()
    this.iniciarVendaViewSwitch();
    if (this.selectedCardView == 1) {
      this.getServicos();
    } else if (this.selectedCardView == 3) {
      this.getServicos_pedido_autorizaaco();
    }
  }

  getServicos() {
    this.spinner_all = true;
    this._stock.get_servicos_us_iniciar_servico(this.model_beneficiario_view.beneficiario_id).subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.servicos_us = res_data.servicos_unidade_sanitaria;
      this.spinner_all = false;
    }, error => {
      // console.log(error)
      this.spinner_all = false;
    })
  }

  getServicos_pedido_autorizaaco() {
    this.spinner_all = true;
    this._stock.get_servicos_us_pedido_aprovacao(this.model_beneficiario_view.beneficiario_id).subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.servicos_us = res_data.servicos_unidade_sanitaria;
      this.spinner_all = false;
    }, error => {
      // console.log(error)
      this.spinner_all = false;
    })
  }

  addItem(item) {
    this.model_medicamento_view = item;
    this.quantidade_venda_item = null;
    this.preco_sem_iva_view = this.model_medicamento_view.preco;
    this.preco_com_iva_view = this.model_medicamento_view.preco_iva;
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
    if (quantidade) {
      this.model_venda.itens_baixa.push({
        servico_id: item_to_add.servico_id,
        preco: this.preco_sem_iva_view,
        iva: this.model_medicamento_view.iva,
        preco_iva: this.preco_com_iva_view,
        quantidade: quantidade
      })
    }

    let valor_temporario: number = 0;
    this.model_venda.itens_baixa.forEach(element => {
      valor_temporario = valor_temporario + Number(element.preco_iva)
    });
    this.model_venda.valor = Number(valor_temporario.toFixed(2));

  }

  removeItem(item) {
    this.model_venda.itens_baixa.forEach((element, index) => {
      if (item.servico_id == element.servico_id) {
        this.model_venda.itens_baixa.splice(index, 1);
      }
    });
  }

  submeterIniciarVenda(){
   
    console.log(this.model_baixa.nr_comprovativo)
    if(this.model_venda.nr_comprovativo.length < 1){
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
    if (this.selectedCardView == 41) {
      formData.append('accao_codigo', String(22)); // accao_codigo == 20
      formData.append('id', this.model_baixa.id);
      formData.append('nr_comprovativo', this.model_venda.nr_comprovativo);
    } 
    
    this._venda.submeterVendaUs(formData).subscribe(data => {
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
      // document.getElementById('closePesquisaModalID').click()
      this.iniciarVendaViewSwitch();
      this.getAllAguardaGasto();
      this.reloadCurrentRoute();
      
    }, error => {
      // console.log(error)
      this.spinner_all = false;
    })
  }


  submeterVenda() {

    if(this.selectedCardView == 41){
      this.submeterIniciarVenda()
    }

    if (this.selectedCardView == 1 || this.selectedCardView == 2 || this.selectedCardView == 4) {
      if (this.model_venda.nr_comprovativo.length < 1) {
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
        formData.append('accao_codigo', String(20)); // accao_codigo == 20
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

      this._venda.submeterVendaUs(formData).subscribe(data => {
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
        this.iniciarVendaViewSwitch();
      }, error => {
        // console.log(error)
        this.spinner_all = false;
      })
    }
    else if (this.selectedCardView == 3) {

      this.spinner_all = true;
      
      this.model_venda.accao_codigo = 40;
      this._venda.submeterPedidoAprovacaoUs(this.model_venda).subscribe(data => {
        // console.log(data);
        this.spinner_all = false;
        Swal.fire({
          title: 'Submetido.',
          text: "Pedido de Autorização submetido com sucesso.",
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

  // ================ UNIDADE SANITÁRIA ====================

  onVerify(){
    this.selectdComprovativo = !this.selectdComprovativo;
    console.log("cliclou em finalizar")
  } 

  updateUsSubmit() {
    this.submitted = true;
    if (this.form.invalid) {
      return
    }
    let texts = this.form.value.contactos.map(function (el) {
      return el.contacto;
    });
    this.form.value.contactos = texts;
    this.spinner = true;
    this._clinica.updateUnidadeSanitaria(this.form.value).subscribe(data => {
      this.form.reset();
      this.spinner = false;
      document.getElementById('actualizarID_Us').click();
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

  getUnidadeSanitaria() {
    this.spinner = true;
    this._clinica.getUnidadeSanitaria().subscribe(data => {
      let res = Object(data)["data"];
      let row = res.unidade_sanitaria;
      this.perfil_roles_unidade_sanitaria = res.categorias_unidade_sanitaria;

      this.submitBtn = false;
      let arrayValues: any[] = [];
      for (let index = 0; index < row.contactos.length; index++) {
        arrayValues.push({ contacto: row.contactos[index] });
      }
      this.form.patchValue({
        id: row.id,
        nome: row.nome,
        endereco: row.endereco,
        email: row.email,
        nuit: row.nuit,
        contactos: arrayValues,
        latitude: row.latitude,
        longitude: row.longitude,
        categoria_unidade_sanitaria_id: row.categoria_unidade_sanitaria_id,
        deleted_at: row.deleted_at,
      });

      this.submitBtn = false;
      this.form.disable();
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })

  }

  initializeFormUsSubmit() {
    this.form = this.formBuilder.group({
      nome: ['', Validators.required],
      endereco: ['', [Validators.required]],
      email: [''],
      categoria_unidade_sanitaria_id: [''],
      nuit: ['', [Validators.required]],
      contactos: this.formBuilder.array([this.formBuilder.group({ contacto: '' })], Validators.required),
      latitude: [''],
      longitude: [''],
      id: [''],
      deleted_at: [''],
      created_at: ['']
    });
  }

  toUpdate() {
    this.submitBtn = true;
    this.form.enable();
  }

  get arrayContactos() {
    return <FormArray>this.form.get('contactos');
  }

  addContacto() {
    this.arrayContactos.push(this.formBuilder.group({ contacto: '' }));
  }

  deleteContacto(index) {
    this.arrayContactos.removeAt(index);
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
    this._sugestoes.registerSugestaoUs(model).subscribe(data => {
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

  getAllTransacoes() {
    this.spinner = true;
    if (this.userRole == 6) { // UNIDADE SANITÁRIA
      this._gastos.getAllTransacoesUs().subscribe(data => {
        // console.log(this.userRole);
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

    else if (this.userRole == 2) { // FARMACIA
      this._gastos.getAllTransacoesFarmacia().subscribe(data => {
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

    else if (this.userRole == 2) { // FARMACIA
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


  getAllPedidos() {
    this.spinner = true;
    if (this.userRole == 6) { // UNIDADE SANITÁRIA
      this._gastos.getAllPedidosUni().subscribe(data => {
        // console.log(this.userRole);
        let res_data = Object(data)["data"]
        this.clientes_baixas = res_data.pedidos_aprovacao;
        // console.log("pedidos", res_data.pedidos_aprovacao);
        this.show_By_Estado = [];
        // console.log(this.show_By_Estado);
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

    else if (this.userRole == 2) { // FARMACIA
      this._gastos. getAllPedidosFarm().subscribe(data => {
        // console.log(data);
        let res_data = Object(data)["data"]
        this.clientes_baixas = res_data.pedidos;
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
  }

  viewCardBaixaModal(item) {
    console.log(item)
    this.selectdComprovativo = false
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
    this.model_baixa.responsavel = item.responsavel;
    this.model_baixa.nr_comprovativo = item.nr_comprovativo;


  }

  getFileDownload(fileName: string) {
    this.spinner_download = true;
    this._gastos.baixarComprovativoUs(this.model_baixa.proveniencia, this.model_baixa.id, fileName).subscribe(res => {
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

    this.model_baixa.descricao.forEach(element => {
      this.model_venda.itens_baixa.push({
        servico_id: element.servico_id,
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

    this.model_beneficiario_view.beneficiario_id = this.model_baixa.beneficiario_id;
    this.getServicos();
    this.iniciarVendaViewSwitch();

    document.getElementById('actualizarID_resubmeter').click();
  }

  // =========== UPLOAD FILE =====================
  detectFiles(event) {
    // this.fileUploadBoolean = false;
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
