import { Component, OnInit } from '@angular/core';
import { UsGestaoStockService } from 'src/app/_services/us-gestao-stock.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-gestao-stock',
  templateUrl: './gestao-stock.component.html',
  styleUrls: ['./gestao-stock.component.scss']
})
export class GestaoStockComponent implements OnInit {
  spinner: boolean = false;
  marcas_medicamentos_admin: any[] = [];
  submitted: boolean = false;
  searchText: any;
  form: FormGroup;

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

  marcas_medicamentos_farmacia: any[];
  adicionar_medicamento_bool: boolean = false;
  spinner_all: boolean = false;
  p: number = 1;
  constructor(
    private router: Router,
    private formBuilder: FormBuilder,
    private _stock: UsGestaoStockService) {

  }

  ngOnInit() {
    this.getStockFarmacia();
    this.form = this.formBuilder.group({
      preco: ['', Validators.required],
      quantidade_disponivel: ['', [Validators.required]],
      iva: [0, [Validators.required]],
      preco_iva: [],
      medicamento_id: [],
      marca_medicamento_id: [],
      id: [],

    });
  }

  getAllStockAdmin() {
    this.spinner_all = true;
    this._stock.get_marcas_medicamentos().subscribe(data => {
      let res_data = Object(data)["data"]

      // let admin_medicamentos = res_data.marcas_medicamentos;
      // admin_medicamentos.forEach((element, index) => {
      //   let element_main = element;
      //   let status = this.marcas_medicamentos_farmacia.some(element => element['marca_medicamento_id'] === element_main.marca_medicamento_id)
      //   console.log(status)
      //   // if (status) {
      //   //   this.marcas_medicamentos_admin.push({
      //   //     marca: element_main.marca,
      //   //     marca_codigo: element_main.marca_codigo,
      //   //     marca_medicamento_id: element_main.marca_medicamento_id,
      //   //     marca_pais_origem: element_main.marca_pais_origem,
      //   //     medicamento_codigo: element_main.medicamento_codigo,
      //   //     medicamento_dosagem: element_main.medicamento_dosagem,
      //   //     medicamento_forma: element_main.medicamento_forma,
      //   //     medicamento_forma_id: element_main.medicamento_forma_id,
      //   //     medicamento_id: element_main.medicamento_id,
      //   //     medicamento_nome_generico: element_main.medicamento_nome_generico,
      //   //     medicamento_nome_generico_id: element_main.medicamento_nome_generico_id
      //   //    });
      //   // }
      // });
      this.marcas_medicamentos_admin = res_data.marcas_medicamentos;
      // console.log(this.marcas_medicamentos_admin)

      this.marcas_medicamentos_farmacia.forEach((element) => {
        // console.log(element)
        this.marcas_medicamentos_admin.forEach((item,index) => {
          if(element.marca_medicamento_id === item.marca_medicamento_id){
            this.marcas_medicamentos_admin.splice(index,1)
          }

        })
      });
      // this.marcas_medicamentos_admin.forEach((element, index) => {
      //   let element_main = element;
      //   this.marcas_medicamentos_farmacia.forEach(element => {
      //     if ((element_main.marca_medicamento_id == element.marca_medicamento_id) && (element_main.medicamento_id == element.medicamento_id)) {
      //       this.marcas_medicamentos_admin.splice(index, 1);
      //     }
      //   });
      // });

      // console.log(this.marcas_medicamentos_admin)

      this.spinner_all = false;
    }, error => {
      // console.log(error)
      this.spinner_all = false;
    })
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
        this._stock.deleteStock(item.id).subscribe(res => {
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


  getStockFarmacia() {
    this.spinner_all = true;
    this._stock.getStock().subscribe(data => {
      let res_data = Object(data)["data"]
      this.marcas_medicamentos_farmacia = res_data.marcas_medicamentos;
      this.spinner_all = false;
    }, error => {
      // console.log(error)
      this.spinner_all = false;
    })
  }

  submit() {
    this.submitted = true;
    this.form.get('preco_iva').setValue(this.model_medicamento_view.preco_iva)
    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._stock.postStock(this.form.value).subscribe(data => {
      let res_data = Object(data)["data"];
      this.marcas_medicamentos_farmacia.push({
        id: res_data.id,
        iva: res_data.iva,
        marca: res_data.marca,
        marca_codigo: res_data.marca_codigo,
        marca_medicamento_id: res_data.marca_medicamento_id,
        marca_pais_origem: res_data.marca_pais_origem,
        medicamento_codigo: res_data.medicamento_codigo,
        medicamento_dosagem: res_data.medicamento_dosagem,
        medicamento_forma: res_data.medicamento_forma,
        medicamento_forma_id: res_data.medicamento_forma_id,
        medicamento_id: res_data.medicamento_id,
        medicamento_nome_generico: res_data.medicamento_nome_generico,
        medicamento_nome_generico_id: res_data.medicamento_nome_generico_id,
        preco: res_data.preco,
        preco_iva: res_data.preco_iva,
        quantidade_disponivel: res_data.quantidade_disponivel
      });
      this.marcas_medicamentos_admin.forEach((element, index) => {
        let element_main = element;
        this.marcas_medicamentos_farmacia.forEach(element => {
          if ((element_main.marca_medicamento_id == element.marca_medicamento_id) && (element_main.medicamento_id == element.medicamento_id)) {
            this.marcas_medicamentos_admin.splice(index, 1);
          }
        });
      });

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
    this.form.get('preco_iva').setValue(this.model_medicamento_view.preco_iva)
    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._stock.editStock(this.form.value).subscribe(data => {
      let res_data = Object(data)["data"];
      this.marcas_medicamentos_farmacia.forEach((element, index) => {
        if (element.id == res_data.id) {
          this.marcas_medicamentos_farmacia[index] = res_data;
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
    this.model_medicamento_view = item;
    this.form.reset();

    this.form.get('marca_medicamento_id').setValue(item.marca_medicamento_id)
    this.form.get('medicamento_id').setValue(item.medicamento_id)
    this.form.get('iva').setValue(item.iva)
    this.calculoIVA();
  }

  edit(item) {
    this.model_medicamento_view = item;
    this.form.reset();
    this.form.get('marca_medicamento_id').setValue(item.marca_medicamento_id)
    this.form.get('medicamento_id').setValue(item.medicamento_id)
    this.form.get('iva').setValue(item.iva)
    this.form.get('quantidade_disponivel').setValue(item.quantidade_disponivel)
    this.form.get('preco').setValue(item.preco)
    this.form.get('id').setValue(item.id)
    this.calculoIVA();
  }

  switchViewTable() {
    this.adicionar_medicamento_bool = true;
    this.getAllStockAdmin()
    console.log(this.marcas_medicamentos_admin)
    console.log(this.marcas_medicamentos_farmacia)
  }

  calculoIVA() {
    this.model_medicamento_view.preco_iva = this.form.value.preco + this.form.value.preco * (this.form.value.iva / 100);
    this.model_medicamento_view.preco_iva = this.model_medicamento_view.preco_iva.toFixed(2);
  }

  voltar() {
    this.adicionar_medicamento_bool = false;
    this.searchText = "";
  }


}
