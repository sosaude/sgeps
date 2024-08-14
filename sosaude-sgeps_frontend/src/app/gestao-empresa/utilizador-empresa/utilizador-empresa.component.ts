import { Component, OnInit } from '@angular/core';
import Swal from 'sweetalert2';
import { AuthenticationService } from '../../_services/authentication.service';
import { Router } from '@angular/router';
import { Validators, FormGroup, FormBuilder, FormArray } from '@angular/forms';
import { EmpresasService } from '../../_services/empresas.service';
import { Observable } from 'rxjs';
import { UtilizadorService } from '../../_services/utilizador.service';

@Component({
  selector: 'app-utilizador-empresa',
  templateUrl: './utilizador-empresa.component.html',
  styleUrls: ['./utilizador-empresa.component.scss']
})
export class UtilizadorEmpresaComponent implements OnInit {
  authValue: any;
  form: FormGroup;

  situacao_farmaceutico: any[] = [
    { nome: 'Activo', value: 1 }, { nome: 'Inativo', value: 0 }
  ]
  submitted: boolean = false;
  spinner: boolean;
  utilizadores: any[];
  empresas: any[];
  viewBtn: boolean = false;
  editBtn: boolean = false;
  submitBtn: boolean = true;
  searchText: any;
  perfil_roles: any[];
  utilizadorView = {
    id: '',
    nome: ''
  };
  perfil_permissoes: any[];
  selectedPermissoes: any[];
  value_exist: boolean;

  constructor(private authenticationService: AuthenticationService,
    private router: Router,
    private formBuilder: FormBuilder,
    private _ut: UtilizadorService) {
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
    this.getAll();
    this.getRoles();
  }


  initializeFormSubmit() {
    this.form = this.formBuilder.group({
      role_id: ['', Validators.required],
      nome: ['', Validators.required],
      contacto: [''],
      nacionalidade: ['', Validators.required],
      activo: ['', Validators.required],
      observacoes: [''],
      permissaos: [],
      id: [''],
    });
  }

  getAll() {
    this.spinner = true;
    this._ut.getAllUtilizadoresEmpresa().subscribe(data => {
      this.utilizadores = Object(data)["data"]
      this.utilizadores.sort((a, b) => {
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
    this._ut.getRolesUtilizadoresEmpresa().subscribe(data => {
      let res = Object(data)["data"];
      this.perfil_roles = res.roles;
      this.perfil_permissoes = res.permissaos;
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  submit() {
    // console.log(this.form.value);
    this.submitted = true;
    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._ut.registerUtilizadoresEmpresa(this.form.value).subscribe(data => {
      this.form.reset();
      this.spinner = false;
      document.getElementById('actualizarID').click();
      this.updateListAfterSUbmit(data)
    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
  }

  updateListAfterSUbmit(data: any) {
    this.utilizadores.push(data.data);
  }

  updateListAfterUpdate(id: any, data: any) {
    for (let index = 0; index < this.utilizadores.length; index++) {
      if (id == this.utilizadores[index].id) {
        this.utilizadores[index] = data.data;
      }
    }
  }

  updateSubmit() {
    this.submitted = true;
    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._ut.updateUtilizadoresEmpresa(this.form.value).subscribe(data => {
      this.form.reset();
      this.spinner = false;
      document.getElementById('actualizarID').click();
      this.updateListAfterUpdate(this.form.value.id, data);
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
        this._ut.deleteUtilizadoresEmpresa(this.form.value.id).subscribe(res => {
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

    let listId: any[] = [];
    for (let index = 0; index < row.permissaos.length; index++) {
      listId.push(row.permissaos[index].id);

    }
    this.selectedPermissoes = listId;

    this.form.patchValue({
      role_id: row.role_id,
      nome: row.nome,
      contacto: row.contacto,
      nacionalidade: row.nacionalidade,
      activo: row.activo,
      observacoes: row.observacoes,
      permissaos: listId,
      id: row.id,
    })
    // console.log(this.form.value);
    this.form.disable();
  }

  toUpdate() {
    this.editBtn = true;
    this.submitBtn = false;
    this.viewBtn = false;
    this.form.enable();
  }

  add() {
    this.viewBtn = false;
    this.submitBtn = true;
    this.editBtn = false;
    this.selectedPermissoes = [];
    this.form.enable();
    this.initializeFormSubmit();
  }

  onSelectOption(item) {
    this.selectedPermissoes = item.value;
  }

}