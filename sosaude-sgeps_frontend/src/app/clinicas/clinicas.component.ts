import { Component, OnInit, Output, EventEmitter } from '@angular/core';
import Swal from 'sweetalert2';
import { AuthenticationService } from '../_services/authentication.service';
import { Router } from '@angular/router';
import { Validators, FormGroup, FormBuilder, FormArray } from '@angular/forms';
import { MatSlideToggleChange } from '@angular/material';
import { ClinicasService } from '../_services/clinicas.service';

@Component({
  selector: 'app-clinicas',
  templateUrl: './clinicas.component.html',
  styleUrls: ['./clinicas.component.scss']
})
export class ClinicasComponent implements OnInit {

  @Output() change: EventEmitter<MatSlideToggleChange>
  authValue: any;
  form: FormGroup;
  utilizadorView = {
    id:'',
    nome:''
  };
  submitted: boolean = false;
  spinner: boolean;
  clinicas: any[];
  viewBtn: boolean = false;
  editBtn: boolean = false;
  submitBtn: boolean = true;
  form_utilizador: FormGroup;
  situacao_utilizador: any[] = [
    { nome: 'Activo', value: 1 }, { nome: 'Inativo', value: 0 }
  ]
  utilizadores: any[];
  searchText: any;
  perfil_roles: any[];
  spinner_1: boolean = false;
  selectedClinicaRow: any;
  spinner_2: boolean = false;
  perfil_roles_unidade_sanitaria: any[];
  invalidTelefoneBoolean: boolean = false;
  selectedPermissoes: any[];
  perfil_permissoes: any[];

  constructor(private authenticationService: AuthenticationService,
    private router: Router,
    private formBuilder: FormBuilder,
    private _clinica: ClinicasService) {
    if (this.authenticationService.currentUserValue) {

      this.authValue = this.authenticationService.currentUserValue;
      if (this.authenticationService.currentUserValue.user.role.codigo == 1) {
        // this.router.navigate(['/clinicas']);
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
    this.initializeFormsubmitUtilizador();
    this.getAll();
    this.getRoles();
    this.getRolesUnidadeSanitaria();
  }

  initializeFormSubmit() {
    this.form = this.formBuilder.group({
      nome: ['', Validators.required],
      endereco: ['', [Validators.required]],
      email: ['', [Validators.required, Validators.email]],
      categoria_unidade_sanitaria_id: [''],
      nuit: ['', Validators.required],
      contactos: this.formBuilder.array([this.formBuilder.group({ contacto: ['', Validators.pattern('[+]{0,1}[0-9]{8,}')] })], Validators.required),
      latitude: ['', Validators.required],
      longitude: ['', Validators.required],
      id: [''],
      deleted_at: [''],
      created_at: ['']
    });
  }

  initializeFormsubmitUtilizador() {
    this.form_utilizador = this.formBuilder.group({
      role_id: ['', Validators.required],
      role: [''],
      nome: [, Validators.required],
      email: [, Validators.required],
      unidade_sanitaria_id: ['', Validators.required],
      unidadeSanitaria: [''],
      contacto: ['', [Validators.required, Validators.pattern('[+]{0,1}[0-9]{8,}')]],
      nacionalidade:['', Validators.required],
      activo: ['',  Validators.required],
      permissaos: [''],
      observacoes: [''],
      id: [''],
    });
  }

  getAll() {
    this.spinner = true;
    this._clinica.getAll().subscribe(data => {
      this.clinicas = Object(data)["data"];
      this.clinicas.sort((a, b) => {
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
    this._clinica.utilizadorgetAllCategorias().subscribe(data => {
      let res = Object(data)["data"]
      this.perfil_roles = res.roles;
      this.perfil_permissoes = res.permissaos;
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  getRolesUnidadeSanitaria() {
    this.spinner = true;
    this._clinica.getRoles().subscribe(data => {      
      this.perfil_roles_unidade_sanitaria = Object(data)["data"]
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  submit() {
    this.submitted = true;

    if (this.invalidTelefoneBoolean) {
      return;
    }

    if (this.form.invalid) {
      return
    }
    let texts = this.form.value.contactos.map(function (el) {
      return el.contacto;
    });
    this.form.value.contactos = texts;
    this.spinner = true;
    this._clinica.register(this.form.value).subscribe(data => {
      this.form.reset();
      this.spinner = false;
      this.invalidTelefoneBoolean = false;
      document.getElementById('actualizarID').click();
      this.getAll()
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

  submitUtilizador() {
    // console.log(this.form_utilizador.value);
    
    this.submitted = true;
  
    if (this.form_utilizador.get('contacto').valid) {
      this.invalidTelefoneBoolean = false;
    }

    if (this.form_utilizador.invalid) {
      return
    }
    this.spinner_1 = true;
    this._clinica.utilizadorregister(this.form_utilizador.value).subscribe(data => {
      this.spinner_1 = false;
      document.getElementById('actualizarID_utilizador').click();
      this.form_utilizador.reset();
      this.getAll()
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
        this.spinner_1 = false;
      })
  }

  updateSubmit() {
    this.submitted = true;
    if (this.invalidTelefoneBoolean) {
      return;
    }
    if (this.form.invalid) {
      return
    }
    let texts = this.form.value.contactos.map(function (el) {
      return el.contacto;
    });
    this.form.value.contactos = texts;
    this.spinner = true;
    this._clinica.update(this.form.value).subscribe(data => {
      this.form.reset();
      this.spinner = false;
      document.getElementById('actualizarID_2').click();
      this.getAll()
      Swal.fire({
        title: 'Submetido.',
        text: "Actualizada com sucesso.",
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

  updatesubmitUtilizador() {
    this.submitted = true;
    this.form_utilizador.get('unidade_sanitaria_id').setValue(this.utilizadorView.id);
    if (this.form_utilizador.get('contacto').valid) {
      this.invalidTelefoneBoolean = false;
    }

    
    if (this.form_utilizador.invalid) {
      return
    }

    this.spinner_1 = true;
    this._clinica.utilizadorupdate(this.form_utilizador.value).subscribe(data => {
      this.form_utilizador.reset();
      this.spinner_1 = false;
      document.getElementById('actualizarID_utilizador').click();
      this.getAllUtilizadoresByEmpresaID(this.selectedClinicaRow);
    },
      error => {
        // console.log(error);
        this.spinner_1 = false;
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
        this._clinica.delete(this.form.value.id).subscribe(res => {
          document.getElementById('actualizarID_2').click();
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

  deleteFarmaceutico(id) {
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
        this.spinner_2 = true;
        this._clinica.utilizadordelete(id).subscribe(res => {
          // document.getElementById('actualizarID_farmacia').click();
          this.getAllUtilizadoresByEmpresaID(this.selectedClinicaRow);
          this.spinner_2 = false;
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
            this.spinner_2 = false;

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
      endereco: row.endereco,
      email: row.email,
      nuit: row.nuit,
      contactos: arrayValues,
      latitude:row.latitude,
      longitude:row.longitude,
      categoria_unidade_sanitaria_id: row.categoria_unidade_sanitaria_id,
      deleted_at: row.deleted_at,
    });
    this.form.disable();
    this.getAllUtilizadoresByEmpresaID(row.id);
    this.utilizadorView.id = row.id;
    this.utilizadorView.nome = row.nome;
    this.selectedClinicaRow = row.id;
  }

  add() {
    this.viewBtn = false;
    this.submitBtn = true;
    this.editBtn = false;
    this.form.enable();
    this.initializeFormSubmit();
  }

  addUtilizador(row) {
    // console.log(row);
    
    this.utilizadorView.id = row.id;
    this.utilizadorView.nome = row.nome;
    this.viewBtn = false;
    this.submitBtn = true;
    this.editBtn = false;
    this.initializeFormsubmitUtilizador();
    this.form_utilizador.enable();
    this.form_utilizador.get('unidade_sanitaria_id').setValue(this.utilizadorView.id);        
  }

  viewCardFarmaceutico(row) {    
    this.viewBtn = true;
    this.editBtn = false;
    this.submitBtn = false;
    let listId: any[] = [];
    this.selectedPermissoes = [];
    row.permissaos.forEach((element, index) => {
      listId.push(row.permissaos[index].id);
    });
    this.selectedPermissoes = listId;

    this.form_utilizador.patchValue(row);
    this.form_utilizador.get('permissaos').setValue(listId);
    this.form_utilizador.disable();
  }

  getAllUtilizadoresByEmpresaID(id: number) {
    this.spinner_2 = true;
    this._clinica.getUtilizadoresByClinicaID(id).subscribe(data => {
      this.utilizadores = Object(data)["data"]
      this.spinner_2 = false;
    }, error => {
      // console.log(error)
      this.spinner_2 = false;
    })
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
    this.form_utilizador.enable();
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
