import { Component, OnInit } from '@angular/core';
import Swal from 'sweetalert2';
import { AuthenticationService } from '../_services/authentication.service';
import { Router } from '@angular/router';
import { Validators, FormGroup, FormBuilder, FormArray } from '@angular/forms';
import { EmpresasService } from '../_services/empresas.service';
import { Observable } from 'rxjs';

@Component({
  selector: 'app-empresas-utilizador',
  templateUrl: './empresas-utilizador.component.html',
  styleUrls: ['./empresas-utilizador.component.scss']
})
export class EmpresasUtilizadorComponent implements OnInit {

  authValue: any;
  form: FormGroup;

  situacao_farmaceutico: any[] = [
    { nome: 'Activo', value: 1 }, { nome: 'Inativo', value: 0 }
  ]
  submitted: boolean = false;
  spinner: boolean;
  utilizadores: any[];
  empresas: any[];
  viewBtn:  boolean = false;
  editBtn: boolean = false;
  submitBtn: boolean = true;
  searchText:any;
  perfil_roles: any[];
  utilizadorView = {
    id:'',
    nome:''
  };
  invalidTelefoneBoolean: boolean = false;
  selectedPermissoes: any[];
  perfil_permissoes: any[];

  constructor(private authenticationService: AuthenticationService,
    private router: Router,
    private formBuilder: FormBuilder,
    private _empresa: EmpresasService) {
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
    this.getAllempresas();
    this.getRoles();
  }

  initializeFormSubmit(){
    this.form = this.formBuilder.group({
      role_id: ['', Validators.required],
      nome: ['', Validators.required],
      email: ['', Validators.required],
      empresa_id: ['', [Validators.required]],
      contacto: ['', [Validators.required, Validators.pattern('[+]{0,1}[0-9]{8,}')]],
      nacionalidade:['', Validators.required],
      activo: ['', Validators.required],
      permissaos: [''],
      observacoes: [''],
      id: [''],
    });
  }
  
  getAll() {
    this.spinner = true;
    this._empresa.utilizadorgetAll().subscribe(data => {
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

  getAllempresas() {
    this._empresa.getAll().subscribe(data => {
      this.empresas = Object(data)["data"];
    }, error => {
      // console.log(error)
    })
  }

  getRoles() {
    this.spinner = true;
    this._empresa.utilizadorgetAllCategorias().subscribe(data => {
      let res = Object(data)["data"];
      this.perfil_roles = res.roles;
      this.perfil_permissoes = res.permissaos;
      this.spinner = false;
    }, error => {
      // console.log(error)
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
    this._empresa.utilizadorupdate(this.form.value).subscribe(data => {
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
        this._empresa.utilizadordelete(this.form.value.id).subscribe(res => {
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
    this.selectedPermissoes = [];
    row.permissaos.forEach((element, index) => {
      listId.push(row.permissaos[index].id);
    });
    this.selectedPermissoes = listId;
    this.form.patchValue(row);
    this.form.get('permissaos').setValue(listId);

    this.form.disable();
    this.utilizadorView.id = row.empresa_id;
    this.getEmpresaName(row.empresa_id)
  }

  toUpdate() {
    this.editBtn = true;
    this.submitBtn = false;
    this.viewBtn = false;
    this.form.enable();
  }

  getEmpresaName(empresa_id:number):Observable<string> {

    for (let index = 0; index < this.empresas.length; index++) {
      if (this.empresas[index].id == empresa_id) {
        return this.empresas[index].nome;
      }
    }
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

  onSelectOption(item) {
    this.selectedPermissoes = item.value;
  }
  
}