import { Component, OnInit } from '@angular/core';
import { Chart } from 'node_modules/chart.js';
import { OverviewService } from 'src/app/_services/overview.service';


@Component({
  selector: 'app-overview',
  templateUrl: './overview.component.html',
  styleUrls: ['./overview.component.scss']
})
export class OverviewComponent implements OnInit {

  overview: any[] = [];
  servicos: any[] = [];
  doencas_percent: any[] = [];
  descricao_servicos: any[] = [];
  spinner: boolean = false;
  descricao_services: boolean = false;

  constructor(private _overview: OverviewService) { }

  ngOnInit() {
    this.getAll();
    this.getServicos();
  }

  getAll(){
    this.spinner = true
    this._overview.getAllAdminOverview().subscribe(data => {
      let res_data = Object(data)["data"];
      this.overview = Object(data)["data"];
      this.spinner = false
      // console.log(this.overview)
    })
  }

  getServicos(){
    const myChart1 = new Chart("myChart1", {
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
          text: '% de Beneficiarios que usaram o PS'
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
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
          text: '% Sinistro (PIZZA)'
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
    const myChart5 = new Chart("myChart5", {
      type: 'pie',
      data: {
        labels: ['% Margem lucro líquido', '% Objectivo'],
        datasets: [{
          label: '# of Votes',
          data: [2, 9],
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
          text: '% Faixa etária'
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
    const myChart6 = new Chart("myChart6", {
      type: 'pie',
      data: {
        labels: ['% Margem lucro líquido', '% Objectivo'],
        datasets: [{
          label: '# of Votes',
          data: [2, 9],
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
          text: 'Doenças crónicas'
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
        }
      }
    });

    const myChart3 = new Chart("myChart3", {
      type: 'bar',
      data: {
        labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        datasets: [{
          label: 'Número de Processos mensais',
          data: [20, 62, 31, 21, 41, 21, 28, 7, 12, 19, 38, 17],
          backgroundColor: [
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
          ],
          borderColor: [
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
          ],
          borderWidth: 1
        }]
      },
      options: {
        // title: {
        //   display: true,
        //   text: 'Número de valores'
        // },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
    const myChart7 = new Chart("myChart7", {
      type: 'bar',
      data: {
        labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        datasets: [{
          label: 'Número de Processos mensais',
          data: [20, 62, 31, 21, 41, 21, 28, 7, 12, 19, 38, 17],
          backgroundColor: [
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
          ],
          borderColor: [
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
            '#FF5100',
            '#FFD8E1',
          ],
          borderWidth: 1
        }]
      },
      options: {
        // title: {
        //   display: true,
        //   text: 'Número de valores'
        // },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

  }

}
