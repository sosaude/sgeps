import { Component, OnInit } from '@angular/core';
import { BeneficiariosService } from 'src/app/_services/beneficiarios.service';
import { AuthenticationService } from 'src/app/_services/authentication.service';
import { Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators, FormArray, FormControl } from '@angular/forms';
import Swal from 'sweetalert2';
import { MatSlideToggleChange } from '@angular/material';
import * as moment from 'moment';
import { GruposService } from 'src/app/_services/grupos.service';
import * as XLSX from 'xlsx';
import { ExcelService } from 'src/app/_services/excel.service';


@Component({
  selector: 'app-beneficiarios',
  templateUrl: './beneficiarios.component.html',
  styleUrls: ['./beneficiarios.component.scss']
})

export class BeneficiariosComponent implements OnInit {
  //doencas = new FormControl();
  //doencasLista: any[] = ['A', 'B', 'C'];
  doencas: any[] = [];
  doencas_cronicas: any[] = [];
  selctedDoencas: any[] = [];
  data: any[] = [];
  selctedDoencas1: any[] = [];
  searchText: any;
  spinner: boolean = false;
  beneficiarios: any[];
  beneficiarios_excel: any[];
  form: FormGroup;
  formDependente: FormGroup;
  perfil_grupos: any[];
  viewBtn: boolean = false;
  editBtn: boolean = false;
  submitBtn: boolean = true;
  editBtnData: boolean = true;
  modelData = {
    dia: null,
    mes: null,
    ano: null
  }

  modelData_dependente = {
    dia: null,
    mes: null,
    ano: null
  }

  modelDependente = {
    id: null,
    utilizador_activo: true,
    nome: '',
    numero_identificacao: '',
    email: '',
    endereco: '',
    bairro: '',
    telefone: '',
    genero: '',
    data_nascimento: '',
    parantesco: '',
    doenca_cronica: false,
    doenca_cronica_nome: []
  }


  submitted: boolean = false;
  selectdiaMesAnoBoolean: boolean = false;
  selectdiaMesAnoBoolean_dependente: boolean = false;
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
  anos: any[] = [];
  temDependentes: boolean = false;
  temDoencaCronica: boolean = false;
  p = 1;
  ArrayView: any[];
  situacao_utilizador: any[] = [
    { nome: 'Activo', value: true }, { nome: 'Inativo', value: false }
  ]
  parentesco_dependentes: any[] = ['Pai', 'Mãe', 'Esposa(o)', 'Filha(o)', 'irmã(o)', 'Outro']
  beneficiario_aposentado: boolean = false;
  temDoencaCronica_dependente: boolean = false;
  editDependente: boolean = false;
  element_dependente_position: any;
  grupos: any[];
  invalidTelefoneBoolean: boolean = false;
  total_numero_beneficiarios: number = 0;
  selectedCardView: number = 0;
  final_beneficiarios_array: any[];


  files: Set<File>;
  ficheiro: File = null;
  fileName = 'Beneficiarios.xlsx';


  constructor(private authenticationService: AuthenticationService,
    private excelService: ExcelService,
    private router: Router,
    private formBuilder: FormBuilder,
    private _bn: BeneficiariosService,
    private _grupo: GruposService) {

  }

  ngOnInit() {
    // // console.log(this.getAllDoencas());
    console.log(this.final_beneficiarios_array)
    this.getAll();
    this.initializeFormSubmit();
    this.getCreate();
    this.getQuantidadeBeneficiarios();
    this.getAllBene();

    for (let index = 0; index < 120; index++) {
      this.anos.push(this.currentYear)
      this.currentYear = this.currentYear - 1;
    }

  };
  getAllBene() {
    this.spinner = true;
    this._bn.getAllBeneExcel().subscribe(data => {
      // console.log(data);
      this.beneficiarios_excel = Object(data)["data"];
      // this.final_beneficiarios_array = Object(data)["data"];

      this.beneficiarios_excel.sort((a, b) => {
        return a.nome.localeCompare(b.nome);
      });
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }
  exportAsXLSX(): void {
    this.excelService.exportAsExcelFile(this.beneficiarios_excel, 'Beneficiarios');
  }

  downloadExcel(): void {

    /* table id is passed over here */
    let element = document.getElementById('table-beneficiarios');
    const ws: XLSX.WorkSheet = XLSX.utils.table_to_sheet(element);

    /* generate workbook and add the worksheet */
    const wb: XLSX.WorkBook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Beneficiarios');

    /* save to file */
    XLSX.writeFile(wb, this.fileName);

  }

  submit() {
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
    this.form.get('data_nascimento').patchValue(stringDate);

    if (this.temDoencaCronica) {

      // let texts = this.selctedDoencas
      let texts = this.form.value.doenca_cronica_nome.map(function (el) {
        return el.nome;
      });
      this.form.value.doenca_cronica_nome = texts;
    } else {
      this.form.value.doenca_cronica_nome = [];
    }

    this.form.value.tem_dependentes = this.temDependentes;
    if (this.temDependentes) {

    } else {
      this.form.value.dependentes = [];
    }

    // console.log(this.form.value);

    if (this.form.invalid) {
      return
    }

    this.spinner = true;
    // console.log(this.form.value)
    this._bn.register(this.form.value).subscribe(data => {
      this.form.reset();
      this.spinner = false;
      this.modelData.ano = null;
      this.modelData.dia = null;
      this.modelData.mes = null;
      document.getElementById('actualizarID').click();
      this.getAll()
      this.getQuantidadeBeneficiarios();
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

  dateBinding() {
    this.selectdiaMesAnoBoolean = false;
  }

  dateBinding_dependente() {
    this.selectdiaMesAnoBoolean_dependente = false;
  }

  onChange_doenca(ob: MatSlideToggleChange) {
    if (ob.checked) {
      this.temDoencaCronica = true;
    } else {
      this.temDoencaCronica = false;
      this.arrayDoencas.reset();
    }
  }

  onChange_doenca_dependente(ob: MatSlideToggleChange) {
    if (ob.checked) {
      this.modelDependente.doenca_cronica = true;
      // this.temDoencaCronica = true;
    } else {
      this.modelDependente.doenca_cronica = false;
    }
  }

  onChange_dependentes(ob: MatSlideToggleChange) {
    if (ob.checked) {
      this.temDependentes = true;
    } else {
      this.temDependentes = false;
    }
  }

  initializeFormSubmit() {
    this.form = this.formBuilder.group({
      utilizador_activo: [true, Validators.required],
      nome: ['', Validators.required],
      email: ['', Validators.required],
      numero_beneficiario: ['', Validators.required],
      numero_identificacao: ['', Validators.required],
      endereco: ['', Validators.required],
      bairro: ['', Validators.required],
      telefone: ['', [Validators.required, Validators.pattern('[+]{0,1}[0-9]{8,}')]],
      genero: ['', Validators.required],
      data_nascimento: ['', Validators.required],
      ocupacao: ['', Validators.required],
      aposentado: [false],
      doenca_cronica: [false],
      doenca_cronica_nome: [],
      grupo_beneficiario_id: ['', Validators.required],
      tem_dependentes: [false],
      dependentes: this.formBuilder.array([]),
      id: ['']
    });

    this.formDependente = this.formBuilder.group({
      id: null,
      utilizador_activo: true,
      nome: '',
      email: '',
      endereco: '',
      bairro: '',
      telefone: '',
      genero: '',
      data_nascimento: '',
      parantesco: '',
      doenca_cronica: false,
      doenca_cronica_nome: []
    })
  };

  buildDoencas() {
    const values = this.doencas.map(v => new FormControl(false));
    return this.formBuilder.array(values);
  }


  getAll() {
    this.spinner = true;
    this._bn.getAll().subscribe(data => {
      // console.log(data);
      this.beneficiarios = Object(data)["data"];
      this.final_beneficiarios_array = Object(data)["data"];

      this.beneficiarios.sort((a, b) => {
        return a.nome.localeCompare(b.nome);
      });
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  getQuantidadeBeneficiarios() {
    this._grupo.getAll().subscribe(data => {
      this.grupos = Object(data)["data"]
      this.total_numero_beneficiarios = 0;
      this.grupos.forEach(element => {
        this.total_numero_beneficiarios = element.numero_beneficiarios + this.total_numero_beneficiarios;
      });
    }, error => {
      // console.log(error)
    })
  }

  changeViewCard(grupo_id) {
    this.selectedCardView = grupo_id;
    this.beneficiarios = [];
    if (grupo_id == 0) {
      this.beneficiarios = this.final_beneficiarios_array;
    } else {
      this.final_beneficiarios_array.forEach((element, index) => {
        if (element.grupoBeneficiario.id == grupo_id) {
          this.beneficiarios.push(element)
        }
      });
    }
  }


  getCreate() {
    this.spinner = true;
    this._bn.getAllCreate().subscribe(data => {
      let res = Object(data)["data"]
      // console.log(res)
      this.perfil_grupos = res.grupos_beneficiario;
      this.doencas_cronicas = res.doencas_cronicas;
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  get dependentes(): FormArray {
    return this.form.get("dependentes") as FormArray
  }

  salvarDependente(model) {

    model.data_nascimento = `${this.modelData_dependente.ano}-${this.modelData_dependente.mes}-${this.modelData_dependente.dia}`;

    if (model.doenca_cronica) {
      // console.log(model.doenca_cronica_nome);
      model.doenca_cronica_nome = this.selctedDoencas1
    } else {
      model.doenca_cronica_nome = [];
    }

    this.dependentes.push(this.formBuilder.group({
      id: [model.id],
      utilizador_activo: [model.utilizador_activo],
      nome: [model.nome],
      numero_identificacao: [model.numero_identificacao],
      email: [model.email],
      endereco: [model.endereco],
      bairro: [model.bairro],
      telefone: [model.telefone],
      genero: [model.genero],
      parantesco: [model.parantesco],
      data_nascimento: [model.data_nascimento],
      doenca_cronica: [model.doenca_cronica],
      doenca_cronica_nome: [model.doenca_cronica_nome],
    }))

    // console.log(this.dependentes.value);
    this.modelDependente.id = null;
    this.modelDependente.utilizador_activo = true;
    this.modelDependente.nome = '';
    this.modelDependente.numero_identificacao = '';
    this.modelDependente.email = '';
    this.modelDependente.endereco = '';
    this.modelDependente.bairro = '';
    this.modelDependente.telefone = '';
    this.modelDependente.genero = '';
    this.modelDependente.data_nascimento = '';
    this.modelDependente.parantesco = '';
    this.modelDependente.doenca_cronica = false;
    this.modelDependente.doenca_cronica_nome = [];

    // data
    this.modelData_dependente.ano = null;
    this.modelData_dependente.dia = null;
    this.modelData_dependente.mes = null;
    document.getElementById('actualizarID_dependente').click();
  }


  viewDependenteModal(item, element_position) {
    // console.log(item.value);

    this.modelDependente.id = item.value.id;
    this.modelDependente.utilizador_activo = item.value.utilizador_activo;
    this.modelDependente.nome = item.value.nome;
    this.modelDependente.numero_identificacao = item.value.numero_identificacao;
    this.modelDependente.email = item.value.email;
    this.modelDependente.endereco = item.value.endereco;
    this.modelDependente.bairro = item.value.bairro;
    this.modelDependente.telefone = item.value.telefone;
    this.modelDependente.genero = item.value.genero;
    this.modelDependente.parantesco = item.value.parantesco;
    this.modelDependente.data_nascimento = item.value.data_nascimento;
    this.modelDependente.doenca_cronica = item.value.doenca_cronica;
    this.modelDependente.doenca_cronica_nome = item.value.doenca_cronica_nome;

    let check = moment(item.value.data_nascimento, 'YYYY/MM/DD');
    this.modelData_dependente.dia = +check.format('D');
    this.modelData_dependente.mes = +check.format('M');
    this.modelData_dependente.ano = +check.format('YYYY');

    this.editDependente = true;
    this.element_dependente_position = element_position;
    // console.log(this.modelDependente);

  }

  updateDependente(model) {

    // console.log(this.dependentes.value)

    if (model.doenca_cronica) {
      // // console.log(model.doenca_cronica_nome);
      model.doenca_cronica_nome = this.selctedDoencas1;
    } else {
      model.doenca_cronica_nome = [];
    }

    model.data_nascimento = `${this.modelData_dependente.ano}-${this.modelData_dependente.mes}-${this.modelData_dependente.dia}`;

    this.dependentes.value[this.element_dependente_position].id = model.id;
    this.dependentes.value[this.element_dependente_position].utilizador_activo = model.utilizador_activo;
    this.dependentes.value[this.element_dependente_position].nome = model.nome;
    this.dependentes.value[this.element_dependente_position].nome = model.numero_identificacao;
    this.dependentes.value[this.element_dependente_position].email = model.email;
    this.dependentes.value[this.element_dependente_position].endereco = model.endereco;
    this.dependentes.value[this.element_dependente_position].bairro = model.bairro;
    this.dependentes.value[this.element_dependente_position].telefone = model.telefone;
    this.dependentes.value[this.element_dependente_position].genero = model.genero;
    this.dependentes.value[this.element_dependente_position].parantesco = model.parantesco;
    this.dependentes.value[this.element_dependente_position].data_nascimento = model.data_nascimento;
    this.dependentes.value[this.element_dependente_position].doenca_cronica = model.doenca_cronica;
    this.dependentes.value[this.element_dependente_position].doenca_cronica_nome = model.doenca_cronica_nome;

    // console.log(this.dependentes.value);

    this.modelDependente.id = null;
    this.modelDependente.utilizador_activo = true;
    this.modelDependente.nome = '';
    this.modelDependente.numero_identificacao = '';
    this.modelDependente.endereco = '';
    this.modelDependente.bairro = '';
    this.modelDependente.telefone = '';
    this.modelDependente.genero = '';
    this.modelDependente.parantesco = '';
    this.modelDependente.data_nascimento = '';
    this.modelDependente.doenca_cronica = false;
    this.modelDependente.doenca_cronica_nome = [];
    // data
    this.modelData_dependente.ano = null;
    this.modelData_dependente.dia = null;
    this.modelData_dependente.mes = null;

    document.getElementById('actualizarID_dependente').click();
  }

  addDependente() {
    this.modelDependente.id = null;
    this.modelDependente.utilizador_activo = true;
    this.modelDependente.nome = '';
    this.modelDependente.email = '';
    this.modelDependente.numero_identificacao = '';
    this.modelDependente.endereco = '';
    this.modelDependente.bairro = '';
    this.modelDependente.telefone = '';
    this.modelDependente.genero = '';
    this.modelDependente.parantesco = '';
    this.modelDependente.data_nascimento = '';
    this.modelDependente.doenca_cronica = false;
    this.modelDependente.doenca_cronica_nome = [];

    this.editDependente = false;
  }

  removeDependente(i: number, id_dependente: number) {

    if (id_dependente) {
      this.deleteDependente(i, id_dependente);
    } else {
      this.dependentes.removeAt(i);
    }
  }

  get arrayDoencas() {
    return <FormArray>this.form.get('doenca_cronica_nome');
  }

  addDoenca() {
    this.arrayDoencas.push(this.formBuilder.group({ nome: '' }));
  }

  addDoenca_dependente() {
    // console.log(this.modelDependente.doenca_cronica_nome);
    this.modelDependente.doenca_cronica_nome.push('');
    // console.log(this.modelDependente.doenca_cronica_nome);
  }

  deleteDoenca(index) {
    this.arrayDoencas.removeAt(index);
  }

  deleteDoenca_dependente(index) {
    // console.log(this.modelDependente.doenca_cronica_nome);
    // console.log(index);

    this.modelDependente.doenca_cronica_nome.splice(index, 1);
  }

  trackByIdx(index: number, obj: any): any {
    return index;
  }

  // onChange(event) {
  //   this.file = event.target.files[0].name;
  //   // console.log(this.file)
  // }

  // importarExcell() {
  //   console.log(this.file)
  //   this._bn.uploadFile(this.file).subscribe((response) => {
  //     console.log(response)
  //   })
  //   // const formData = new FormData();

  // }

  onChange(event) {
    this.ficheiro = event.target.files[0];
  }

  // OnClick of button Upload
  importarExcell() {
    this.spinner = true
    console.log(this.ficheiro);
    this._bn.upload(this.ficheiro).subscribe(
      (response) => {
        Swal.fire({
          title: 'Submetido.',
          text: "Criado com sucesso.",
          type: 'success',
          showCancelButton: false,
          confirmButtonColor: "#f15726",
          confirmButtonText: 'Ok'
        })
        document.getElementById('actualizarID_dependente').click();
        this.getAll()

        console.log(response)
      },
      error => {
        console.log(error)
        this.spinner = false
      }
    );
  }



  viewCard(row) {
    // console.log(row);
    this.ArrayView = [];
    this.temDoencaCronica = false;
    this.viewBtn = true;
    this.editBtn = false;
    this.editBtnData = false;
    this.dependentes.reset()
    this.submitBtn = false;

    this.selctedDoencas = row.doenca_cronica_nome;
    // console.log(this.selctedDoencas);

    this.dependentes.value.forEach((element, index) => {
      this.dependentes.removeAt(index)
    });
    // console.log(this.dependentes.value);

    if (row.doenca_cronica) {
      this.selctedDoencas = row.doenca_cronica_nome;
      // this.ArrayView = this.ArrayView.map((str) => ({ nome: str }));
      this.temDoencaCronica = true;

    }

    this.temDependentes = row.tem_dependentes;
    if (row.tem_dependentes) {

      row.dependentes.forEach(element => {
        let utilizador_activo_depe: boolean;
        if (element.utilizador_activo) {
          utilizador_activo_depe = true
        } else {
          utilizador_activo_depe = false
        }

        this.selctedDoencas1 = element.doenca_cronica_nome
        this.dependentes.push(this.formBuilder.group({
          id: [element.id],
          utilizador_activo: [utilizador_activo_depe],
          nome: [element.nome],
          numero_identificacao: [element.numero_identificacao],
          email: [element.email],
          endereco: [element.endereco],
          bairro: [element.bairro],
          telefone: [element.telefone],
          genero: [element.genero],
          parantesco: [element.parantesco],
          data_nascimento: [element.data_nascimento],
          doenca_cronica: [element.doenca_cronica],
          doenca_cronica_nome: [element.doenca_cronica_nome],
          // doenca_cronica_nome: this.formBuilder.array(element.doenca_cronica_nome),
        }))
      });
      // console.log(this.dependentes.value);
      this.dependentes.value.forEach((element, index) => {
        if (element.nome === null) {
          this.dependentes.removeAt(index);
        }
      });

    } else {
      this.dependentes.reset();
    }

    let utilizador_activo: boolean;
    if (row.utilizador_activo) {
      utilizador_activo = true
    } else {
      utilizador_activo = false
    }

    this.form.patchValue({
      id: row.id,
      utilizador_activo: utilizador_activo,
      nome: row.nome,
      numero_identificacao: row.numero_identificacao,
      email: row.email,
      numero_beneficiario: row.numero_beneficiario,
      endereco: row.endereco,
      bairro: row.bairro,
      telefone: row.telefone,
      genero: row.genero,
      data_nascimento: row.data_nascimento,
      ocupacao: row.ocupacao,
      aposentado: row.aposentado,
      doenca_cronica: row.doenca_cronica,
      doenca_cronica_nome: this.selctedDoencas,
      // doencas: this.selctedDoencas,
      // doenca_cronica_nome: this.ArrayView,
      grupo_beneficiario_id: row.grupo_beneficiario_id,
      tem_dependentes: row.tem_dependentes,
      // dependentes: arraYt,
      // dependentes: this.formBuilder.array(row.dependentes),
    });


    this.form.updateValueAndValidity();
    // console.log(this.form.value);
    let check = moment(row.data_nascimento, 'YYYY/MM/DD');
    this.modelData.dia = +check.format('D');
    this.modelData.mes = +check.format('M');
    this.modelData.ano = +check.format('YYYY');

    this.form.disable();
  }

  add() {
    this.viewBtn = false;
    this.submitBtn = true;
    this.editBtnData = true;
    this.editBtn = false;
    this.form.enable();
    this.initializeFormSubmit();
    this.modelData.ano = null;
    this.modelData.dia = null;
    this.modelData.mes = null;
    this.temDependentes = false;
    this.temDoencaCronica = false;
  }

  toUpdate() {
    this.editBtn = true;
    this.editBtnData = true;
    this.submitBtn = false;
    this.viewBtn = false;
    this.form.enable();
  }

  update() {
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
    this.form.get('data_nascimento').patchValue(stringDate);

    if (this.temDoencaCronica) {
      let texts = this.selctedDoencas
      // let texts = this.form.value.doenca_cronica_nome.map(function (el){
      //   return el.nome
      // });
      this.form.value.doenca_cronica_nome = texts;

    } else {
      this.form.value.doenca_cronica_nome = [];
    }

    this.form.value.tem_dependentes = this.temDependentes;
    if (this.temDependentes) {
      // Dependente

    } else {
      this.form.value.dependentes = [];
    }

    // console.log(this.form.value);
    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._bn.update(this.form.value).subscribe(data => {
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
        this._bn.delete(this.form.value.id).subscribe(res => {
          document.getElementById('actualizarID').click();
          this.getAll();
          this.getQuantidadeBeneficiarios();
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


  deleteDependente(index_position, id: number) {
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
        this._bn.deleteDependente(id).subscribe(res => {
          // document.getElementById('actualizarID').click();
          this.dependentes.removeAt(index_position);
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

  onTelefoneChange(event) {
    const regex = new RegExp('^[+]{0,1}[0-9]{8,}$');
    const valid = regex.test(event);
    if (!valid) {
      this.invalidTelefoneBoolean = true;
    } else {
      this.invalidTelefoneBoolean = false;
    }
  }

  doenca_dependente_change(index_position, event) {

    this.modelDependente.doenca_cronica_nome.forEach((element, index) => {
      if (index == index_position) {
        this.modelDependente.doenca_cronica_nome[index] = event.srcElement.value;
      }
    });
    // console.log(this.modelDependente.doenca_cronica_nome);
  }

  trackByFn(index: any) {
    return index;
  }

  onSelectOption_utilizador(item) {
    // console.log(item.value)
    this.selctedDoencas = item.value;
    // console.log(this.selctedDoencas);
  }
  onSelectOption_Dependente(item) {
    this.selctedDoencas1 = item.value;
    // console.log(this.selctedDoencas1);
  }


}

