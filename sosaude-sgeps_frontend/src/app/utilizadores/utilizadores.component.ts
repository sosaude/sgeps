import { Component, OnInit } from '@angular/core';
import Swal from 'sweetalert2';
import { AuthenticationService } from '../_services/authentication.service';
import { Router } from '@angular/router';
import { Validators, FormGroup, FormBuilder, FormArray } from '@angular/forms';
import { UtilizadorService } from '../_services/utilizador.service';

@Component({
  selector: 'app-utilizadores',
  templateUrl: './utilizadores.component.html',
  styleUrls: ['./utilizadores.component.scss']
})
export class UtilizadoresComponent implements OnInit {
  authValue: any;
  form: FormGroup;
  categoria_farmaceutico: any[] = [
    { nome: 'Técnico de farmácia' }, { nome: 'Bacharel em farmácia' }, { nome: 'Farmacêutico' }
  ]
  situacao_utilizador: any[] = [
    { nome: 'Activo', value: 1 }, { nome: 'Inativo', value: 0 }
  ]
  invalidTelefoneBoolean: boolean = false;
  submitted: boolean = false;
  spinner: boolean;
  farmaceuticos: any[];
  farmacias: any[];
  viewBtn: boolean = false;
  editBtn: boolean = false;
  submitBtn: boolean = true;
  searchText: any;
  perfil_roles: any[];
  constructor(private authenticationService: AuthenticationService,
    private router: Router,
    private formBuilder: FormBuilder,
    private _utilizador: UtilizadorService) {
    if (this.authenticationService.currentUserValue) {
      this.authValue = this.authenticationService.currentUserValue;
      if (this.authenticationService.currentUserValue.user.role.codigo == 1) {
        // this.router.navigate(['/farmacias']);
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
    this.getAll();
    this.getRoles();
  }


  initializeFormSubmit() {
    this.form = this.formBuilder.group({
      nome: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      contacto: ['',[Validators.pattern('[+]{0,1}[0-9]{8,}'), Validators.required]],
      activo: ['', Validators.required],
      role_id: ['', Validators.required],
      deleted_at: [''],
      created_at: [''],
      updated_at: [''],
      id: [''],
    });
  }

  getAll() {
    this.spinner = true;
    this._utilizador.getAll().subscribe(data => {
      this.farmaceuticos = Object(data)["data"]
      this.farmaceuticos.sort((a, b) => {
        return a.nome.localeCompare(b.nome);
      });
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  getRoles() {
    this.spinner = true;
    this._utilizador.getRoles().subscribe(data => {
      let res = Object(data)["data"];
      this.perfil_roles = res.roles;
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  submit() {
    this.submitted = true;
    // console.log(this.form.value);
    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._utilizador.register(this.form.value).subscribe(data => {
      this.form.reset();
      this.spinner = false;
      document.getElementById('actualizarID').click();
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

  updateSubmit() {
    this.submitted = true;
    // console.log(this.form.value);
    if (this.form.get('contacto').valid) {
      this.invalidTelefoneBoolean = false;
      }
    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._utilizador.update(this.form.value).subscribe(data => {
      this.form.reset();
      this.spinner = false;
      document.getElementById('actualizarID').click();
      this.getAll()
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
        this.spinner = false;
      })
  }

  delete(item) {
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
      // this.spinner = true;
      if (result.value) {
        this.spinner = true;
        this._utilizador.delete(item.id).subscribe(res => {
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
    // console.log(row);
    this.viewBtn = true;
    this.editBtn = false;
    this.submitBtn = false;
    this.form.patchValue(row);
    // console.log(this.form.value);
    this.form.disable();
  }

  add() {
    this.viewBtn = false;
    this.submitBtn = true;
    this.editBtn = false;
    this.form.enable();
    this.initializeFormSubmit();
  }

  toUpdate() {
    this.editBtn = true;
    this.submitBtn = false;
    this.viewBtn = false;
    this.form.enable();
  }

  onTelefoneChange(event) {
    const regex = new RegExp('^[+]{0,1}[0-9]{8,}$');
    const valid = regex.test(event);
    if (!valid) {
    this.invalidTelefoneBoolean = true;
    } else {
    this.invalidTelefoneBoolean = false;
    }
    
   }

  mudarEstado(item) {
    let estado;
    if (item.activo == 1) {
      estado = 0;
    } else if(item.activo == 0){
      estado = 1;
    }
    let model = {
      nome: item.nome,
      contacto: item.contacto,
      email: item.email,
      role_id: item.role_id,
      activo: estado,
      id:item.id,
      deleted_at:item.deleted_at,
      created_at:item.created_at,
      updated_at:item.updated_at,
    }
    this.spinner = true;
    this._utilizador.update(model).subscribe(data => {
      this.spinner = false;
      // document.getElementById('actualizarID').click();
      this.getAll()
    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
  }
}
