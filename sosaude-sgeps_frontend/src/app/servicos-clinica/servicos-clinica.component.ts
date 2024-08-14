import { Component, OnInit } from '@angular/core';
import Swal from 'sweetalert2';
import { AuthenticationService } from '../_services/authentication.service';
import { Router } from '@angular/router';
import { Validators, FormGroup, FormBuilder, FormArray } from '@angular/forms';
import { EmpresasService } from '../_services/empresas.service';
import { Observable } from 'rxjs';
import { ServicosService } from '../_services/servicos.service';

@Component({
  selector: 'app-servicos-clinica',
  templateUrl: './servicos-clinica.component.html',
  styleUrls: ['./servicos-clinica.component.scss']
})
export class ServicosClinicaComponent implements OnInit {

  authValue: any;
  form: FormGroup;

  situacao_farmaceutico: any[] = [
    { nome: 'Activo', value: 1 }, { nome: 'Inativo', value: 0 }
  ]
  submitted: boolean = false;
  spinner: boolean;
  servicos: any[];
  empresas: any[];
  viewBtn:  boolean = false;
  editBtn: boolean = false;
  submitBtn: boolean = true;
  searchText:any;
  perfil_roles: any[];
  utilizadorView = {
    id:'',
    nome:'',

  };
  servicos_create: any;
  group_modal_option: number;
  spinner_grupo: boolean;
  categorias: any[];
  form_categoria: FormGroup;

  p = 1; 
  constructor(private authenticationService: AuthenticationService,
    private router: Router,
    private formBuilder: FormBuilder,
    private _servico: ServicosService) {
    if (this.authenticationService.currentUserValue) {
      this.authValue = this.authenticationService.currentUserValue;
      if (this.authenticationService.currentUserValue.user.role.codigo == 1) {
        // this.router.navigate(['/empresas']);
      }

      else if (this.authenticationService.currentUserValue.user.role.codigo == 2) {
        // this.router.navigate(['/']);
      }

      else if (this.authenticationService.currentUserValue.user.role.codigo == 3) {
        // this.router.navigate(['/']);
      }
      else if (this.authenticationService.currentUserValue.user.role.codigo == 4) {
        // this.router.navigate(['/']);
      }

      else if (this.authenticationService.currentUserValue.user.role.codigo == 5) {
        // this.router.navigate(['/']);
      }
    }
  }

  ngOnInit() {
    this.initializeFormSubmit();
    this.initializeFormCategoria();
    this.getAll();
    this.getCreate();
  
  }


  initializeFormSubmit(){
    this.form = this.formBuilder.group({
      nome: ['', Validators.required],
      id: [''],
      categoria_servico_id: ['', Validators.required],
    });
  }

  initializeFormCategoria() {
    this.form_categoria = this.formBuilder.group({
      codigo: [''],
      id: [''],
      nome: ['', Validators.required],
    });
  }
  
  add() {
    this.viewBtn = false;
    this.submitBtn = true;
    this.editBtn = false;
    this.form.enable();
    this.initializeFormSubmit();
  }

  getAll() {
    this.spinner = true;
    this._servico.getAll().subscribe(data => {
      this.servicos = Object(data)["data"]
      this.servicos.sort((a, b) => {
        return a.nome.localeCompare(b.nome);
      });
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  getCreate() {
    this.spinner = true;
    this._servico.getCreate().subscribe(data => {
      let res_data = Object(data)["data"]
      this.servicos_create = res_data.categorias_serivicos;      
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  submit() {
    this.submitted = true;
    if (this.form.invalid) {
      return
    }
 
    this.spinner = true;
    this._servico.register(this.form.value).subscribe(data => {
      this.form.reset();
      this.spinner = false;
      document.getElementById('actualizarID').click();
      this.getAll()
    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
  }
  
  updateSubmit() {
    this.submitted = true;
    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._servico.update(this.form.value).subscribe(data => {
      this.form.reset();
      this.spinner = false;
      document.getElementById('actualizarID').click();
      this.getAll()
    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
  }

  delete() {
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
        this.spinner = true;
        this._servico.delete(this.form.value.id).subscribe(res => {
          document.getElementById('actualizarID').click();
          this.getAll();
          this.spinner = false;
          Swal.fire({
            title: 'Submetido.',
            text: "Removido com sucesso.",
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

  viewCard(row) {
    this.viewBtn = true;
    this.editBtn = false;
    this.submitBtn = false;
    this.form.setValue({
      nome: row.nome,
      id: row.id,
      categoria_servico_id: row.categoria_servico.id
    });
    this.form.disable();
    this.utilizadorView.id = row.empresa_id;
  }

  toUpdate() {
    this.editBtn = true;
    this.submitBtn = false;
    this.viewBtn = false;
    this.form.enable();
  }

  modalGrupo(op: number) {
    this.group_modal_option = op;
    switch (op) {
      case 1:
        this.spinner_grupo = true;
        this._servico.getAllCategorias().subscribe(data => {
          let res = Object(data)["data"]
          this.categorias = res.categorias_servicos
          this.categorias.sort(function (a, b) {
            return a.nome.localeCompare(b.nome);
          });
          this.spinner_grupo = false;
        }, error => {
          // console.log(error)
          this.spinner_grupo = false;
        })
        break;
      case 2:
        this.form_categoria.reset();
        break;

        case 3:
          this.submitted = true;
          if (this.form_categoria.invalid) {
            return
          }
          this.spinner_grupo = true;
          this._servico.registerCategoria(this.form_categoria.value).subscribe(data => {
            this.spinner_grupo = false;
            this.modalGrupo(1);
            this.form_categoria.reset();
            Swal.fire({
              title: 'Submetido.',
              text: "Criado com sucesso.",
              type: 'success',
              showCancelButton: false,
              confirmButtonColor: "#f15726",
              confirmButtonText: 'Ok'
            })
          },
            error => {
              // console.log(error);
              this.spinner_grupo = false;
            })
          break;
  
        case 4:
          this.submitted = true;
          // console.log(this.form_categoria.value);
          if (this.form_categoria.invalid) {
            return
          }
  
          this.spinner_grupo = true;
          this._servico.updateCategoria(this.form_categoria.value).subscribe(data => {
            this.spinner_grupo = false;
            this.modalGrupo(1);
            this.form_categoria.reset();
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
              this.spinner_grupo = false;
            })
          break;
  
        case 5:
          this.group_modal_option = 1;
          break;

      default:
        break;
    }
  }

  editCardGrupo(item) {
    this.form_categoria.patchValue(item);
    this.group_modal_option = 3;
  }

  deleteGrupo(row) {
    Swal.fire({
      title: 'Tem certeza que deseja remover?',
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
        this.spinner_grupo = true;
        this._servico.deleteCategoria(row.id).subscribe(res => {
          this.spinner_grupo = false;
          this.modalGrupo(1);
          Swal.fire({
            title: 'Submetido.',
            text: "Removido com sucesso.",
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
