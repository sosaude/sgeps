import { Component, OnInit } from '@angular/core';
import Swal from 'sweetalert2';
import { UsGestaoStockService } from 'src/app/_services/us-gestao-stock.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';

@Component({
  selector: 'app-gestao-servicos',
  templateUrl: './gestao-servicos.component.html',
  styleUrls: ['./gestao-servicos.component.scss']
})
export class GestaoServicosComponent implements OnInit {

  spinner: boolean = false;
  submitted: boolean = false;
  searchText: any;
  form: FormGroup;

  model_servico_view = {
    id: null,
    preco: null,
    iva: null,
    preco_iva: null,
    servico_id: null,
    servico_nome: ""

  }

  adicionar_servico_bool: boolean = false;
  spinner_all: boolean = false;
  p: number = 1;
  servicos_unidade_sanitaria: any[];
  servicos_unidade_sanitaria_admin: any[] = [];
  constructor(
    private router: Router,
    private formBuilder: FormBuilder,
    private _stock: UsGestaoStockService) {

  }

  ngOnInit() {
    this.getStockFarmacia();
    this.form = this.formBuilder.group({
      preco: ['', Validators.required],
      iva: [0, [Validators.required]],
      preco_iva: [],
      servico_id: [],
      id: [],
    });
  }

  getAllServicoskAdmin() {
    this.spinner_all = true;
    this._stock.get_servicos_globais().subscribe(data => {
      // console.log("Servicos Globais",data);
      let res_data = Object(data)["data"]
      this.servicos_unidade_sanitaria_admin = res_data.servicos;
      // // console.log("copiando", this.servicos_unidade_sanitaria_admin)
    //   admin_servicos.forEach((element, index) => {
    //     let element_main = element;
    // let status =  this.servicos_unidade_sanitaria.some(element => element['servico_id'] === element_main.servico_id)
          // if (!status) {
          //   // console.log(status, element_main);
            
          //   this.servicos_unidade_sanitaria_admin.push({
          //     servico_id: element_main.servico_id,
          //     servico_nome: element_main.servico_nome
          //   });
          // }
      // });
      this.spinner_all = false;
    }, error => {
      // console.log(error)
      this.spinner_all = false;
    })
  }


  getStockFarmacia() {
    this.spinner_all = true;
    this._stock.get_servicos_us().subscribe(data => {
      // console.log(data);
      let res_data = Object(data)["data"]
      this.servicos_unidade_sanitaria = res_data.servicos_unidade_sanitaria;
      this.spinner_all = false;
    }, error => {
      // console.log(error)
      this.spinner_all = false;
    })
  }

  submit() {
    this.submitted = true;
    this.form.get('preco_iva').setValue(this.model_servico_view.preco_iva)
    // console.log(this.form.value);
    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._stock.post_servicos_us(this.form.value).subscribe(data => {
      // console.log(data);
      this.servicos_unidade_sanitaria.push({ data });


      this.form.reset();
      this.spinner = false;
      document.getElementById('actualizarID').click();
      // this.getAll()
    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
  }

  submitUpdate() {
    this.submitted = true;
    this.form.get('preco_iva').setValue(this.model_servico_view.preco_iva)
    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._stock.update_servicos_us(this.form.value).subscribe(data => {
      let res_data = Object(data)["data"];
      this.servicos_unidade_sanitaria.forEach((element, index) => {
        if (element.id == res_data.id) {
          this.servicos_unidade_sanitaria[index] = res_data;
        }
      });
      this.spinner = false;
      document.getElementById('actualizarID_update').click();
    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
  }


  add(item) {
    this.model_servico_view = item;
    this.form.reset();
    this.form.get('servico_id').setValue(item.servico_id)
    this.form.get('iva').setValue(item.iva)
    this.calculoIVA();
  }
  removeItem(item) {
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
        this.spinner_all = true;
        this._stock.delete(item.id).subscribe(res => {
          this.getStockFarmacia();
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
            this.spinner_all = false;
            // console.log(error);

          });
      }
    })
  }

  edit(item) {
    this.model_servico_view = item;
    this.form.reset();

    this.form.get('servico_id').setValue(item.servico_id)
    this.form.get('iva').setValue(item.iva)
    this.form.get('preco').setValue(item.preco)
    this.form.get('id').setValue(item.id)
    this.calculoIVA();
  }

  switchViewTable() {
    this.adicionar_servico_bool = true;
    this.getAllServicoskAdmin()
  }

  calculoIVA() {
    this.model_servico_view.preco_iva = this.form.value.preco + this.form.value.preco * (this.form.value.iva / 100);
    this.model_servico_view.preco_iva  = this.model_servico_view.preco_iva.toFixed(2);
  }

  voltar() {
    this.adicionar_servico_bool = false;
    this.searchText = "";
  }
}
