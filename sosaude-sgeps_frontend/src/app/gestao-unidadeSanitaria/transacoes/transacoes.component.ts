import { Component, OnInit, ChangeDetectorRef,ViewChild, ElementRef } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormArray, FormControl } from '@angular/forms';
import { AuthenticationService } from 'src/app/_services/authentication.service';
import { GastosService } from 'src/app/_services/gastos.service';
import Swal from 'sweetalert2';
import * as moment from 'moment';
import { Chart } from 'node_modules/chart.js';
import { OverviewFarmService } from 'src/app/_services/overview-farm.service';

@Component({
  selector: 'app-transacoes',
  templateUrl: './transacoes.component.html',
  styleUrls: ['./transacoes.component.scss']
})
export class TransacoesComponent implements OnInit {
  overview_resumo: any[] = []
  startDate = '';
  endDate = '';
  companyId = '';
  empresas: any[] = [];
  empresa_nome: [];
  form: FormGroup;
  spinner: boolean = false;
  @ViewChild('chartId') private chartElement: ElementRef;

  constructor(private _overviewFarm: OverviewFarmService, private formBuilder: FormBuilder ) {

  }

  ngOnInit() {
    this.getAll();
    this.getTransacoes();
    this.initializeFormSubmit(); 

  }
  initializeFormSubmit() {
    this.form = this.formBuilder.group({
      startDate: [''],
      endDate: [''],
      company: [],
      empresa_nome: [],
      companyId: [''],
    });
  };

  getAll() {
    this.spinner = true;
    this._overviewFarm.getAll().subscribe(data => {
      let res_data = Object(data)["data"]
      let company_data = res_data.empresas
      this.overview_resumo = res_data
      this.empresas = company_data
      console.log(data)

      this.chartElement.nativeElement.innerHTML = '&nbsp;';
      this.chartElement.nativeElement.innerHTML = '<canvas id="myChart2" height="160px"></canvas>';
      this.getChart(res_data.transacoes_percent);
      this.spinner = false;

    })
  }

  getChart(transacoes_percent){
    const myChart2 = new Chart("myChart2", {
      type: 'pie',
      data: {
        labels: ['% Rejeitados', '% Pagos', '% Pendentes'],
        datasets: [{
          label: '# of Votes',
          data: transacoes_percent,
          backgroundColor: [
            '#FF5100',
            '#FFD8E1',
            '#5f9e54'
          ],
          borderColor: [
            '#FF5100',
            '#FFD8E1',
            '#5f9e54'
          ],
          borderWidth: 1
        }]
      },
      options: {
        title: {
          display: true,
          text: '% Transações'
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
  }

  getAllFiltered() {
    this.spinner = true;
    let newDateMain  = moment.utc(Date.now()).local();
    let newEndDate = newDateMain.format("YYYY-MM-DD");
    let newDate = moment.utc(this.form.value.startDate).local();
      let newDate2 = moment.utc(this.form.value.endDate).local();
      this.form.value.startDate = newDate.format("YYYY-MM-DD");
      this.form.value.endDate = newDate2.format("YYYY-MM-DD");
      const x = this.form.value.startDate == 'Invalid date' ? null :this.form.value.startDate;
      const y = this.form.value.startDate == 'Invalid date' ? newEndDate : this.form.value.endDate;
      const z = this.form.value.companyId == "" ? 0 : this.form.value.companyId;


    this._overviewFarm.getAllFiltered(x,y,z).subscribe(data => {
      let res_data = Object(data)["data"]
      this.overview_resumo = res_data
      console.log(data)
      this.chartElement.nativeElement.innerHTML = '&nbsp;';
      this.chartElement.nativeElement.innerHTML = '<canvas id="myChart2" height="160px"></canvas>'
      this.getChart(res_data.transacoes_percent);

    })

    this.spinner = false;
  }

  onSelectOption_empresa(item) {
    this.companyId = item.value;
  }

  getTransacoes(){
    this._overviewFarm.getAllTransacoes().subscribe(data => {
      let res_data = Object(data)["data"]
      // const myChart2 = new Chart("myChart2", {
      //   type: 'horizontalBar',
      //   data: {
      //     labels: res_data.empresas,
      //     datasets: [
      //       {
      //         label: 'Aguarda confirmação',
      //         data: res_data.gasto_aguarda_confirmacao,
      //         borderColor: '#00AEFF',
      //         backgroundColor: '#ff551e',
      //         fill: false
      //       },
      //       {
      //         label: 'Aguarda pagamento',
      //         data: res_data.gasto_aguarda_pagamento,
      //         borderColor: "#00bdaa",
      //         backgroundColor: '#00bdaa',
      //         fill: false 
      //       }
      //     ]
      //   },
      //   options: {
      //     indexAxis: 'y',
      //     scales: {
      //       xAxes: [{ 
      //         ticks: {
      //           beginAtZero: true
      //         }
      //       }]
      //     },
      //     title: {
      //       display: true,
      //       text: 'Transações'
      //     }
      //   }
      // });
    })
  }



  
}
