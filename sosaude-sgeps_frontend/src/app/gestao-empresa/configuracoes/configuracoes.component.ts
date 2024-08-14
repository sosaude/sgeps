import { Component, OnInit, Renderer2 } from '@angular/core';
import { GruposService } from 'src/app/_services/grupos.service';
import { AuthenticationService } from 'src/app/_services/authentication.service';
import { Router } from '@angular/router';
import { FormBuilder, Validators, FormGroup } from '@angular/forms';
import { Chart } from 'node_modules/chart.js';
import Swal from 'sweetalert2';
import { ConfiguracoesEmpresaService } from 'src/app/_services/configuracoes-empresa.service';
import { element } from '@angular/core/src/render3';
import { Observable } from 'rxjs';
import { UtilizadorService } from 'src/app/_services/utilizador.service';
import { OrcamentosEmpresaService } from 'src/app/_services/orcamentos-empresa.service';

@Component({
  selector: 'app-configuracoes',
  templateUrl: './configuracoes.component.html',
  styleUrls: ['./configuracoes.component.scss']
})
export class ConfiguracoesComponent implements OnInit {

  grupos: any[];
  form: FormGroup;
  spinner_1: boolean = false;
  viewTableAndChart: boolean = false;
  viewBtn: boolean = false;
  editBtn: boolean = false;
  submitBtn: boolean = true;
  submitted: boolean = false;
  spinner_modal: boolean = false;
  searchText: any;
  searchText_farmacias: any;
  searchText_farmacias_modal: any;
  searchText_clinicas: any;
  searchText_utilizador: any;
  searchText_orcamento: any;
  searchText_clinicas_modal: any;
  selectedCardView = 1;
  farmacias_associar: any[];
  clinicas_associar: any[];
  farmacias_associadas: any[];
  clinicas_associadas: any[];
  // ============
  favoriteSeason: string;
  seasons: string[] = ['Winter', 'Spring', 'Summer', 'Autumn'];
  situacao_farmaceutico: any[] = [
    { nome: 'Activo', value: 1 }, { nome: 'Inativo', value: 0 }
  ]

  currentCheckedValue = null;
  listFarmaciasSelecionadas: any[] = [];
  value_farmacia_exist: boolean;
  listClinicassSelecionadas: any[] = [];
  form_utilizador: FormGroup;
  spinner: boolean = false;
  utilizadores: any[];
  perfil_roles: any[];
  perfil_permissoes: any[];
  selectedPermissoes: any[];
  model_grupo_temp = {
    nome:'',
    id:null
  }
  currentYear = new Date().getFullYear();
  anos: any[] = [];

  // ===== Orcamento Begin
  form_orcamento: FormGroup;
  orcamentos: any[];
  orcamentoExec: any[];
  orcamento_tipos: any[] = [
    { nome: 'Anual', value: 'Anual' }, { nome: 'Plurianual', value: 'Plurianual' }
  ];

  executado_ou_orcamento: any[] = [
    { nome: 'Executado', value: true }, { nome: 'Orcamento', value: false }
  ]

  model_orcamento_view = {
    total: null
  }
  // ===== Orcamento End

  group_id_selected:boolean = false;
  invalidTelefoneBoolean: boolean = false;

  p = 1 //paginação

  constructor(private authenticationService: AuthenticationService,
    private router: Router,
    private formBuilder: FormBuilder,
    private _ut: UtilizadorService,
    private _grupo: GruposService,
     private _configuracao: ConfiguracoesEmpresaService,
    private _orcamento: OrcamentosEmpresaService
     ) { }

  ngOnInit() {
    this.initializeGrupo();
    this.getAll();
    this.initializeFormSubmit();
    this.initializeOrcamentoFormSubmit();

    for (let index = 0; index < 10; index++) {
      this.anos.push(this.currentYear.toString())
      this.currentYear = this.currentYear - 1;
      
    }
  }

  initializeGrupo() {
    this.form = this.formBuilder.group({
      nome: ['', Validators.required],
      id: [''],
    });
  }

  getAll() {
    this.spinner_1 = true;
    this._grupo.getAll().subscribe(data => {
      this.grupos = Object(data)["data"]

      // let valTotal = this.grupos.reduce((id, grupo) => {
      //   id += grupo.id;
      //   return id
      // }, 0)
   
      this.spinner_1 = false;
    }, error => {
      // console.log(error)
      this.spinner_1 = false;
    })
  }

// ===========  Farmacias ===============
  getAssociarFarmacias() {
    if (this.farmacias_associar) {
      return;
    }
    this.spinner_modal = true;
    this._configuracao.getAssociarFarmacias().subscribe(data => {
      this.farmacias_associar = Object(data)["data"]
      this.farmacias_associar.sort((a, b) => {
        return a.nome.localeCompare(b.nome);
      });
      this.spinner_modal = false;
    }, error => {
      // console.log(error)
      this.spinner_modal = false;
    })
  }

  getFarmaciasAssociadas() {
    if (this.farmacias_associadas) {
      return;
    }
    this.spinner_1 = true;
    this._configuracao.getFarmaciasAssociadas().subscribe(data => {
      // console.log(data);
      this.farmacias_associadas = Object(data)["data"]
      this.farmacias_associadas.sort((a, b) => {
        return a.nome.localeCompare(b.nome);
      });
      this.farmacias_associadas.forEach(el => {
        this.listFarmaciasSelecionadas.push(el.id)
      })
      this.spinner_1 = false;
    }, error => {
      // console.log(error)
      this.spinner_1 = false;
    })
  }


  associarFarmaciasEmpresa() {
    this.spinner_modal = true;
    // console.log(this.listFarmaciasSelecionadas);
    this._configuracao.associarFarmaciasEmpresa(this.listFarmaciasSelecionadas).subscribe(data => {
      // console.log(data);
      document.getElementById('actualizarID_1').click();
      let res = Object(data)["data"]
      this.farmacias_associadas = res.farmacias;
      this.farmacias_associadas.sort((a, b) => {
        return a.nome.localeCompare(b.nome);
      });
      this.getFarmaciasAssociadas()
      this.spinner_modal = false;
    }, error => {
      // console.log(error)
      this.spinner_modal = false;
    })
  }

  desassociarFarmacias(id) {
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
    if(result.value){
     this.spinner_1 = true;
      this._configuracao.desassociarFarmacias(id).subscribe(data => {
        // console.log(data);
        let res = Object(data)["data"]
        this.farmacias_associadas = res.farmacias;
        this.farmacias_associadas.sort((a, b) => {
          return a.nome.localeCompare(b.nome);
        });
        this.getFarmaciasAssociadas()
        this.spinner_1 = false;
      }, error => {
        // console.log(error)
        this.spinner_1 = false;
      })
    }
    })
  }
// ===========  Clinica Teste ===============
getAssociarClinicas() {
  if (this.clinicas_associar){
    return;
  }
    this.spinner_modal = true;
    this._configuracao.getAssociarClinicas().subscribe(data => {
      this.clinicas_associar = Object(data)["data"]
      this.clinicas_associar.sort((a, b) => {
        return a.nome.localeCompare(b.nome);
      });
      this.spinner_modal = false;
    }, error => {
      // console.log(error)
      this.spinner_modal = false;
    })
  }

  getClinicassAssociadas() {
    if (this.clinicas_associadas) {
      return;
    }
    this.spinner_1 = true;
    this._configuracao.getClinicasAssociadas().subscribe(data => {
      // console.log(data);
      this.clinicas_associadas = Object(data)["data"]
      this.clinicas_associadas.sort((a, b) => {
        return a.nome.localeCompare(b.nome);
      });
      this.clinicas_associadas.forEach(el => {
        this.listClinicassSelecionadas.push(el.id)
      })
      this.spinner_1 = false;
    }, error => {
      // console.log(error)
      this.spinner_1 = false;
    })
  }


  associarClinicaEmpresa() {
    this.spinner_modal = true;
    // console.log(this.listClinicassSelecionadas);
    this._configuracao.associarClinicasEmpresa(this.listClinicassSelecionadas).subscribe(data => {
      // console.log(data);
      document.getElementById('actualizarID_2').click();
      let res = Object(data)["data"]
      this.clinicas_associadas = res.unidades_sanitarias;
      this.clinicas_associadas.sort((a, b) => {
        return a.nome.localeCompare(b.nome);
      });
      this.getClinicassAssociadas();
      this.spinner_modal = false;
    }, error => {
      // console.log(error)
      this.spinner_modal = false;
    })
  }

  desassociarClinica(id) {
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
      this.spinner_1 = true;
      this._configuracao.desassociarClinica(id).subscribe(data => {
        // console.log(data);
        let res = Object(data)["data"]
        this.clinicas_associadas = res.unidades_sanitarias;
        this.clinicas_associadas.sort((a, b) => {
          return a.nome.localeCompare(b.nome);
        });
        this.getClinicassAssociadas();
        this.spinner_1 = false;
      }, error => {
        // console.log(error)
        this.spinner_1 = false;
      })
    })
  }

// ===========  CLINICAS ===============

// getAssociarClinicas() {
//     if (this.clinicas_associar) {
//       return;
//     }
//     this.spinner_modal = true;
//     this._configuracao.getAssociarClinicas().subscribe(data => {
//       this.clinicas_associar = Object(data)["data"]
//       this.clinicas_associar.sort((a, b) => {
//         return a.nome.localeCompare(b.nome);
//       });
//       this.spinner_modal = false;
//     }, error => {
//       // console.log(error)
//       this.spinner_modal = false;
//     })
//   }

//   getClinicassAssociadas() {
//     if (this.clinicas_associadas){
//       return;
//     }
//     this.spinner_1 = true;
//     this._configuracao.getClinicasAssociadas().subscribe(data => {
//       // console.log(data);
//       this.clinicas_associadas = Object(data)["data"]
//       this.clinicas_associadas.sort((a, b) => {
//         return a.nome.localeCompare(b.nome);
//       });
//       this.spinner_1 = false;
//     }, error => {
//       // console.log(error)
//       this.spinner_1 = false;
//     })
//   }

//   associarClinicaEmpresa() {
//     this.spinner_modal = true;
//     // console.log(this.listClinicassSelecionadas);
//     this._configuracao.associarClinicasEmpresa(this.listClinicassSelecionadas).subscribe(data => {
//       // console.log(data);
//       document.getElementById('actualizarID_2').click();
//       let res = Object(data)["data"]
//       this.clinicas_associadas = res.unidades_sanitarias;
//       this.clinicas_associadas.sort((a, b) => {
//         return a.nome.localeCompare(b.nome);
//       });
//       // this.getClinicassAssociadas();

//       this.spinner_modal = false;
//     }, error => {
//       // console.log(error)
//       this.spinner_modal = false;
//     })
//   }

//   desassociarClinica(id) {
//     Swal.fire({
//       title: 'Tem certeza que deseja remover?',
//       text: "",
//       type: 'warning',
//       showCancelButton: true,
//       confirmButtonColor: "#f15726",
//       cancelButtonText: "Cancelar",
//       // cancelButtonColor: '#d33',
//       confirmButtonText: 'Sim',
//       reverseButtons: true
//     }).then((result) => {
//       this.spinner_1 = true;
//       this._configuracao.desassociarClinica(id).subscribe(data => {
//         // console.log(data);
//         let res = Object(data)["data"]
//         this.clinicas_associadas = res.unidades_sanitarias;
//         this.clinicas_associadas.sort((a, b) => {
//           return a.nome.localeCompare(b.nome);
//         });
//         // this.getClinicassAssociadas();
//         this.spinner_1 = false;
//       }, error => {
//         // console.log(error)
//         this.spinner_1 = false;
//       })
//     })
//   }


  viewGrupo(row) {
    this.viewBtn = true;
    this.editBtn = false;
    this.submitBtn = false;
    this.form.patchValue({
      nome: row.nome,
      id: row.id
    })
    // this.form.setValue(row);
    // console.log(this.form.value);
    this.form.disable();
  }

  toUpdate() {
    this.editBtn = true;
    this.submitBtn = false;
    this.viewBtn = false;
    this.form.enable();
  }

  addGrupo() {
    this.viewBtn = false;
    this.submitBtn = true;
    this.editBtn = false;
    this.form.enable();
    this.initializeGrupo();
  }

  submit() {
    this.submitted = true;
    // console.log(this.form.value);
    if (this.form.invalid) {
      return
    }

    this.spinner_modal = true;
    this._grupo.register(this.form.value).subscribe(data => {
      this.spinner_modal = false;
      document.getElementById('actualizarID').click();
      this.getAll();
      this.form.reset();
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
        this.spinner_modal = false;
      })
  }

  update() {
    this.submitted = true;
    // console.log(this.form.value);
    if (this.form.invalid) {
      return
    }
    this.spinner_modal = true;
    let model = {
      id: this.form.value.id,
      nome: this.form.value.nome
    }
    this._grupo.update(this.form.value).subscribe(data => {
      this.form.reset();
      this.spinner_modal = false;
      document.getElementById('actualizarID').click();
      this.getAll()
    },
      error => {
        // console.log(error);
        this.spinner_modal = false;
      })
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
        this.spinner_1 = true;
        this._grupo.delete(row.id).subscribe(res => {
          document.getElementById('actualizarID').click();
          this.getAll();
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

  changeView(opcao) {

    switch (opcao) {
      case 1:
        this.group_id_selected = false;
        this.selectedCardView = 1;
        break;
      case 2:
        this.group_id_selected = false;
        this.selectedCardView = 2;
        this.getFarmaciasAssociadas();
        break;
      case 3:
        this.group_id_selected = false;
        this.selectedCardView = 3;
        this.getClinicassAssociadas();
        break;
      case 4:
        this.group_id_selected = false;
        this.selectedCardView = 4;
        this.getAllUtilizadores();
        break;
      case 5:
        this.group_id_selected = false;
        this.model_grupo_temp.id = 0;
        this.model_grupo_temp.nome = "Plano de saúde principal";
        this.selectedCardView = 5;
        break;
      case 6:
        this.group_id_selected = false;
        this.selectedCardView = 6;
        this.getAllOrcamentos();
        break;
    }
  }

  goToPlanoSaude(grupo) {
    this.model_grupo_temp.id = grupo.id;
    this.model_grupo_temp.nome = grupo.nome;
    this.group_id_selected = true;
    this.selectedCardView = 1;
  }

 
  verifyIfChecked(id_farmacia){
    let status = this.farmacias_associadas.some(element => element['id'] === id_farmacia);
    return status;
  }

  onSelectOption(id_farmacia:number) {
      this.checkIfExists(id_farmacia)
      if (!this.value_farmacia_exist) {
      	this.listFarmaciasSelecionadas.push(id_farmacia)
      }
      else {
        for (let index = 0; index < this.listFarmaciasSelecionadas.length; index++) {
          if (this.listFarmaciasSelecionadas[index] == id_farmacia) {
            this.listFarmaciasSelecionadas.splice(index,1)
          }
        }
      }      
  }
  checkIfExists(itemId) {
    this.value_farmacia_exist = this.listFarmaciasSelecionadas.some((item) => {
      return item === itemId
    })
  }

  verifyIfChecked_clinica(id_clinica){
    let status = this.clinicas_associadas.some(element => element['id'] === id_clinica);
    return status;

  }

  onSelectOption_clinica(id_clinica:number) {
    // console.log("clinicas",id_clinica)
      this.checkIfExists_clinica(id_clinica)
      if (!this.value_farmacia_exist) {
      	this.listClinicassSelecionadas.push(id_clinica)
      }
      else {
        for (let index = 0; index < this.listClinicassSelecionadas.length; index++) {
          if (this.listClinicassSelecionadas[index] == id_clinica) {
            this.listClinicassSelecionadas.splice(index,1)
          }
        }
      }      
  }
  checkIfExists_clinica(itemId) {
    this.value_farmacia_exist = this.listClinicassSelecionadas.some((item) => {
      return item === itemId
    })
  }


  // ==================================  UTILIZADOR =======================================
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
  

  getRolesUtilizador() {
    if (this.perfil_roles && this.perfil_permissoes) {
      return;
    }

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
    this._ut.registerUtilizadoresEmpresa(this.form_utilizador.value).subscribe(data => {
      this.form_utilizador.reset();
      this.spinner = false;
      document.getElementById('actualizarID_3').click();
     this.getAllUtilizadores();
    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
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
    this._ut.updateUtilizadoresEmpresa(this.form_utilizador.value).subscribe(data => {
      this.form_utilizador.reset();
      this.spinner = false;
      document.getElementById('actualizarID_3').click();
      this.getAllUtilizadores();
    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
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
        this._ut.deleteUtilizadoresEmpresa(this.form_utilizador.value.id).subscribe(res => {
          // console.log("new user:", res);
          
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
    })
  }

  viewCardUtilizador(row) {
    // console.log(row);
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
    // console.log(this.selectedPermissoes);
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


   // ==================================  Orcamento =======================================
  
   initializeOrcamentoFormSubmit() {
    this.form_orcamento = this.formBuilder.group({
      tipo_orcamento: ['', Validators.required],
      orcamento_laboratorio: ['', Validators.required],
      orcamento_farmacia: ['', Validators.required],
      orcamento_clinica: ['', Validators.required],
      executado: [false],
      ano_de_referencia: ['', Validators.required],
      id: [''],
    });
  }

   getAllOrcamentos() {
   
    this.spinner = true;
    this._orcamento.getAll().subscribe(data => {
      this.orcamentos = Object(data)["data"]
      this.spinner = false;
    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  getOrcamentoExecutado() {
   
    this.spinner_1 = true;
    this._orcamento.getExecutados().subscribe(data => {
      let resData = Object(data)["data"];
      this.orcamentoExec = resData.orcamentos;
      this.ChartBaixasUS(resData);
      this.spinner_1 = false;
    }, error => {
      // console.log(error)
      this.spinner_1 = false;
    })
  }


  addOrcamento() {
    this.viewBtn = false;
    this.viewTableAndChart = false;
    this.submitBtn = true;
    this.editBtn = false;
    this.form_orcamento.enable();
    this.initializeOrcamentoFormSubmit();
    this.calculoOrcamento();
  }
 

  viewCardOrcamento(row) {
    // console.log(row);
    this.viewTableAndChart = true;
    this.viewBtn = true;
    this.editBtn = false;
    this.submitBtn = false;

    this.form_orcamento.patchValue({

      tipo_orcamento: row.tipo_orcamento,
      orcamento_laboratorio: row.orcamento_laboratorio,
      orcamento_farmacia: row.orcamento_farmacia,
      orcamento_clinica: row.orcamento_clinica,
      ano_de_referencia: row.ano_de_referencia,
      executado: row.executado,
      id: row.id,
    })
    this.getOrcamentoExecutado();
    this.calculoOrcamento();
    // console.log(this.form_utilizador.value);
    this.form_orcamento.disable();
  }

  submitOrcamento() {
    this.submitted = true;
    if (this.form_orcamento.invalid) {
      return
    }
    this.spinner = true;

    this._orcamento.register(this.form_orcamento.value).subscribe(data => {
      this.form_orcamento.reset();
      this.spinner = false;
      document.getElementById('actualizarID_4').click();
     this.getAllOrcamentos();
    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
  }

  toUpdateOrcamento() {
    this.editBtn = true;
    this.submitBtn = false;
    this.viewBtn = false;
    this.form_orcamento.enable();
  }

  updateSubmitOrcamento() {
    this.submitted = true;
    if (this.form_orcamento.invalid) {
      return
    }
    Swal.fire({
      title: 'Tem certeza que deseja actualizar?',
      text: "",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: "#f15726",
      cancelButtonText: "Cancelar",
      // cancelButtonColor: '#d33',
      confirmButtonText: 'Sim',
      reverseButtons: true
    }).then((result) => {
    this.spinner = true;
    this._orcamento.updateOrcamentoEmpresa(this.form_orcamento.value).subscribe(data => {
      this.form_orcamento.reset();
      this.spinner = false;
      document.getElementById('actualizarID_4').click();
      this.getAllOrcamentos();
    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
    })
  }


  deleteOrcamento() {
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
        this._orcamento.deleteOrcamentoEmpresa(this.form_orcamento.value.id).subscribe(res => {
          // console.log("new user:", res);
          
          document.getElementById('actualizarID_3').click();
          this.getAllOrcamentos();
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
    })
  }

  onSelectOption_tipoOrcamento(item) {
    this.form_orcamento.get('tipo_orcamento').patchValue(item.value);
  }

  onSelectOption_AnoDeReferencia(item) {
    const ano = item.value.toString();
    this.form_orcamento.get('ano_de_referencia').patchValue(ano);
  }

  ChartBaixasUS(data){
    var lineChartData = { labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'], datasets: [] },
    array = data.linechart_data;
    const randomNum = () => Math.floor(Math.random() * (235 - 52 + 1) + 52);
    const randomRGB = () => `rgb(${randomNum()}, ${randomNum()}, ${randomNum()})`;
    const transparentRGB = () => `rgb(255, 0, 0, 0)`;

    array.forEach(function (a, i) {
        lineChartData.datasets.push({
            label: data.linechart_labels[i],
            data: a,
            backgroundColor: transparentRGB(),
            borderColor: randomRGB()
            
        });
});


    const myChart1 = new Chart("myChart1", {
      type: 'line',
  data:lineChartData,
  options: {
    responsive: true,
    plugins: {
      legend: {
        position: 'top',
      },
      title: {
        display: true,
        text: 'Executados Por Ano'
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  },
    });
  }

  calculoOrcamento() {
    this.model_orcamento_view.total = this.form_orcamento.value.orcamento_laboratorio + this.form_orcamento.value.orcamento_farmacia +this.form_orcamento.value.orcamento_clinica ;
    this.model_orcamento_view.total = this.model_orcamento_view.total.toFixed(2);
  }


}
