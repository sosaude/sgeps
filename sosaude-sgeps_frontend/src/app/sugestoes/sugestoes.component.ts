import { Component, OnInit } from '@angular/core';
import { SugestoesService } from '../_services/sugestoes.service';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-sugestoes',
  templateUrl: './sugestoes.component.html',
  styleUrls: ['./sugestoes.component.scss']
})
export class SugestoesComponent implements OnInit {
  spinner: boolean = false;
  sugestoes: any[];

  constructor(private _sugestao:SugestoesService) { }

  ngOnInit() {
    this. getAll();
  }

  getAll() {
    this.spinner = true;
    this._sugestao.getAll().subscribe(data => {
      let res_data = Object(data)["data"]
      this.sugestoes = res_data.sugestoes
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  delete(item) {
    Swal.fire({
      title: 'Tem certeza?',
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
        this._sugestao.delete(item.id).subscribe(res => {
          this.getAll();
          this.spinner = false;
          Swal.fire({
            title: 'Submetido.',
            text: "Removida com sucesso.",
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
    })
  }

}
