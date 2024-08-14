import { Component, OnInit,ViewChild, ElementRef } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormArray, FormControl } from '@angular/forms';
import { MatDatepickerInputEvent,MatRadioChange,MatRadioButton } from '@angular/material';
import * as moment from 'moment';
import { Chart } from 'node_modules/chart.js';
import { OverviewService } from 'src/app/_services/overview.service';

@Component({
  selector: 'app-overview-empresa',
  templateUrl: './overview-empresa.component.html',
  styleUrls: ['./overview-empresa.component.scss']
})
export class OverviewEmpresaComponent implements OnInit {

  overview: any[] = [];
  servicos: any[] = [];
  servicos_farm: any[] = [];
  doencas_percent: any[] = [];
  descricao_servicos: any[] = [];
  descricao_servicos_farm: any[] = [];
  spinner: boolean = false;
  descricao_services: boolean = false;
  descricao_services_farm: boolean = false;
  startDate = '';
  endDate = '';
  farmId = '';
  farmUsOpt = '';
  farmacias: any[] = [];
  usId = '';
  us: any[] = [];
  form: FormGroup;
  @ViewChild('chartId1') private chartElement1: ElementRef;
  @ViewChild('chartId5') private chartElement5: ElementRef;
  @ViewChild('chartId6') private chartElement6: ElementRef;
  @ViewChild('chartId7') private chartElement7: ElementRef;
  filterId = 1;
  constructor(private _overview: OverviewService, private formBuilder: FormBuilder) { }

  ngOnInit() {

    let newDateMain  = moment.utc(Date.now()).local();
    let eDate = newDateMain.format("YYYY-MM-DD");
    this.graficos();
    this.getAll();
    this.getServicos(null,eDate,0,0);
    this.getBeneFaixas(null,eDate);
    this.initializeFormSubmit(); 

  }


  initializeFormSubmit() {
    this.form = this.formBuilder.group({
      startDate: [''],
      endDate: [''],
      farmUsOpt: [''],
      farmId: [''],
      usId: [''],
    });
  };


  separator(numb) {
    var str = numb.toString().split(".");
    str[0] = str[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return str.join(".");
}
  getAll(){
    this.spinner = true
    this._overview.getAll().subscribe(data => {
      let res_data = Object(data)["data"];
      res_data.valor_pago = res_data.valor_pago.toFixed(2);
      res_data.valor_pago = this.separator(res_data.valor_pago);
      res_data.valor_faturado = res_data.valor_faturado.toFixed(2);
      res_data.valor_faturado = this.separator(res_data.valor_faturado);
      res_data.valor_recusado = res_data.valor_recusado.toFixed(2);
      res_data.valor_recusado = this.separator(res_data.valor_recusado);
      res_data.valor_orcado = this.separator(res_data.valor_orcado);
      this.overview = res_data
      let farmacia_data = res_data.farmacias;
      this.farmacias = farmacia_data;
      let us_data = res_data.unidades_sanitarias;
      this.us = us_data;
      this.chartElement1.nativeElement.innerHTML = '&nbsp;';
      this.chartElement1.nativeElement.innerHTML = '<canvas id="myChart1" height="360px"></canvas>';
      this.chart(res_data.bene_ps);
      this.spinner = false
      // console.log(this.overview)
    })
  }

  getServicos(startDate,endDate,farmId,usId){
    this.spinner = true

    this.servicos = [];
    this.servicos_farm = [];

    this.descricao_servicos = [];
    this.descricao_servicos_farm = [];

    this.descricao_services = false;
    this.descricao_services_farm = false;

    this._overview.getAllServicos(startDate,endDate,usId,farmId).subscribe(data => {
      let res_data = Object(data)["data"];
      this.servicos = Object(data)["data"]
      this.servicos.forEach((element) =>{
        let servicos = element.descricao;
        servicos.forEach((element1)=>{
          this.descricao_servicos.forEach((element) => {
            if(element.servico == element1.servico){
              this.descricao_services = true
              element.quantidade += element1.quantidade
              element.preco = (parseFloat(element.preco) + parseFloat(element1.preco)).toFixed(2)
            }
          })
          if(!this.descricao_services){
            this.descricao_servicos.push(element1);
          }
        })
      })
      this.spinner = false
    });

    this._overview.getAllServicosFarm(startDate,endDate,farmId,usId).subscribe(data => {
      this.servicos_farm = Object(data)["data"]
      this.servicos_farm.forEach((element) =>{
        let servicos = element.descricao;
        servicos.forEach((element1)=>{
          this.descricao_servicos_farm.forEach((element) => {
            if(element.servico == element1.servico){
              this.descricao_services_farm = true
              element.quantidade += element1.quantidade
              element.preco = (parseFloat(element.preco) + parseFloat(element1.preco)).toFixed(2)
            }
          })
          if(!this.descricao_services_farm){
            this.descricao_servicos_farm.push(element1);
          }
        })
      })
      this.descricao_servicos.push(this.descricao_servicos_farm[0]);
      this.spinner = false
    });



  }

  graficos(){
    this.spinner = true
    this._overview.getAll().subscribe(data => {
      let res_data = Object(data)["data"]
      const myChart4 = new Chart("myChart4", {
        type: 'pie',
        data: {
          labels: ['% Margem lucro líquido', '% Objectivo'],
          datasets: [{
            label: '# of Votes',
            data: [12, 9],
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
            text: '% Sinistros'
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

      const myChart2 = new Chart("myChart2", {
        type: 'pie',
        data: {
          labels: ['% Margem lucro líquido', '% Objectivo'],
          datasets: [{
            label: '# of Votes',
            data: [12, 39],
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
            display: true
          }
        }
      });

      const lineChartData = [],
      array = res_data.barchart_datax;
      const randomNum = () => Math.floor(Math.random() * (235 - 52 + 1) + 52);
      const randomRGB = () => `rgb(${randomNum()}, ${randomNum()}, ${randomNum()})`;
      array.forEach(function (a) {
          lineChartData.push(a);
  });
  
      const myChart3 = new Chart("myChart3", {
        type: 'bar',
        data: {
          labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
          datasets: [{
            label: 'Executado',
            data: lineChartData[0],
            backgroundColor: [
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1'
            ],
            borderColor: [
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1',
              '#FFD8E1'
            ],
            borderWidth: 1
          },
          {
            label: 'Orçado',
            data: res_data.barchart_orcamento_mes,
            backgroundColor: [
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100'
            ],
            borderColor: [
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100',
              '#FF5100'
            ],
            borderWidth: 1
          }
        ]
        },
        options: {
          title: {
            display: true,
            text: 'Abertura de consumo vs orçado'
          },
          scales: {
            y: {
              beginAtZero: true
            }
          },
          legend: {
            display: true
          }
        }
      });

      this.ChartBaixasUS(res_data)

    })
  }

  getBeneFaixas(sDate,eDate){
    this._overview.getAllBeneFaixas(sDate,eDate).subscribe(data => {
      let res_data = Object(data)["data"]
      this.doencas_percent = res_data.doencas_nomes_percent
      const myChart5 = new Chart("myChart5", {
        type: 'pie',
        data: {
          labels: res_data.bene_intervalos,
          datasets: [{
            label: '# of Votes',
            data: res_data.bene_faixas_etarias,
            backgroundColor: [
              '#FF5100',
              '#FFD8E1',
              '#00bdaa'
            ],
            borderColor: [
              '#FF5100',
              '#FFD8E1',
              '#00bdaa'
            ],
            borderWidth: 1
          }]
        },
        options: {
          title: {
            display: true,
            text: '% Faixa etária'
          },
          scales: {
            y: {
              beginAtZero: true
            }
          },
          legend: {
            display: true
          }
        }
      });

      const myChart6 = new Chart("myChart6", {
        type: 'pie',
        data: {
          labels: res_data.doencas_nomes,
          datasets: [{
            // label: '# of Votes',
            data: res_data.doencas_nomes_percent,
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
          title: {
            display: true,
            text: 'Doenças crónicas'
          },
          scales: {
            y: {
              beginAtZero: true
            }
          },
          legend: {
            display: false,
            position: 'bottom',
            labels : {
              useLineStyle: false
          }
          }
        }
      });
    })
  }

  add() {
      let newDateMain  = moment.utc(Date.now()).local();
      let newDate = moment.utc(this.form.value.startDate).local();
      let newDate2 = moment.utc(this.form.value.endDate).local();
      this.form.value.startDate = newDate.format("YYYY-MM-DD");
      this.form.value.endDate = newDate2.format("YYYY-MM-DD");
      let newEndDate = newDateMain.format("YYYY-MM-DD");
      const sdate = this.form.value.startDate == 'Invalid date' ? null :this.form.value.startDate;
      const edate = this.form.value.endDate == 'Invalid date' ? newEndDate : this.form.value.endDate;
      const farm = this.form.value.farmId == "" ? 0: this.form.value.farmId;
      const us = this.form.value.usId == "" ? 0: this.form.value.usId;
      const us2 = this.form.value.usId == "" ? null: this.form.value.usId;
      this.spinner = true;
      this._overview.getAllBenBaixas(sdate,edate,farm,us).subscribe(data => {
        let res_data = Object(data)["data"];

        res_data.valor_pago = res_data.valor_pago.toFixed(2);
        res_data.valor_pago = this.separator(res_data.valor_pago);
        res_data.valor_faturado = res_data.valor_faturado.toFixed(2);
        res_data.valor_faturado = this.separator(res_data.valor_faturado);
        res_data.valor_recusado = res_data.valor_recusado.toFixed(2);
        res_data.valor_recusado = this.separator(res_data.valor_recusado);
        res_data.valor_orcado = res_data.valor_orcado.toFixed(2);
        res_data.valor_orcado = this.separator(res_data.valor_orcado);

        this.overview = res_data
        this.chartElement1.nativeElement.innerHTML = '&nbsp;';
        this.chartElement1.nativeElement.innerHTML = '<canvas id="myChart1" height="360px"></canvas>';
        this.chart(res_data.bene_ps)
        this.chartElement7.nativeElement.innerHTML = '&nbsp;';
        this.chartElement7.nativeElement.innerHTML = '<canvas id="myChart7" height="108px"></canvas>';
        this.ChartBaixasUS(res_data)

        this.spinner = false
        // console.log(res_data)
      })
      this.chartElement5.nativeElement.innerHTML = '&nbsp;';
      this.chartElement5.nativeElement.innerHTML = '<canvas id="myChart5" height="360px"></canvas>';
      this.chartElement6.nativeElement.innerHTML = '&nbsp;';
      this.chartElement6.nativeElement.innerHTML = '<canvas id="myChart6" height="380px">';
      
      this.getBeneFaixas(sdate,edate);
      
      this.getServicos(sdate,edate,farm,us);
      
    }


    chart(bene_ps){
      const myChart1 = new Chart("myChart1", {
        type: 'pie',
        data: {
          labels: ['% Não usaram', '% Usaram'],
          datasets: [{
            label: '# of Votes',
            data: bene_ps,
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
            text: '% de Beneficiarios que usaram o PS'
          },
          scales: {
            y: {
              beginAtZero: true
            }
          },
              legend: {
                display: true
              }
        }
      });
    }

    ChartBaixasUS(res_data){
      var lineChartData = { labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'], datasets: [] },
      array = res_data.stacked_data;
      const randomNum = () => Math.floor(Math.random() * (235 - 52 + 1) + 52);
      const randomRGB = () => `rgb(${randomNum()}, ${randomNum()}, ${randomNum()})`;
      array.forEach(function (a, i) {
          lineChartData.datasets.push({
              label: res_data.stacked_labels[i],
              data: a,
              backgroundColor: randomRGB(),
              borderColor: randomRGB(),
              borderWidth: 1
              
          });
  });


      const myChart7 = new Chart("myChart7", {
        type: 'bar',
        data: 
          lineChartData
        ,
        options: {
          title: {
            display: true,
            text: 'Sinistro por US'
          },
          scales: {
            xAxes: [{
              stacked:true
            }],
          yAxes: [{
            stacked:true,
            ticks: {
              stepSize: 1,
              beginAtZero: true
            }
            
          }]
          },
          legend: {
            display: true
          }
        }
      });
    }

    onSelectOption_farmacia(item) {
      this.farmId = item.value;
    }

    onSelectOption_farmus(item) {
      this.farmUsOpt = item.value;
      this.filterId = item.value;
      if(item.value == 1){
        this.form.get('usId').patchValue(0);
      }
      else if(item.value == 2){
        this.form.get('farmId').patchValue(0);
      }
      else if(item.value == 0){
        this.form.get('farmId').patchValue(0);
        this.form.get('usId').patchValue(0);
      }
    }
    onSelectOption_us(item) {
      this.usId = item.value;
    }
    addEvent(event: MatDatepickerInputEvent<Date>) {
     var x = event.value;
    }

    radioChange(event: MatRadioChange) {
      console.log(event.value);
      this.filterId = event.value;
      if(event.value == 1){
        this.form.get('usId').patchValue(0);
      }
      else if(event.value == 2){
        this.form.get('farmId').patchValue(0);
      }

    }

}