import { Component, OnInit } from '@angular/core';
import { FormGroup, FormBuilder, Validators } from '@angular/forms';
import { UtilizadorService } from 'src/app/_services/utilizador.service';
import Swal from 'sweetalert2';
import { AuthenticationService } from 'src/app/_services/authentication.service';

@Component({
  selector: 'app-us-utilizadores',
  templateUrl: './us-utilizadores.component.html',
  styleUrls: ['./us-utilizadores.component.scss']
})
export class UsUtilizadoresComponent implements OnInit {
  form_utilizador: FormGroup;
  spinner: boolean = false;
  utilizadores: any[];
  perfil_roles: any[];
  perfil_permissoes: any[];
  selectedPermissoes: any[];
  model_grupo_temp = {
    nome: '',
    id: null
  }
  submitted: boolean = false;
  form: FormGroup;
  viewBtn: boolean = false;
  editBtn: boolean = false;
  submitBtn: boolean = true;
  searchText_utilizador: any;
  spinner_all: boolean = false;
  authValue: any;
  userRole: number;
  situacao_farmaceutico: any[] = [
    { nome: 'Activo', value: 1 }, { nome: 'Inativo', value: 0 }
  ]
  invalidTelefoneBoolean: boolean = false;
  constructor(private formBuilder: FormBuilder, private authenticationService: AuthenticationService,
    private _ut: UtilizadorService) {
    if (this.authenticationService.currentUserValue) {
      this.authValue = this.authenticationService.currentUserValue;
      this.userRole = this.authenticationService.currentUserValue.user.role.codigo;
    }
  }

  ngOnInit() {
    this.initializeGrupo();
    this.getAllUtilizadores();
    this.initializeFormSubmit();
    this.getRolesUtilizador();
    // console.log(this.form_utilizador.value);
  }

  initializeGrupo() {
    this.form = this.formBuilder.group({
      nome: ['', Validators.required],
      id: [''],
    });
  }

  initializeFormSubmit() {
    this.form_utilizador = this.formBuilder.group({
      role_id: ['', Validators.required],
      nome: ['', Validators.required],
      email: ['', Validators.required],
      contacto: ['', [Validators.required, Validators.pattern('[+]{0,1}[0-9]{8,}')]],
      nacionalidade: ['', Validators.required],
      activo: ['', Validators.required],
      observacoes: [''],
      permissaos: [],
      id: [''],
    });
  }

  getAllUtilizadores() {

    this.spinner_all = true;
    if (this.userRole == 6) { // UNIDADE SANITÁRIA
      this._ut.getAllUtilizadoresUs().subscribe(data => {
        this.utilizadores = Object(data)["data"]
        this.spinner_all = false;
      }, error => {
        // console.log(error)
        this.spinner_all = false;
      })
    } 
    else if (this.userRole == 2 || this.userRole == 3) { // FARMÁCIA
      this._ut.getAllUtilizadoresFarmacia().subscribe(data => {
        this.utilizadores = Object(data)["data"]
        this.spinner_all = false;
      }, error => {
        // console.log(error)
        this.spinner_all = false;
      })
    } 

  }

  getRolesUtilizador() {

    this.spinner = true;
    if (this.userRole == 6) { // UNIDADE SANITÁRIA
      this._ut.getRolesUtilizadoresUs().subscribe(data => {
        let res = Object(data)["data"];
        this.perfil_roles = res.roles;
        this.perfil_permissoes = res.permissaos;
        this.spinner = false;
      }, error => {
        // console.log(error)
        this.spinner = false;
      })
    } 
    else if (this.userRole == 2) { // FARMÁCIA
      this._ut.getRolesUtilizadoresFarmacia().subscribe(data => {
        let res = Object(data)["data"];
        this.perfil_roles = res.roles;
        this.perfil_permissoes = res.permissaos;
        this.spinner = false;
      }, error => {
        // console.log(error)
        this.spinner = false;
      })
    } 
   
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
    this.spinner = true;
    if (this.userRole == 6) { // UNIDADE SANITÁRIA
      this._ut.registerUtilizadoresUs(this.form_utilizador.value).subscribe(data => {
        // console.log("dados", data)
        this.form_utilizador.reset();
        this.spinner = false;
        document.getElementById('actualizarID_3').click();
        this.getAllUtilizadores()
      },
        error => {
          // console.log(error);
          this.spinner = false;
        })
    } 
    else if (this.userRole == 2) { // FARMÁCIA
      this._ut.registerUtilizadoresFarmacia(this.form_utilizador.value).subscribe(data => {
        this.form_utilizador.reset();
        this.spinner = false;
        document.getElementById('actualizarID_3').click();
        this.getAllUtilizadores()
      },
        error => {
          // console.log(error);
          this.spinner = false;
        })
    } 
  
  }


  updateSubmitUtilizador() {
    this.submitted = true;
    if (this.form_utilizador.get('contacto').valid) {
      this.invalidTelefoneBoolean = false;
    }

    if (this.form_utilizador.invalid) {
      return
    }
    this.spinner = true;

    if (this.userRole == 6) { // UNIDADE SANITÁRIA
      this._ut.updateUtilizadoresUs(this.form_utilizador.value).subscribe(data => {
        this.form_utilizador.reset();
        this.spinner = false;
        this.getAllUtilizadores()
        document.getElementById('actualizarID_3').click();
      },
        error => {
          // console.log(error);
          this.spinner = false;
        })
    } 
    else if (this.userRole == 2) { // FARMÁCIA
      this._ut.updateUtilizadoresFarmacia(this.form_utilizador.value).subscribe(data => {
        this.form_utilizador.reset();
        this.getAllUtilizadores()
        this.spinner = false;
        document.getElementById('actualizarID_3').click();
      },
        error => {
          // console.log(error);
          this.spinner = false;
        })
    } 
  
  }

  deleteUtilizador() {
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

        if (this.userRole == 6) { // UNIDADE SANITÁRIA
          this._ut.deleteUtilizadoresUs(this.form_utilizador.value.id).subscribe(res => {
         
            document.getElementById('actualizarID_3').click();
            this.getAllUtilizadores();
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
              // console.log(error);
  
            });
        } 
        else if (this.userRole == 2) { // FARMÁCIA
          this._ut.deleteUtilizadoresFarmacia(this.form_utilizador.value.id).subscribe(res => {
  
            document.getElementById('actualizarID_3').click();
            this.getAllUtilizadores();
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
              // console.log(error);
  
            });
        } 

      }
    })
  }

  viewCardUtilizador(row) {
    // console.log("row", row);
    this.viewBtn = true;
    this.editBtn = false;
    this.submitBtn = false;
    let listId: any[] = [];
    for (let index = 0; index < row.permissaos.length; index++) {
      listId.push(row.permissaos[index].id);
    }

    this.selectedPermissoes = listId;
    this.form_utilizador.patchValue({
      role_id: row.role_id,
      nome: row.nome,
      email: row.email,
      contacto: row.contacto,
      nacionalidade: row.nacionalidade,
      activo: row.activo,
      observacoes: row.observacoes,
      permissaos: listId,
      id: row.id,
    })
    // console.log("teste", this.selectedPermissoes);
    // console.log(this.form_utilizador.value);
    this.form_utilizador.disable();
    this.getRolesUtilizador();
  }

  toUpdateUtilizador() {
    this.editBtn = true;
    this.submitBtn = false;
    this.viewBtn = false;
    this.form_utilizador.enable();
  }

  addUtilizador() {
    this.viewBtn = false;
    this.submitBtn = true;
    this.editBtn = false;
    this.selectedPermissoes = [];
    this.form_utilizador.enable();
    this.initializeFormSubmit();
    this.getRolesUtilizador();
  }

  onSelectOption_utilizador(item) {
    this.selectedPermissoes = item.value;
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

}
