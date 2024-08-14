import { Component, OnInit } from '@angular/core';
import Swal from 'sweetalert2';
import { AuthenticationService } from '../_services/authentication.service';
import { Router } from '@angular/router';
import { Validators, FormGroup, FormBuilder, FormArray } from '@angular/forms';
import { FarmaceuticosService } from '../_services/farmaceuticos.service';
import { FarmaciasService } from '../_services/farmacias.service';
import { EmpresasService } from '../_services/empresas.service';
import { Observable } from 'rxjs';

@Component({
  selector: 'app-empresas',
  templateUrl: './empresas.component.html',
  styleUrls: ['./empresas.component.scss']
})
export class EmpresasComponent implements OnInit {

  authValue: any;
  form: FormGroup;
  submitted: boolean = false;
  spinner: boolean;
  empresas: any[];
  viewBtn: boolean = false;
  editBtn: boolean = false;
  submitBtn: boolean = true;
  searchText: any;
  searchText_marca: any;
  categorias: any[];
  utilizadorView = {
    id: '',
    nome: ''
  };
  form_farmaceutico: FormGroup;
  perfil_roles: any[];
  utilizadores: any[];
  empresa_id: string;
  auxiliarArray: any[] = [];
  situacao_farmaceutico: any[] = [
    { nome: 'Activo', value: 1 }, { nome: 'Inativo', value: 0 }
  ]
  spinner_1: boolean = false;
  selectedEmpresaRow: any;
  invalidNuitBoolean: boolean = false;
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
    this.initializeFormSubmitFarmaceutico();
    this.getAll();
    this.getAllUtilizadores();
    this.getAllCategorias();
    this.getRoles();
  }


  initializeFormSubmit() {
    this.form = this.formBuilder.group({
      id: [''],
      nome: ['', Validators.required],
      categoria_empresa_id: ['', Validators.required],
      endereco: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      // nuit: ['', [Validators.required, Validators.maxLength(9), Validators.pattern('[0-9]{9}')]],
      nuit: ['', Validators.required],
      contactos: this.formBuilder.array([this.formBuilder.group({ contacto: ['', Validators.pattern('[+]{0,1}[0-9]{8,}')] })], Validators.required),
      delegacao: [''],
    });
  }

  initializeFormSubmitFarmaceutico() {
    this.form_farmaceutico = this.formBuilder.group({
      role_id: ['', Validators.required],
      nome: ['', Validators.required],
      email: ['', Validators.required],
      empresa_id: ['', [Validators.required]],
      contacto: ['', [Validators.required, Validators.pattern('[+]{0,1}[0-9]{8,}')]],
      nacionalidade: ['', Validators.required],
      activo: ['', Validators.required],
      permissaos: [''],
      observacoes: [''],
      id: [''],
    });
  }

  onNuitChange(event) {
    const regex = new RegExp('^[0-9]{1,9}$');
    const valid = regex.test(event);
    if (!valid) {
      this.invalidNuitBoolean = true;
    } else {
      this.invalidNuitBoolean = false;
    }
  }

  getAllUtilizadores() {
    this.spinner_1 = true;
    this._empresa.utilizadorgetAll().subscribe(data => {
      this.utilizadores = Object(data)["data"]
      this.spinner_1 = false;

    }, error => {
      // console.log(error)
      this.spinner_1 = false;
    })
  }

  get arrayContactos() {
    return <FormArray>this.form.get('contactos');
  }

  addContacto() {
    this.arrayContactos.push(this.formBuilder.group({ contacto: '' }));
  }

  deleteContacto(index) {
    this.arrayContactos.removeAt(index);
  }

  getAll() {
    this.spinner = true;
    this._empresa.getAll().subscribe(data => {
      this.empresas = Object(data)["data"]
      this.empresas.sort((a, b) => {
        return a.nome.localeCompare(b.nome);
      });
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  getAllCategorias() {
    this.spinner = true;
    this._empresa.getAllCategorias().subscribe(data => {
      this.categorias = Object(data)["data"];
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  getRoles() {
    this.spinner = true;
    this._empresa.utilizadorgetAllCategorias().subscribe(data => {
      let res = Object(data)["data"]
      this.perfil_roles = res.roles;
      this.perfil_permissoes = res.permissaos;
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  submit() {
   
    this.submitted = true;
  
    let texts = this.form.value.contactos.map(function (el) {
      return el.contacto;
    });
    
    this.form.value.contactos = texts;
    if (this.invalidTelefoneBoolean) {
      return;
    }
    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._empresa.register(this.form.value).subscribe(data => {
      this.form.reset();
      // this.spinner_1 = false;
      document.getElementById('actualizarID').click();
      this.getAll();
      Swal.fire({
        title: 'Submetido.',
        text: "Criado com sucesso.",
        type: 'success',
        showCancelButton: false,
        confirmButtonColor: "#f15726",
        confirmButtonText: 'Ok'
      })
      this.getAll()
    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
  }

  submitFarmaceutico() {
    this.submitted = true;

    if (this.form_farmaceutico.get('contacto').valid) {
      this.invalidTelefoneBoolean = false;
    }

    if (this.form_farmaceutico.invalid) {
      return
    }
    this.spinner = true;
    this._empresa.utilizadorregister(this.form_farmaceutico.value).subscribe(data => {
      this.form_farmaceutico.reset();
      this.spinner = false;
      
      document.getElementById('actualizarID_utilizador').click();
      this.getAllUtilizadores()
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
        this.spinner = false;
      })
  }

  updateSubmit() {
    this.submitted = true;
    let texts = this.form.value.contactos.map(function (el) {
      return el.contacto;
    });
    this.form.value.contactos = texts;
    if (this.invalidTelefoneBoolean) {
      return;
    }

    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._empresa.update(this.form.value).subscribe(data => {
      Swal.fire({
        title: 'Submetido.',
        text: "Actualizado com sucesso.",
        type: 'success',
        showCancelButton: false,
        confirmButtonColor: "#f15726",
        confirmButtonText: 'Ok'
      })
      document.getElementById('actualizarID_edit').click();
      this.form.reset();
      this.getAll()
    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
  }

  updateSubmitFarmaceutico() {
    this.submitted = true;
    // console.log(this.form_farmaceutico.value);

    // if(this.form.get('contactos').valid){
    //   this.invalidTelefoneBoolean = false;
    // }

    if (this.form_farmaceutico.get('contacto').valid) {
      this.invalidTelefoneBoolean = false;
    }

    if (this.form_farmaceutico.invalid) {
      return
    }
    this.spinner = true;
    this._empresa.utilizadorupdate(this.form_farmaceutico.value).subscribe(data => {
      this.spinner = false;
      document.getElementById('actualizarID_utilizador').click();
      this.form_farmaceutico.reset();
      this.invalidTelefoneBoolean = false;
      Swal.fire({
        title: 'Submetido.',
        text: "Actualizado com sucesso.",
        type: 'success',
        showCancelButton: false,
        confirmButtonColor: "#f15726",
        confirmButtonText: 'Ok'
      })
      this.getAllUtilizadores();
      this.getAllUtilizadoresByEmpresaID(this.selectedEmpresaRow);
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
        this._empresa.delete(this.form.value.id).subscribe(res => {
          document.getElementById('actualizarID_edit').click();
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

  deleteFarmaceutico(id: number) {
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
        this.spinner_1 = true;
        this._empresa.utilizadordelete(id).subscribe(res => {
          this.getAllUtilizadoresByEmpresaID(this.selectedEmpresaRow);
          this.spinner_1 = false;
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
    let arrayValues: any[] = [];
    for (let index = 0; index < row.contactos.length; index++) {
      arrayValues.push({ contacto: row.contactos[index] });
    }
    this.form.patchValue({
      id: row.id,
      nome: row.nome,
      categoria_empresa_id: row.categoria_empresa_id,
      endereco: row.endereco,
      email: row.email,
      nuit: row.nuit,
      contactos: arrayValues,
      delegacao: row.delegacao,
    });
    this.form.disable();
    this.getAllUtilizadoresByEmpresaID(row.id);
    this.utilizadorView.id = row.id;
    this.utilizadorView.nome = row.nome;
    this.selectedEmpresaRow = row.id;
  }

  viewCardUtilizador(row) {
    this.viewBtn = true;
    this.editBtn = false;
    this.submitBtn = false;
    let listId: any[] = [];
    this.selectedPermissoes = [];
    row.permissaos.forEach((element, index) => {
      listId.push(row.permissaos[index].id);
    });
    this.selectedPermissoes = listId;

    this.form_farmaceutico.patchValue(row);
    this.form_farmaceutico.get('permissaos').setValue(listId);
    this.form_farmaceutico.disable();
  }

  add() {
    this.viewBtn = false;
    this.submitBtn = true;
    this.editBtn = false;
    this.form.enable();
    this.initializeFormSubmit();
  }

  addUtilizador(row) {
    this.utilizadorView.id = row.id;
    this.utilizadorView.nome = row.nome;
    this.viewBtn = false;
    this.submitBtn = true;
    this.editBtn = false;
    this.form_farmaceutico.enable();
    this.initializeFormSubmitFarmaceutico();
    this.form_farmaceutico.get('empresa_id').setValue(this.utilizadorView.id);
    // console.log(this.form_farmaceutico.value);

  }

  toUpdate() {
    this.editBtn = true;
    this.submitBtn = false;
    this.viewBtn = false;
    this.form.enable();

  }
  toUpdateUser() {
    this.editBtn = true;
    this.submitBtn = false;
    this.viewBtn = false;
    this.form_farmaceutico.enable();
  }

  getAllUtilizadoresByEmpresaID(id: number) {
    this.spinner_1 = true;
    this._empresa.getUtilizadoresByEmpresaID(id).subscribe(data => {
      this.auxiliarArray = Object(data)["data"]
      this.auxiliarArray.sort((a, b) => {
        return a.nome.localeCompare(b.nome);
      });
      this.spinner_1 = false;
    }, error => {
      // console.log(error)
      this.spinner_1 = false;
    })
  }

  getEmpresaName(empresa_id: number): Observable<string> {
    for (let index = 0; index < this.empresas.length; index++) {
      if (this.empresas[index].id == empresa_id) {
        return this.empresas[index].nome;
      }
    }
  }

  onTelefoneChange(event) {
    const regex = new RegExp('^[+]{0,1}[0-9]{2,}$');
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