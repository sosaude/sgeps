import { Component, OnInit } from '@angular/core';
import Swal from 'sweetalert2';
import { AuthenticationService } from '../_services/authentication.service';
import { Router } from '@angular/router';
import { Validators, FormGroup, FormBuilder, FormArray } from '@angular/forms';
import { FarmaceuticosService } from '../_services/farmaceuticos.service';
import { FarmaciasService } from '../_services/farmacias.service';
import { Observable } from 'rxjs';

@Component({
  selector: 'app-farmaceuticos',
  templateUrl: './farmaceuticos.component.html',
  styleUrls: ['./farmaceuticos.component.scss']
})
export class FarmaceuticosComponent implements OnInit {

  authValue: any;
  form: FormGroup;
  categoria_farmaceutico: any[] = [
    { nome: 'Técnico de farmácia'}, { nome: 'Bacharel em farmácia'},{ nome: 'Farmacêutico'}
  ]
  situacao_farmaceutico: any[] = [
    { nome: 'Activo', value: 1 }, { nome: 'Inativo', value: 0 }
  ]
  submitted: boolean = false;
  spinner: boolean;
  farmaceuticos: any[];
  farmacias: any[];
  viewBtn:  boolean = false;
  editBtn: boolean = false;
  submitBtn: boolean = true;
  searchText:any;
  perfil_roles: any[];
  invalidTelefoneBoolean: boolean = false;
  selectedPermissoes: any[];
  perfil_permissoes: any[];

  constructor(private authenticationService: AuthenticationService,
    private router: Router,
    private formBuilder: FormBuilder,
    private _farmaceutico: FarmaceuticosService, private _farmacia: FarmaciasService) {
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
    this.getAllFarmacias();
  }


  initializeFormSubmit(){
    this.form = this.formBuilder.group({
      numero_caderneta: ['', Validators.required],
      nome: ['', Validators.required],
      email: ['', Validators.required],
      role_id: ['', Validators.required],
      farmacia_id: ['', [Validators.required]],
      contacto: ['', Validators.pattern('[+]{0,1}[0-9]{8,}')],
      categoria_profissional: ['', Validators.required],
      nacionalidade:['', Validators.required],
      activo: ['', Validators.required],
      permissaos: [''],
      observacoes: [''],
      deleted_at: [''],
      created_at: [''],
      updated_at: [''],
      id: [''],
    });
  }
  
  getAll() {
    this.spinner = true;
    this._farmaceutico.getAll().subscribe(data => {
      this.farmaceuticos = Object(data)["data"]
      this.farmaceuticos.sort(function(a, b) {
        return a.nome.localeCompare(b.nome);
     });
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  getAllFarmacias() {
    this.spinner = true;
    this._farmaceutico.getAllFarmacias().subscribe(data => {
      let res = Object(data)["data"];
      this.farmacias = res.farmacias;
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
    // console.log(this.form.value);
    if (this.form.get('contacto').valid) {
      this.invalidTelefoneBoolean = false;
    }
    
    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._farmaceutico.register(this.form.value).subscribe(data => {
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
    // console.log(this.form.value);
    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._farmaceutico.update(this.form.value).subscribe(data => {
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
        this._farmaceutico.delete(this.form.value.id).subscribe(res => {
          document.getElementById('actualizarID').click();
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

  getEmpresaName(farmacia_id:number):Observable<string> {
    for (let index = 0; index < this.farmacias.length; index++) {
      if (this.farmacias[index].id == farmacia_id) {
        return this.farmacias[index].nome;
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