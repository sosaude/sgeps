import { Component, OnInit, Output, EventEmitter, Input } from '@angular/core';
import Swal from 'sweetalert2';
import { AuthenticationService } from '../_services/authentication.service';
import { Router } from '@angular/router';
import { Validators, FormGroup, FormBuilder, FormArray } from '@angular/forms';
import { MatSlideToggleChange } from '@angular/material';
import { FarmaciasService } from '../_services/farmacias.service';
import { FarmaceuticosService } from '../_services/farmaceuticos.service';
import * as moment from 'moment';

@Component({
  selector: 'app-farmacia',
  templateUrl: './farmacia.component.html',
  styleUrls: ['./farmacia.component.scss']
})
export class FarmaciaComponent implements OnInit {
  @Output() change: EventEmitter<MatSlideToggleChange>
  authValue: any;
  form: FormGroup;
  // contactos:FormArray;

  situacao_farmacia: any[] = [
    { nome: 'Activa', value: 1 }, { nome: 'Inativa', value: 0 }
  ]
  submitted: boolean = false;
  spinner: boolean;
  farmacias: any[];
  viewBtn: boolean = false;
  editBtn: boolean = false;
  editBtnData: boolean = true;
  submitBtn: boolean = true;
  aberturaBool: boolean = false;
  form_farmaceutico: FormGroup;
  farmaciaView = {
    id: '',
    nome: ''
  };
  categoria_farmaceutico: any[] = [
    { nome: 'Técnico de farmácia' }, { nome: 'Bacharel em farmácia' }, { nome: 'Farmacêutico' }
  ]

  situacao_farmaceutico: any[] = [
    { nome: 'Activo', value: 1 }, { nome: 'Inativo', value: 0 }
  ]
  horarios: any[] = [
    '--:--', '00:00', '00:30', '01:00', '01:30', '02:00', '02:30', '03:00', '03:30',
    '04:00', '04:30', '05:00', '05:30', '06:00', '06:30', '07:00', '07:30', '08:00',
    '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00',
    '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
    '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00', '21:30',
    '22:00', '22:30', '23:00', '23:30'
  ]
  dias: any[] = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31]
  meses: any[] = [
    { nome: 'Janeiro', value: 1 }, { nome: 'Fevereiro', value: 2 },
    { nome: 'Março', value: 3 }, { nome: 'Abril', value: 4 },
    { nome: 'Maio', value: 5 }, { nome: 'Junho', value: 6 },
    { nome: 'Julho', value: 7 }, { nome: 'Agosto', value: 8 },
    { nome: 'Setembro', value: 9 }, { nome: 'Outubro', value: 10 },
    { nome: 'Novembro', value: 11 }, { nome: 'Dezembro', value: 12 }
  ]

  currentYear = new Date().getFullYear();
  currentDay = new Date().getDate();
  currentMonth = new Date().getMonth() + 1;
  anos: any[] = [];
  days: any[] = [];

  farmaceuticos: any[];
  searchText: any;
  perfil_roles: any[];
  selectedFarmaciaRow: any;
  spinner_1: boolean = false;
  modelData = {
    dia: null,
    mes: null,
    ano: null
  }
  selectdiaMesAnoBoolean: boolean = false;
  invalidTelefoneBoolean: boolean = false;
  selectedPermissoes: any[];
  perfil_permissoes: any[];

  @Input() max: any;
  tomorrow = new Date();

  constructor(private authenticationService: AuthenticationService,
    private router: Router,
    private formBuilder: FormBuilder,
    private _farmacia: FarmaciasService, private _farmaceutico: FarmaceuticosService) {
    if (this.authenticationService.currentUserValue) {
      // // console.log(this.authenticationService.currentUserValue);
      this.tomorrow.setDate(this.tomorrow.getDate());


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
    this.getRoles();


    for (let index = 0; index < 80; index++) {
      this.anos.push(this.currentYear)
      this.currentYear = this.currentYear - 1;
    }

  }

  initializeFormSubmit() {
    this.form = this.formBuilder.group({
      nome: ['', Validators.required],
      endereco: ['', [Validators.required]],
      horario_funcionamento: this.formBuilder.array([
        this.formBuilder.group({ dia: ['Domingo'], estado: [false], abertura: [''], enceramento: [''] }),
        this.formBuilder.group({ dia: ['Segunda-feira'], estado: [false], abertura: [''], enceramento: ['']  }),
        this.formBuilder.group({ dia: ['Terça-feira'], estado: [false], abertura: [''], enceramento: ['']  }),
        this.formBuilder.group({ dia: ['Quarta-feira'], estado: [false], abertura: [''], enceramento: ['']  }),
        this.formBuilder.group({ dia: ['Quinta-feira'], estado: [false], abertura: [''], enceramento: ['']  }),
        this.formBuilder.group({ dia: ['Sexta-feira'], estado: [false], abertura: [''], enceramento: [''] }),
        this.formBuilder.group({ dia: ['Sábado'], estado: [false], abertura: [''], enceramento: [''] }),
        this.formBuilder.group({ dia: ['Feriados'], estado: [false], abertura: [''], enceramento: [''] } )
      ], [Validators.required]),
      activa: ['', Validators.required],
      contactos: this.formBuilder.array([this.formBuilder.group({ contacto: ['', Validators.pattern('[+]{0,1}[0-9]{8,}')] })], Validators.required),
      latitude: ['', Validators.required],
      longitude: ['', Validators.required],
      numero_alvara: ['', Validators.required],
      data_alvara_emissao: [''],
      observacoes: [''],
      created_at: [''],
      updated_at: [''],
      deleted_at: [''],
      id: [''],
    });
  }

  initializeFormSubmitFarmaceutico() {
    this.form_farmaceutico = this.formBuilder.group({
      numero_caderneta: ['', Validators.required],
      role_id: ['', Validators.required],
      nome: ['', Validators.required],
      email: ['', Validators.required],
      farmacia_id: ['', [Validators.required]],
      contacto: ['', [Validators.required, Validators.pattern('[+]{0,1}[0-9]{8,}')]],
      categoria_profissional: ['', Validators.required],
      nacionalidade: ['', Validators.required],
      activo: ['', Validators.required],
      permissaos: [''],
      observacoes: [''],
      created_at: [''],
      updated_at: [''],
      deleted_at: [''],
      id: [''],
    });
    
    
  }

  getAll() {
    this.spinner = true;
    this._farmacia.getAll().subscribe(data => {
      this.farmacias = Object(data)["data"];
      this.farmacias.sort(function (a, b) {
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
    this._farmacia.getRoles().subscribe(data => {
      let res = Object(data)["data"]
      this.perfil_roles = res.roles;
      this.perfil_permissoes = res.permissaos;
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  dateBinding() {
    this.selectdiaMesAnoBoolean = false;
  }

  submit() {
    let ano = new Date().getFullYear()
    if( this.modelData.mes > this.currentMonth && this.modelData.ano == ano ){
      this.selectdiaMesAnoBoolean = true;
      return;
    }

    this.submitted = true;

    if (!this.modelData.dia ) {
      this.selectdiaMesAnoBoolean = true;
      return;
    }
    else if (!this.modelData.mes) {
      this.selectdiaMesAnoBoolean = true;
      return;
    }
    else if (!this.modelData.ano) {
      this.selectdiaMesAnoBoolean = true;
      return;
    }
    let stringDate = `${this.modelData.ano}-${this.modelData.mes}-${this.modelData.dia}`;
    this.form.value.data_alvara_emissao = stringDate;

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
    this._farmacia.register(this.form.value).subscribe(data => {
      this.form.reset();
      this.spinner = false;
      this.modelData.ano = null;
      this.modelData.dia = null;
      this.modelData.mes = null;
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

  submitFarmaceutico() {
    this.submitted = true;
    // console.log(this.form_farmaceutico.value);

    if (this.form_farmaceutico.get('contacto').valid) {
      this.invalidTelefoneBoolean = false;
    }

    if (this.form_farmaceutico.invalid) {
      return
    }

    this.spinner = true;
    this._farmaceutico.register(this.form_farmaceutico.value).subscribe(data => {
      this.form_farmaceutico.reset();
      this.spinner = false;
      this.invalidTelefoneBoolean = false;
      document.getElementById('actualizarID_farmacia').click();
      document.getElementById('actualizarID_2').click();
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

  updateSubmit() {
    let ano = new Date().getFullYear()
    if( this.modelData.mes > this.currentMonth && this.modelData.ano == ano ){
      this.selectdiaMesAnoBoolean = true;
      return;
    }

    this.submitted = true;

    if (!this.modelData.dia) {
      this.selectdiaMesAnoBoolean = true;
      return;
    }
    else if (!this.modelData.mes) {
      this.selectdiaMesAnoBoolean = true;
      return;
    }
    else if (!this.modelData.ano) {
      this.selectdiaMesAnoBoolean = true;
      return;
    }
    let stringDate = `${this.modelData.ano}-${this.modelData.mes}-${this.modelData.dia}`;
    this.form.value.data_alvara_emissao = stringDate;
    // console.log(this.form.value);

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
    this._farmacia.update(this.form.value).subscribe(data => {
      this.form.reset();
      this.spinner = false;
      this.modelData.ano = null;
      this.modelData.dia = null;
      this.modelData.mes = null;
      this.invalidTelefoneBoolean = false;
      document.getElementById('actualizarID').click();
      document.getElementById('actualizarID_2').click();
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

  updateSubmitFarmaceutico() {
    this.submitted = true;
    // console.log(this.form_farmaceutico.value);

    if (this.form_farmaceutico.get('contacto').valid) {
      this.invalidTelefoneBoolean = false;
    }

    if (this.form_farmaceutico.invalid) {
      return
    }
    this.spinner = true;
    this._farmaceutico.update(this.form_farmaceutico.value).subscribe(data => {
      this.form_farmaceutico.reset();
      this.spinner = false;
      document.getElementById('actualizarID_farmacia').click();
      document.getElementById('actualizarID_2').click();
      this.getAllFarmaceuticos(this.selectedFarmaciaRow);
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

  delete() {
    console.log(this.form.value.id)
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
        this._farmacia.delete(this.form.value.id).subscribe(res => {
          document.getElementById('actualizarID_2').click();
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
            // console.log(error);

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
        this.spinner_1 = true;
        this._farmaceutico.delete(id).subscribe(res => {
          // document.getElementById('actualizarID_farmacia').click();
          this.getAllFarmaceuticos(this.selectedFarmaciaRow);
          this.spinner_1 = false;
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
            this.spinner_1 = false;

          });
      }
    })
  }

  viewCard(row) {
    this.viewBtn = true;
    this.editBtn = false;
    this.editBtnData = false;
    this.submitBtn = false;
    let arrayValues: any[] = [];
    for (let index = 0; index < row.contactos.length; index++) {
      arrayValues.push({ contacto: row.contactos[index] });
    }

    row.contactos = arrayValues;
    this.form.patchValue({
      nome: row.nome,
      email: row.email,
      endereco: row.endereco,
      horario_funcionamento: row.horario_funcionamento,
      activa: row.activa,
      contactos: arrayValues,
      latitude: row.latitude,
      longitude: row.longitude,
      numero_alvara: row.numero_alvara,
      data_alvara_emissao: row.data_alvara_emissao,
      // data_alvara_emissao: new Date(row.data_alvara_emissao).toISOString().substring(0, 10),
      observacoes: row.observacoes,
      created_at: row.created_at,
      updated_at: row.updated_at,
      deleted_at: row.deleted_at,
      id: row.id
    });

    let check = moment(row.data_alvara_emissao, 'YYYY/MM/DD');
    this.modelData.dia = +check.format('D');
    this.modelData.mes = +check.format('M');
    this.modelData.ano = +check.format('YYYY');

    this.form.disable();
    this.getAllFarmaceuticos(row.id);
    this.farmaciaView.id = row.id;
    this.farmaciaView.nome = row.nome;
    this.selectedFarmaciaRow = row.id;
  }

  add() {
    this.viewBtn = false;
    this.submitBtn = true;
    this.editBtn = false;
    this.editBtnData = true;
    this.form.enable();
    this.initializeFormSubmit();
    this.modelData.dia = null;
    this.modelData.mes = null;
    this.modelData.ano = null;
  }

  addFarmaceutico(row) {
    // console.log(row);
    this.farmaciaView.id = row.id;
    this.farmaciaView.nome = row.nome;
    this.viewBtn = false;
    this.submitBtn = true;
    this.editBtn = false;
    this.editBtnData = true;
    this.initializeFormSubmitFarmaceutico();
    this.form_farmaceutico.enable();
    this.form_farmaceutico.get('farmacia_id').setValue(this.farmaciaView.id);
  }

  viewCardFarmaceutico(row) {
    // console.log(row);
    
    let listId: any[] = [];
    this.selectedPermissoes = [];
    row.permissaos.forEach((element, index) => {
      listId.push(row.permissaos[index].id);
    });
    this.selectedPermissoes = listId;
    
    this.form_farmaceutico.patchValue({
      id: row.id,
      nome: row.nome,
      email: row.email,
      farmacia_id: row.farmacia_id,
      contacto: row.contacto,
      numero_caderneta: row.numero_caderneta,
      activo: row.activo,
      role_id: row.role_id,
      categoria_profissional: row.categoria_profissional,
      nacionalidade: row.nacionalidade,
      observacoes: row.observacoes,
      permissaos: listId,
      created_at: row.created_at,
      updated_at: row.updated_at,
      deleted_at: row.deleted_at
    })
    this.viewBtn = true;
    this.editBtn = false;
    this.editBtnData = false;
    this.submitBtn = false;
    // console.log(this.form_farmaceutico.value);
    this.form_farmaceutico.disable();
  }

  getAllFarmaceuticos(id: number) {
    this.spinner_1 = true;
    this._farmacia.getAllFarmaceuticosByFarmaciaId(id).subscribe(data => {
      this.farmaceuticos = Object(data)["data"]
      this.farmaceuticos.sort(function (a, b) {
        return a.nome.localeCompare(b.nome);
      });
      this.spinner_1 = false;
    }, error => {
      // console.log(error)
      this.spinner_1 = false;
    })
  }

  toUpdate() {
    this.editBtn = true;
    this.editBtnData = true;
    this.submitBtn = false;
    this.viewBtn = false;
    this.form.enable();
  }

  toUpdate_farmaceutico() {
    this.editBtn = true;
    this.submitBtn = false;
    this.viewBtn = false;
    this.form_farmaceutico.enable();
  }

  onChange(ob: MatSlideToggleChange) {
    if (ob.checked) {
      this.aberturaBool = true;
    } else {
      this.aberturaBool = false;
    }   
  }


  get arrayHorarios() {
    return this.form.get('horario_funcionamento') as FormArray;
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

  dateformat(data: string | number | Date) {
    let date = new Date(data);
    let dia = date.getDate();
    let mes = date.getMonth() + 1;
    let ano = date.getFullYear();
    // // console.log("::::::::::::::::::: - " + ano + "-" + mes + "-" + dia)
    return "" + ano + "-" + mes + "-" + dia
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
