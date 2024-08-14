import { Component, OnInit, ChangeDetectorRef,ViewChild, ElementRef } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormArray, FormControl } from '@angular/forms';
import { AuthenticationService } from 'src/app/_services/authentication.service';
import { GastosService } from 'src/app/_services/gastos.service';
import Swal from 'sweetalert2';
import * as moment from 'moment';
import { Chart } from 'node_modules/chart.js';
import { OverviewFarmService } from 'src/app/_services/overview-farm.service';

@Component({
  selector: 'app-resumo-grafico',
  templateUrl: './resumo-grafico.component.html',
  styleUrls: ['./resumo-grafico.component.scss']
})
export class ResumoGraficoComponent implements OnInit {
  overview_resumo: any[] = []
  startDate = '';
  formInitDate = '';
  formFinalDate = '';
  endDate = '';
  companyId = '';
  empresas: any[] = [];
  empresa_nome: [];
  form: FormGroup;
  spinner: boolean = false;
  @ViewChild("myNameElem") myNameElem: ElementRef;
  @ViewChild('chartId') private chartElement: ElementRef;


  constructor(private _overviewUS: OverviewFarmService, private formBuilder: FormBuilder) {
  }

  ngOnInit() {
    this.initializeFormSubmit();
    this.getAllFiltered();
    
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

  getAllFiltered() {
    this.spinner = true
   

    let newDateMain  = moment.utc(Date.now()).local();
    let newEndDate = newDateMain.format("YYYY-MM-DD");
    let newDate = moment.utc(this.form.value.startDate).local();
      let newDate2 = moment.utc(this.form.value.endDate).local();
      this.form.value.startDate = newDate.format("YYYY-MM-DD");
      this.form.value.endDate = newDate2.format("YYYY-MM-DD");
      const sdate = this.form.value.startDate == 'Invalid date' ? null :this.form.value.startDate;
      const edate = this.form.value.endDate == 'Invalid date' ? newEndDate : this.form.value.endDate;
      const cId = this.form.value.companyId == "" ? 0 : this.form.value.companyId;


    this._overviewUS.getAllFilteredUSData(sdate,edate,cId).subscribe(data => {
      let res_data = Object(data)["data"]
      this.overview_resumo = res_data

      let company_data = res_data.empresas
      this.empresas = company_data
       //Form Inicial Date
    let fsdate  = moment.utc(res_data.data_criacao_us).local();
    let dateinit = fsdate.format("YYYY-MM-DD");
    let ffdate  = moment.utc(Date.now()).local();
    let datef = ffdate.format("YYYY-MM-DD");
    this.formInitDate = dateinit;
    this.formFinalDate = datef;
    //End
    
    this.chartElement.nativeElement.innerHTML = '&nbsp;';
    this.chartElement.nativeElement.innerHTML = '<canvas id="myChart2" height="160px"></canvas>';
    this.TransactionsChart(res_data.transacoes_percent);

    })
    this.spinner = false;
  }

  TransactionsChart(data){
    const myChart2 = new Chart("myChart2", {
      type: 'pie',
      data: {
        labels: ['% Rejeitados', '% Pagos', '% Pendentes'],
        datasets: [{
          label: '# of Votes',
          data: data,
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
          text: '% TRANSAÇÕES'
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

  onSelectOption_empresa(item) {
    this.companyId = item.value;
  }

  getTransacoes(){
    this._overviewUS.getAllTransacoesUS().subscribe(data => {
      let res_data = Object(data)["data"]
    })
  }

}