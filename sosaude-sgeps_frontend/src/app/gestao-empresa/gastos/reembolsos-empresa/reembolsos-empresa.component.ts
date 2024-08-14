import { Component, OnInit, ChangeDetectorRef } from '@angular/core';
import { AuthenticationService } from 'src/app/_services/authentication.service';
import Swal from 'sweetalert2';
import { GastosService } from 'src/app/_services/gastos.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MatSlideToggleChange } from '@angular/material';
import { Chart } from 'node_modules/chart.js';
import { ExcelService } from 'src/app/_services/excel.service';

@Component({
  selector: 'app-reembolsos-empresa',
  templateUrl: './reembolsos-empresa.component.html',
  styleUrls: ['./reembolsos-empresa.component.scss']
})
export class ReembolsosEmpresaComponent implements OnInit {

  authValue: any;
  searchText: any;
  model_pedido_reembolso = {
    id: null,
    beneficio_proprio_beneficiario: null,
    comentario: null,
    comprovativo: [],
    data: '',
    estado: null,
    estado_pedido_reembolso_codigo: '',
    estado_texto: '',
    nome_beneficiario: '',
    nome_dependente: '',
    responsavel: [],
    nr_comprovativo: '',
    servico_prestado: '',
    unidade_sanitaria: '',
    valor: '',
    updated_at: '',
  }

  submitted: boolean = false;
  spinner: boolean;
  clientes_pedido_reembolso: any[];
  clientes_pedido_reembolso_excel: any[];
  show_By_Estado: any[] = [];
  selectedCardView: number = 1;
  spinner_1: boolean = false;
  fileUploadBoolean: boolean = false;
  selectedFiles: File[] = [];
  spinner_download: boolean = false;
  invalid_comentario: boolean = false;
  comentario_devolucao: string = '';
  form: FormGroup;
  servicos_prestados: any[];
  resumo: any[] = []

  modelData = {
    dia: null,
    mes: null,
    ano: null
  }
  selectdiaMesAnoBoolean: boolean = false;
  dias: any[] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31]
  meses: any[] = [
    { nome: 'Janeiro', value: 1 }, { nome: 'Fevereiro', value: 2 },
    { nome: 'Março', value: 3 }, { nome: 'Abril', value: 4 },
    { nome: 'Maio', value: 5 }, { nome: 'Junho', value: 6 },
    { nome: 'Julho', value: 7 }, { nome: 'Agosto', value: 8 },
    { nome: 'Setembro', value: 9 }, { nome: 'Outubro', value: 10 },
    { nome: 'Novembro', value: 11 }, { nome: 'Dezembro', value: 12 }
  ]

  currentYear = new Date().getFullYear();
  anos: any[] = [];
  create_reembolso: any;
  dependentes_array: any[] = [];
  dependente_isBeneficiario: boolean = false;
  isServico_outro: boolean = false;
  isServico_outro_value: string = '';
  array_bulk: any[] = [];

  constructor(private authenticationService: AuthenticationService,
    private excelService: ExcelService,
    private _gastos: GastosService,
    private formBuilder: FormBuilder,
    private cd: ChangeDetectorRef) {
    if (this.authenticationService.currentUserValue) {
      this.authValue = this.authenticationService.currentUserValue;
    }
  }

  ngOnInit() {
    this.getAll();
    this.getResumo();
    this.getCreate();
    this.initializeFormSubmit();

    for (let index = 0; index < 5; index++) {
      this.anos.push(this.currentYear)
      this.currentYear = this.currentYear - 1;
    }

  }
  getAllExcel() {
    this.spinner = true;
    this._gastos.getAllPedidosReembolsoExcel().subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.clientes_pedido_reembolso_excel = res_data.baixas;
      this.excelService.exportAsExcelFile(this.clientes_pedido_reembolso_excel, 'Reembolso_Empresa');

      // this.resumo = res_data.resumo

      // this.show_By_Estado = [];


      // this.clientes_baixas.forEach(element => {
      //   if (element.estado_codigo == '10') {
      //     this.show_By_Estado.push(element)

      //   }
      // });
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  changeView(opcao) {

    switch (opcao) {
      case 1:
        this.selectedCardView = 1;
        this.show_By_Estado = [];
        this.clientes_pedido_reembolso.forEach(element => {
          if (element.estado_pedido_reembolso_codigo == '10') {
            this.show_By_Estado.push(element)
          }
        });
        this.array_bulk = [];
        break;
      case 2:
        this.selectedCardView = 2;
        this.show_By_Estado = [];
        this.clientes_pedido_reembolso.forEach(element => {
          if (element.estado_pedido_reembolso_codigo == '11') {
            this.show_By_Estado.push(element)
          }
        });
        this.array_bulk = [];
        break;
      case 3:
        this.selectedCardView = 3;
        this.show_By_Estado = [];
        this.clientes_pedido_reembolso.forEach(element => {
          if (element.estado_pedido_reembolso_codigo == '12') {
            this.show_By_Estado.push(element)
          }
        });
        this.array_bulk = [];
        break;
      case 4:
        this.selectedCardView = 4;
        this.show_By_Estado = [];
        this.clientes_pedido_reembolso.forEach(element => {
          if (element.estado_pedido_reembolso_codigo == '13') {
            this.show_By_Estado.push(element)
          }
        });
        this.array_bulk = [];
        break;
    }
  }

  getAll() {
    this.spinner = true;
    this._gastos.getAllPedidosReembolso().subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.clientes_pedido_reembolso = res_data.baixas
      this.show_By_Estado = [];
      this.clientes_pedido_reembolso.forEach(element => {
        if (element.estado_pedido_reembolso_codigo == '10') {          
          this.show_By_Estado.push(element)
        }
      });
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  getResumo() {
    this._gastos.getAllPedidosReembolso().subscribe(data => {
      let res_data = Object(data)["data"]
      const ctx = document.getElementById('myChart');
      const score = res_data.resumo
      this.resumo = res_data.baixas

      this.resumo.forEach((element) => {
        // console.log(element)
        if (element.estado_codigo == '10') {
          // this.teste = element
          // console.log(element)
        }
      })

      console.log(this.resumo)


      const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['Aguarda confirmação', 'Aguarda pagamento'],
          datasets: [{
            label: `Número de Processos`,
            data: score,
            backgroundColor: [
              '#FF5100',
              '#FFD8E1'
            ],
            borderColor: [
              '#FF5100',
              '#FFD8E1'
            ],
            borderWidth: 1
          }]
        },
        options: {
          indexAxis: 'x',
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero: true
              }
            }]
          }
        }
      });

      const myChart4 = new Chart("myChart4", {
        type: 'pie',
        data: {
          labels: ['Fármacia', 'Unidade Sanitária'],
          datasets: [{
            label: '# of Votes',
            data: res_data.total_baixas,
            backgroundColor: [
              '#FF5100',
              '#FFD8E1'
            ],
            borderColor: [
              '#FF5100',
              '#FFD8E1'
            ],
            borderWidth: 1
          }]
        },
        options: {
          title: {
            display: true,
            text: 'Proveniencia dos Processos'
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });



      const myChart2 = new Chart("myChart2", {
        type: 'pie',
        data: {
          labels: ['Aguarda confirmação', 'Aguarda pagamento'],
          datasets: [{
            label: '# of Votes',
            data: res_data.valor_total,
            backgroundColor: [
              '#FF5100',
              '#FFD8E1'
            ],
            borderColor: [
              '#FF5100',
              '#FFD8E1'
            ],
            borderWidth: 1
          }]
        },
        options: {
          title: {
            display: true,
            text: 'Número de valores'
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
      const myChart3 = new Chart("myChart3", {
        type: 'bar',
        data: {
          labels: res_data.meses2,
          // labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
          datasets: [{
            label: 'Número de Processos mensais',
            data: res_data.meses_baixas2,
            backgroundColor: [
              '#FF5100',
              '#FFD8E1',
              '#00bdaa',
              '#E14D57',
              '#EC932F',
              '#FECB4F',
              '#FF5100',
              '#FFD8E1',
              '#00bdaa',
              '#E14D57',
              '#EC932F',
              '#FECB4F'
            ],
            borderColor: [
              '#FF5100',
              '#FFD8E1',
              '#00bdaa',
              '#E14D57',
              '#EC932F',
              '#FECB4F',
              '#FF5100',
              '#FFD8E1',
              '#00bdaa',
              '#E14D57',
              '#EC932F',
              '#FECB4F'
            ],
            borderWidth: 1
          }]
        },
        options: {
          // title: {
          //   display: true,
          //   text: 'Número de valores'
          // },
          indexAxis: 'x',
          scales: {
            yAxes: [{ 
              ticks: {
                beginAtZero: true
              }
            }]
          },
        }
      });
    })

  }

  getCreate() {
    this.spinner = true;
    this._gastos.CreateParaPedidosReembolso().subscribe(data => {
      // console.log(data);
      this.create_reembolso = Object(data)["data"]
      this.servicos_prestados = this.create_reembolso.gastos_reembolso;
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }


  viewCard(item) {
    // console.log(item);
    
    this.comentario_devolucao = null;
    this.selectedFiles = [];

    this.model_pedido_reembolso.beneficio_proprio_beneficiario = item.beneficio_proprio_beneficiario;
    this.model_pedido_reembolso.comentario = item.comentario;
    this.model_pedido_reembolso.comprovativo = item.comprovativo;
    this.model_pedido_reembolso.data = item.data;
    this.model_pedido_reembolso.estado = item.estado;
    this.model_pedido_reembolso.estado_texto = item.estado_texto;
    this.model_pedido_reembolso.estado_pedido_reembolso_codigo = item.estado_pedido_reembolso_codigo;
    this.model_pedido_reembolso.id = item.id;
    this.model_pedido_reembolso.nome_beneficiario = item.nome_beneficiario;
    this.model_pedido_reembolso.nome_dependente = item.nome_dependente;
    this.model_pedido_reembolso.responsavel = item.responsavel;
    this.model_pedido_reembolso.nr_comprovativo = item.nr_comprovativo;
    this.model_pedido_reembolso.servico_prestado = item.servico_prestado;
    this.model_pedido_reembolso.unidade_sanitaria = item.unidade_sanitaria;
    this.model_pedido_reembolso.valor = item.valor;
    this.model_pedido_reembolso.updated_at = item.updated_at;

  }


  confirmacaoPagamento() {
    this.invalid_comentario = false;
    Swal.fire({
      title: 'Tem certeza que deseja confirmar?',
      text: "",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: "#f15726",
      cancelButtonText: "Cancelar",
      // cancelButtonColor: '#d33',
      confirmButtonText: 'Sim',
      reverseButtons: true
    }).then((result) => {
      if (result.value) {
        this.spinner_1 = true;

        const formData = new FormData();
        formData.append('id', this.model_pedido_reembolso.id);
        formData.append('comentario', this.comentario_devolucao);

        if (this.selectedFiles.length) {
          for (let i = 0; i < this.selectedFiles.length; i++)
            formData.append('ficheiros[]', this.selectedFiles[i], this.selectedFiles[i].name);
        }

        this._gastos.confirmarPedidosReembolso(formData).subscribe(res => {
          document.getElementById('actualizarID').click();
          this.getAll();
          this.spinner_1 = false;
          Swal.fire({
            title: 'Submetido.',
            text: "Pedido de reembolso processado com sucesso.",
            type: 'success',
            showCancelButton: false,
            confirmButtonColor: "#f15726",
            confirmButtonText: 'Ok'
          })
        },
          error => {
            this.spinner_1 = false;

          });
      }
    })
  }

  processarPagamento() {
    this.invalid_comentario = false;
    Swal.fire({
      title: 'Tem certeza que deseja Processar o pagamento?',
      text: "",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: "#f15726",
      cancelButtonText: "Cancelar",
      // cancelButtonColor: '#d33',
      confirmButtonText: 'Sim',
      reverseButtons: true
    }).then((result) => {
      if (result.value) {
        this.spinner_1 = true;
        const formData = new FormData();
        formData.append('id', this.model_pedido_reembolso.id);
        formData.append('comentario', this.comentario_devolucao);

        if (this.selectedFiles.length) {
          for (let i = 0; i < this.selectedFiles.length; i++)
            formData.append('ficheiros[]', this.selectedFiles[i], this.selectedFiles[i].name);
        }

        this._gastos.processarPagamentoPedidosReembolso(formData).subscribe(res => {
          document.getElementById('actualizarID').click();
          this.getAll();
          this.spinner_1 = false;
          Swal.fire({
            title: 'Submetido.',
            text: "Pedido de reembolso processado com sucesso.",
            type: 'success',
            showCancelButton: false,
            confirmButtonColor: "#f15726",
            confirmButtonText: 'Ok'
          })
        },
          error => {
            this.spinner_1 = false;

          });
      }
    })
  }

  onChangeInput(event) {
    if (this.comentario_devolucao.length > 1) {
      this.invalid_comentario = false;
    }
  }

  devolverPedidoReembolso() {
    // console.log(this.comentario_devolucao);

    if (!this.comentario_devolucao) {
      this.invalid_comentario = true;
      return;
    }
    else if (this.comentario_devolucao.length <= 1) {
      this.invalid_comentario = true;
      return;
    }
    else if (this.comentario_devolucao.length > 1) {
      this.invalid_comentario = false;
    }

    Swal.fire({
      title: 'Tem certeza que deseja devolver o pedido?',
      text: "",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: "#f15726",
      cancelButtonText: "Cancelar",
      // cancelButtonColor: '#d33',
      confirmButtonText: 'Sim',
      reverseButtons: true
    }).then((result) => {
      if (result.value) {
        this.spinner_1 = true;
        this.model_pedido_reembolso.comentario = this.comentario_devolucao;
        this._gastos.devolverPedidosReembolso(this.model_pedido_reembolso).subscribe(res => {
          document.getElementById('actualizarID').click();
          this.getAll();
          this.spinner_1 = false;
          Swal.fire({
            title: 'Submetido.',
            text: "Pedido processado com sucesso.",
            type: 'success',
            showCancelButton: false,
            confirmButtonColor: "#f15726",
            confirmButtonText: 'Ok'
          })
        },
          error => {
            this.spinner_1 = false;

          });
      }
    })
  }

    //  ================== BULK =================
    changeStateGasto(event, id) {
      if (event.checked) {
        this.array_bulk.push({
          id: id
        })
      }
      else if (!event.checked) {
        this.array_bulk.forEach((element, index) => {
          if (element.id == id) {
            this.array_bulk.splice(index, 1)
          }
        });
      }
    }
  
    stateGasto(id) {
      return this.array_bulk.some(element => element['id'] === id)
    }
  
    submeterGastoBulk() {
      Swal.fire({
        title: 'Tem certeza que deseja Submeter?',
        text: "",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: "#f15726",
        cancelButtonText: "Cancelar",
        // cancelButtonColor: '#d33',
        confirmButtonText: 'Sim',
        reverseButtons: true
      }).then((result) => {
        if (result.value) {
  
  
          let model = {
            "pedidos_reembolso": this.array_bulk
          }
  
          this.spinner = true;
          if (this.selectedCardView == 1) {
            this._gastos.confirmarPedidosReembolsoBulk(model).subscribe(res => {
              this.getAll();
              this.array_bulk = [];
              this.spinner = false;
              Swal.fire({
                title: 'Submetido.',
                text: "Confirmado com sucesso.",
                type: 'success',
                showCancelButton: false,
                confirmButtonColor: "#f15726",
                confirmButtonText: 'Ok'
              })
            },
              error => {
                this.spinner = false;
              });
          } else if (this.selectedCardView == 2) {
            this._gastos.processarPagamentoPedidosReembolsoBulk(model).subscribe(res => {
              this.getAll();
              this.array_bulk = [];
              this.spinner = false;
              Swal.fire({
                title: 'Submetido.',
                text: "Processado com sucesso.",
                type: 'success',
                showCancelButton: false,
                confirmButtonColor: "#f15726",
                confirmButtonText: 'Ok'
              })
            },
              error => {
                this.spinner = false;
              });
          }
  
        }
      })
    }
  
    // ===============================================
  

  getFileDownload(fileName: string) {
    this.spinner_download = true;
    this._gastos.baixarFilePedidoReembolso(this.model_pedido_reembolso.id, fileName).subscribe(res => {
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

  // =============== PEDIDO DE REEMBOLSO ======================

  addPedido() {
    this.modelData.ano = null;
    this.modelData.dia = null;
    this.modelData.mes = null;
    this.selectedFiles = [];
    this.form.reset();
    this.form.get('beneficio_proprio_beneficiario').setValue(true);
    this.dependente_isBeneficiario = false;
  }

  initializeFormSubmit() {
    this.form = this.formBuilder.group({
      unidade_sanitaria: [true, Validators.required],
      servico_prestado: ['', Validators.required],
      nr_comprovativo: ['', Validators.required],
      valor: ['', Validators.required],
      data: ['', Validators.required],
      beneficio_proprio_beneficiario: [true, Validators.required],
      beneficiario_id: ['', Validators.required],
      comentario: [''],
      dependente_beneficiario_id: [''],
    });
  }

  onChange_dependentes(ob: MatSlideToggleChange) {
    if (ob.checked) {
      this.dependente_isBeneficiario = false;
      // this.form.get('dependente_beneficiario_id').setValidators([]);
      this.form.get('beneficio_proprio_beneficiario').setValue(true);
    } else {
      this.dependente_isBeneficiario = true;
      // this.form.get('dependente_beneficiario_id').setValidators([Validators.required]);
      this.form.get('beneficio_proprio_beneficiario').setValue(false);
    }
  }

  submitPedido() {
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
    this.form.get('data').patchValue(stringDate);

    // console.log(this.form.value);
    
    if (this.isServico_outro) {
      this.form.value.servico_prestado = this.form.value.servico_prestado + ' - ' + this.isServico_outro_value;
    }

    if (this.form.invalid) {
      return
    }


    this.spinner = true;
    const formData = new FormData();

    if (this.form.get('beneficio_proprio_beneficiario').value) {
      this.form.get('beneficio_proprio_beneficiario').setValue(1)
      formData.append('unidade_sanitaria', this.form.value.unidade_sanitaria);
      formData.append('servico_prestado', this.form.value.servico_prestado);
      formData.append('nr_comprovativo', this.form.value.nr_comprovativo);
      formData.append('valor', this.form.value.valor);
      formData.append('data', this.form.value.data);
      formData.append('comentario', this.form.value.comentario);
      formData.append('beneficio_proprio_beneficiario', this.form.value.beneficio_proprio_beneficiario);
      formData.append('beneficiario_id', this.form.value.beneficiario_id);
      // formData.append('dependente_beneficiario_id', this.form.value.dependente_beneficiario_id);
  
    } else {
      this.form.get('beneficio_proprio_beneficiario').setValue(0)

      formData.append('unidade_sanitaria', this.form.value.unidade_sanitaria);
      formData.append('servico_prestado', this.form.value.servico_prestado);
      formData.append('nr_comprovativo', this.form.value.nr_comprovativo);
      formData.append('valor', this.form.value.valor);
      formData.append('data', this.form.value.data);
      formData.append('comentario', this.form.value.comentario);
      formData.append('beneficio_proprio_beneficiario', this.form.value.beneficio_proprio_beneficiario);
      formData.append('beneficiario_id', this.form.value.beneficiario_id);
      formData.append('dependente_beneficiario_id', this.form.value.dependente_beneficiario_id);
  
    }

    if (this.selectedFiles.length) {
      for (let i = 0; i < this.selectedFiles.length; i++)
        formData.append('ficheiros[]', this.selectedFiles[i], this.selectedFiles[i].name);
    }

    this._gastos.registarPedidoReembolso(formData).subscribe(data => {
      this.form.reset();
      this.spinner = false;
      this.modelData.ano = null;
      this.modelData.dia = null;
      this.modelData.mes = null;
      document.getElementById('addPedidoID').click();
      this.getAll()
      Swal.fire({
        title: 'Submetido.',
        text: "Submetido com sucesso.",
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

  dateBinding() {
    this.selectdiaMesAnoBoolean = false;
  }

  selectedBeneficiario() {
    this.dependentes_array = [];
    this.create_reembolso.beneficiarios.forEach((element, index) => {
      if (element.id == this.form.get('beneficiario_id').value) {
        this.dependentes_array = element.dependentes_beneficiario;
      }
    });
  }

  selectedServico() {
    // console.log(this.form.get('servico_prestado').value);
    this.isServico_outro = false;
    this.servicos_prestados.forEach((element, index) => {
      if ('Outros' == this.form.get('servico_prestado').value) {
        this.isServico_outro = true;
      }
    });
  }
  // ========== FILES =========
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
