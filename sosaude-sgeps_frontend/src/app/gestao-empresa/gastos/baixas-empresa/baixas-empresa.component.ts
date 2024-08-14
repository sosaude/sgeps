import { Component, OnInit, ChangeDetectorRef,ViewChild, ElementRef } from '@angular/core';
import { AuthenticationService } from 'src/app/_services/authentication.service';
import Swal from 'sweetalert2';
import { GastosService } from 'src/app/_services/gastos.service';
import { Chart } from 'node_modules/chart.js';
import { ExcelService } from 'src/app/_services/excel.service';

@Component({
  selector: 'app-baixas-empresa',
  templateUrl: './baixas-empresa.component.html',
  styleUrls: ['./baixas-empresa.component.scss']
})
export class BaixasEmpresaComponent implements OnInit {

  authValue: any;
  searchText: any;
  teste: any;
  model_baixa = {
    id: null,
    proveniencia: null,
    responsavel: null,
    nome_beneficiario: '',
    nome_instituicao: '',
    valor_baixa: '',
    estado_id: null,
    nr_comprovativo: '',
    estado_nome: '',
    estado_codigo: '',
    data_baixa: '',
    updated_at: '',
    descricao: [],
    comprovativo: [],
    comentario_baixa: [],
    comentario_pedido_aprovacao: [],
    beneficio_proprio_beneficiario: null,
    nome_dependente: null
  }
  @ViewChild('chartId1') private chartElement1: ElementRef;
  @ViewChild('chartId2') private chartElement2: ElementRef;
  @ViewChild('chartId3') private chartElement3: ElementRef;
  @ViewChild('chartId4') private chartElement4: ElementRef;

  array_bulk: any[] = [];

  submitted: boolean = false;
  spinner: boolean;
  clientes_baixas: any[];
  clientes_baixas_excel: any[];
  show_By_Estado: any[] = [];
  resumo: any[] = [];
  selectedCardView: number = 1;
  spinner_1: boolean = false;
  fileUploadBoolean: boolean = false;
  selectedFiles: File[] = [];
  spinner_download: boolean = false;
  comentario_devolucao: any;
  p = 1;

  constructor(private authenticationService: AuthenticationService,
    private _gastos: GastosService,
    private excelService: ExcelService,
    private cd: ChangeDetectorRef) {
    if (this.authenticationService.currentUserValue) {
      this.authValue = this.authenticationService.currentUserValue;
      if (this.authenticationService.currentUserValue.user.role.codigo == 1) {
        // this.router.navigate(['/empresas']);
      }
    }
  }

  ngOnInit() {
    this.getAll();
    this.getResumo()
  }

  changeView(opcao) {
    switch (opcao) {
      case 1:
        this.selectedCardView = 1;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '10') {
            this.show_By_Estado.push(element)
          }
        });
        this.array_bulk = [];
        break;
      case 2:
        this.selectedCardView = 2;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '11') {
            this.show_By_Estado.push(element)
          }
        });
        this.array_bulk = [];
        break;
      case 3:
        this.selectedCardView = 3;
        this.show_By_Estado = [];
        this.clientes_baixas.forEach(element => {
          if (element.estado_codigo == '12') {
            this.show_By_Estado.push(element)
          }
        });
        this.array_bulk = [];
        break;
    }
  }

  getAll() {
    this.spinner = true;
    this._gastos.getAllBaixas().subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.clientes_baixas = res_data.baixas;
      this.resumo = res_data.resumo

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
  getAllExcel() {
    this.spinner = true;
    this._gastos.getAllBaixasExcel().subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.clientes_baixas_excel = res_data.baixas;
      this.excelService.exportAsExcelFile(this.clientes_baixas_excel, 'Baixas_Empresa');

      this.resumo = res_data.resumo

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

  getResumo() {
    this._gastos.getAllBaixas().subscribe(data => {
      let res_data = Object(data)["data"]
      const ctx = document.getElementById('myChart');
      const score = res_data.resumo
      this.resumo = res_data.baixas

      this.resumo.forEach((element) => {
        // console.log(element)
        if (element.estado_codigo == '10') {
          this.teste = element
          // console.log(element)
        }
      })

      console.log(this.resumo)

      this.chartElement1.nativeElement.innerHTML = '&nbsp;';
      this.chartElement1.nativeElement.innerHTML = '<canvas id="myChart" height="250px"></canvas>';

      const myChart = new Chart("myChart", {
        type: 'bar',
        data: {
          labels: ['Esperando confirmação', 'Esperando pagamento'],
          datasets: [{
            label: `Número de Processos`,
            data: score,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)'
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
          legend: {
            display: false
          }
        }
    });


    this.chartElement2.nativeElement.innerHTML = '&nbsp;';
    this.chartElement2.nativeElement.innerHTML = '<canvas id="myChart2" height="250px"></canvas>';
      const myChart2 = new Chart("myChart2", {
        type: 'pie',
        data: {
          labels: ['Esperando confirmação', 'Esperando pagamento'],
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
          },
           
              legend: {
                display: false
              }
        }
      });

      this.chartElement4.nativeElement.innerHTML = '&nbsp;';
      this.chartElement4.nativeElement.innerHTML = '<canvas id="myChart4" height="250px"></canvas>';
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
            },
              legend: {
                display: false
              }
          }
        }
      });

      this.chartElement3.nativeElement.innerHTML = '&nbsp;';
      this.chartElement3.nativeElement.innerHTML = '<canvas id="myChart3" height="250px"></canvas>';
      const myChart3 = new Chart("myChart3", {
        type: 'bar',
        data: {
          labels: res_data.meses2,
          //  labels: ['Número de Processos mensais'],
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
  getPagamento() {
    this.spinner = true;
    this._gastos.getAllBaixas().subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.clientes_baixas = res_data.baixas;
      this.show_By_Estado = [];
      this.clientes_baixas.forEach(element => {
        if (element.estado_codigo == '11') {
          this.show_By_Estado.push(element)
        }
      });
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }


  viewCard(item) {
    console.log(item)
    // this.model_baixa.comprovativo = [];
    this.selectedFiles = []
    this.model_baixa.data_baixa = item.data_baixa;
    this.model_baixa.updated_at = item.updated_at;
    this.model_baixa.descricao = item.descricao;
    this.model_baixa.estado_id = item.estado_id;
    this.model_baixa.estado_codigo = item.estado_codigo;
    this.model_baixa.estado_nome = item.estado_nome;
    this.model_baixa.nr_comprovativo = item.nr_comprovativo;
    this.model_baixa.id = item.id;
    this.model_baixa.nome_beneficiario = item.nome_beneficiario;
    this.model_baixa.nome_instituicao = item.nome_instituicao;
    this.model_baixa.responsavel = item.responsavel;
    this.model_baixa.proveniencia = item.proveniencia;
    this.model_baixa.valor_baixa = item.valor_baixa;
    this.model_baixa.comprovativo = item.comprovativo;
    this.model_baixa.comentario_baixa = item.comentario_baixa;
    this.model_baixa.comentario_pedido_aprovacao = item.comentario_pedido_aprovacao;
    this.model_baixa.beneficio_proprio_beneficiario = item.beneficio_proprio_beneficiario;
    this.model_baixa.nome_dependente = item.nome_dependente;
  }

  confirmarSubmit() {
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
        const formData = new FormData();
        formData.append('id', this.model_baixa.id);
        formData.append('proveniencia', this.model_baixa.proveniencia);
        if (this.selectedFiles.length) {
          for (let i = 0; i < this.selectedFiles.length; i++)
            formData.append('ficheiros[]', this.selectedFiles[i], this.selectedFiles[i].name);
        }

        this.spinner_1 = true;
        this._gastos.confirmarBaixa(formData).subscribe(res => {
          document.getElementById('actualizarID').click();
          this.getAll();
          this.getResumo();
          this.spinner_1 = false;
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
            this.spinner_1 = false;

          });
      }
    })
  }

  processarPagamento() {
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

        this._gastos.confirmarPagamento(this.model_baixa).subscribe(res => {
          document.getElementById('actualizarID').click();
          // this.getAll();
          this.getPagamento();
          this.getResumo();
          this.spinner_1 = false;
          Swal.fire({
            title: 'Submetido.',
            text: "Pagamento processado com sucesso.",
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

  rejeitarBaixaSubmit() {
    Swal.fire({
      title: 'Tem certeza que deseja devolver a Baixa?',
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
        let model = {
          proveniencia: this.model_baixa.proveniencia,
          id: this.model_baixa.id,
          comentario_baixa: this.comentario_devolucao
        }

        this._gastos.devolverBaixa(model).subscribe(res => {
          document.getElementById('actualizarID').click();
          // this.getAll();
          this.getPagamento();
          this.spinner_1 = false;
          this.comentario_devolucao = "";
          Swal.fire({
            title: 'Submetido.',
            text: "Devolvido com sucesso.",
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
  changeStateGasto(event, proveniencia, id) {
    if (event.checked) {
      this.array_bulk.push({
        proveniencia: proveniencia,
        id: id
      })
    }
    else if (!event.checked) {
      this.array_bulk.forEach((element, index) => {
        if (element.id == id && element.proveniencia == proveniencia) {
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
          "baixas": this.array_bulk
        }

        this.spinner = true;
        if (this.selectedCardView == 1) {
          this._gastos.confirmarBaixaBulk(model).subscribe(res => {
            this.getAll();
            this.getResumo();
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
          this._gastos.processar_pagamento_Baixa_Bulk(model).subscribe(res => {
            this.getAll();
            this.getResumo();
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

  getFileDownload(fileName: string) {
    this.spinner_download = true;
    this._gastos.baixarComprovativo(this.model_baixa.proveniencia, this.model_baixa.id, fileName).subscribe(res => {
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

  // exportAsXLSX(): void {
  //   this.excelService.exportAsExcelFile(this.beneficiarios_excel, 'Beneficiarios');
  // }

}
