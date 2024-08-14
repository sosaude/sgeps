import { Component, OnInit, ViewChild } from '@angular/core';
import Swal from 'sweetalert2';
import { AuthenticationService } from '../_services/authentication.service';
import { Router } from '@angular/router';
import { Validators, FormGroup, FormBuilder, FormControl } from '@angular/forms';
import { MedicamentosService } from '../_services/medicamentos.service';
import { MatSelect } from '@angular/material';
import { Subject, ReplaySubject, Observable } from 'rxjs';
import { takeUntil, take, startWith, map } from 'rxjs/operators';
import { GruposService } from '../_services/grupos.service';

export class Medicamento {
  id: number;
  nome: string;
  deleted_at?: string;
  created_at: string;
  updated_at?: string;
  codigo: string;
}
@Component({
  selector: 'app-medicamentos',
  templateUrl: './medicamentos.component.html',
  styleUrls: ['./medicamentos.component.scss']
})

export class MedicamentosComponent implements OnInit {

  authValue: any;
  form: FormGroup;
  categoria_medicamento: any[] = [
    { nome: 'Técnico de farmácia' }, { nome: 'Bacharel em farmácia' }, { nome: 'Farmacêutico' }
  ]
  situacao_medicamento: any[] = [
    { nome: 'Activo', value: 1 }, { nome: 'Inativo', value: 0 }
  ]
  submitted: boolean = false;
  spinner: boolean;
  medicamentos: any[];
  marcas: any[];
  viewBtn: boolean = false;
  editBtn: boolean = false;
  submitBtn: boolean = true;
  searchText: any;
  searchText_marca: any;
  form_marca: FormGroup;
  options: Medicamento[] = [];
  medicamentoView = {
    codigo: "",
    nome: "",
    dosagem: "",
    forma: "",
    deleted_at: null,
    created_at: "",
    updated_at: "",
    id: null
  };
  filteredOptions: Observable<Medicamento[]>;
  myControl = new FormControl();
  marcasviewContainerBool: boolean = false;
  spinner_1: boolean = false;
  spinner_2: boolean = false;
  allFormas: any[];
  group_modal_option: number;
  form_grupos: FormGroup;
  form_sub_grupos: FormGroup;
  form_sub_classes: FormGroup;
  spinner_grupo: boolean;
  grupos_terapeuticos: any;
  sub_group_modal_option: number;
  sub_grupos_terapeuticos: any;
  create_grupos_all: any;
  sub_classes_terapeuticos: any;
  sub_class_modal_option: number;
  form_modal_option: number;
  formas_terapeutica: any;
  form_formas: FormGroup;
  form_nome_generico: FormGroup;
  nomeGenerico_modal_option: number;
  nomes_Genericos: any;
  sub_grupos_medicamentos: any;
  sub_classes_medicamentos: any;
  constructor(private authenticationService: AuthenticationService,
    private router: Router,
    private formBuilder: FormBuilder,
    private _medicamento: MedicamentosService,
    private _grupo: GruposService) {
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
    this.initializeFormMedicamentosSubmit();
    this.initializeFormMarcasSubmit();
    this.initializeFormGrupos();
    this.initializeFormSubGrupos();
    this.initializeFormSubClasses();
    this.initializeFormFormas();
    this.initializeFormNomeGenerico();
    this.getAll();
    this.getFormas();
    this.getGrupoCreate();
  }


  initializeFormMedicamentosSubmit() {
    this.form = this.formBuilder.group({
      codigo: ['', Validators.required],
      nome_generico_medicamento_id: ['', Validators.required],
      dosagem: ['', Validators.required],
      forma_medicamento_id: ['', Validators.required],
      grupo_medicamento_id: ['', Validators.required],
      sub_grupo_medicamento_id: ['', Validators.required],
      sub_classe_medicamento_id: [''],
      deleted_at: [''],
      created_at: [''],
      updated_at: [''],
      id: [''],
    });
  }


  initializeFormMarcasSubmit() {
    this.form_marca = this.formBuilder.group({
      id: [''],
      marca: ['', Validators.required],
      medicamento_id: ['', Validators.required],
      codigo: ['', Validators.required],
      pais_origem: ['', Validators.required],
      deleted_at: [''],
      created_at: [''],
      updated_at: [''],
    });
  }

  initializeFormGrupos() {
    this.form_grupos = this.formBuilder.group({
      id: [''],
      nome: ['', Validators.required],
    });
  }

  initializeFormSubGrupos() {
    this.form_sub_grupos = this.formBuilder.group({
      id: [''],
      nome: ['', Validators.required],
      grupo_medicamento_id: ['', Validators.required],
    });
  }

  initializeFormSubClasses() {
    this.form_sub_classes = this.formBuilder.group({
      id: [''],
      nome: ['', Validators.required],
      sub_grupo_medicamento_id: ['', Validators.required],
    });
  }

  initializeFormFormas() {
    this.form_formas = this.formBuilder.group({
      id: [''],
      forma: ['', Validators.required]
    });
  }

  initializeFormNomeGenerico() {
    this.form_nome_generico = this.formBuilder.group({
      id: [''],
      nome: ['', Validators.required]
    });
  }

  getAll() {
    this.spinner_1 = true;
    this._medicamento.getAll().subscribe(data => {
      this.medicamentos = Object(data)["data"]
      this.medicamentos.sort(function (a, b) {
        return a.nome_generico.nome.localeCompare(b.nome_generico.nome);
      });
      this.spinner_1 = false;
      this.options = Object(data)["data"];
    }, error => {
      // console.log(error)
      this.spinner_1 = false;
    })
  }

  getAllMarcas() {
    this.spinner = true;
    this._medicamento.getAllMarcas().subscribe(data => {
      this.marcas = Object(data)["data"]
      this.spinner = false;

    }, error => {
      // console.log(error)
      this.spinner = false;
    })
  }

  getGrupoCreate() {
    this._grupo.getAllGruposCreate().subscribe(data => {
      this.create_grupos_all = Object(data)["data"]
      // this.marcas = Object(data)["data"]
    }, error => {
      // console.log(error)
    })
  }

  getFormas() {
    this._medicamento.getFormas().subscribe(data => {
      this.allFormas = Object(data)["formas_marca_medicamento"];
    }, error => {
      // console.log(error)
    })
  }


  submit() {
    this.submitted = true;
    if (this.form.invalid) {
      return
    }

    this.spinner = true;
    this._medicamento.register(this.form.value).subscribe(data => {
      this.spinner = false;
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
        this.spinner = false;
      })
  }

  submitMarca() {
    this.submitted = true;
    if (this.form_marca.invalid) {
      return
    }
    this.spinner = true;
    this._medicamento.registerMarcas(this.form_marca.value).subscribe(data => {
      this.spinner = false;
      document.getElementById('actualizarID_1').click();
      this.getMarcasByEmpresaID(this.medicamentoView.id);
      this.form_marca.reset();
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
    if (this.form.invalid) {
      return
    }
    this.spinner = true;
    this._medicamento.update(this.form.value).subscribe(data => {
      this.form.reset();
      Swal.fire({
        title: 'Submetido.',
        text: "Actualizado com sucesso.",
        type: 'success',
        showCancelButton: false,
        confirmButtonColor: "#f15726",
        confirmButtonText: 'Ok'
      })
      document.getElementById('actualizarID').click();
      this.getAll();
      this.spinner = false;

    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
  }
  updateSubmitMarca() {
    this.submitted = true;
    this.form_marca.value.medicamento_id = this.medicamentoView.id;
    // console.log(this.form_marca.value);
    if (this.form_marca.invalid) {
      return
    }
    this.spinner = true;
    this._medicamento.updateMarcas(this.form_marca.value).subscribe(data => {
      this.form_marca.reset();
      this.spinner = false;
      // this.medicamentoView.id = '';
      // this.medicamentoView.nome = '';
      document.getElementById('actualizarID_1').click();
      this.getMarcasByEmpresaID(this.medicamentoView.id);
    },
      error => {
        // console.log(error);
        this.spinner = false;
      })
  }

  delete(row) {
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
        this._medicamento.delete(row.id).subscribe(res => {
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

  deleteMarca() {
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
        this.spinner = true;
        this._medicamento.deleteMarcas(this.form_marca.value.id).subscribe(res => {
          document.getElementById('actualizarID_1').click();
          this.getMarcasByEmpresaID(this.medicamentoView.id);
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
    this.viewBtn = true;
    this.editBtn = false;
    this.submitBtn = false;

    if(row.sub_classe_medicamento == null){
      this.form.patchValue({
        codigo: row.codigo,
        nome_generico_medicamento_id: row.nome_generico.id,
        dosagem: row.dosagem,
        forma_medicamento_id: row.forma_medicamento.id,
        grupo_medicamento_id: row.grupo_medicamento.id,
        sub_grupo_medicamento_id: row.sub_grupo_medicamento.id,
        // sub_classe_medicamento_id: row.sub_classe_medicamento.id,
        deleted_at: row.deleted_at,
        created_at: row.created_at,
        updated_at: row.updated_at,
        id: row.id,
      });
    }
    else {
      this.form.patchValue({
        codigo: row.codigo,
        nome_generico_medicamento_id: row.nome_generico.id,
        dosagem: row.dosagem,
        forma_medicamento_id: row.forma_medicamento.id,
        grupo_medicamento_id: row.grupo_medicamento.id,
        sub_grupo_medicamento_id: row.sub_grupo_medicamento.id,
        sub_classe_medicamento_id: row.sub_classe_medicamento.id,
        deleted_at: row.deleted_at,
        created_at: row.created_at,
        updated_at: row.updated_at,
        id: row.id,
      });
    }

    this.form.patchValue({
      codigo: row.codigo,
      nome_generico_medicamento_id: row.nome_generico.id,
      dosagem: row.dosagem,
      forma_medicamento_id: row.forma_medicamento.id,
      grupo_medicamento_id: row.grupo_medicamento.id,
      sub_grupo_medicamento_id: row.sub_grupo_medicamento.id,
      // sub_classe_medicamento_id: row.sub_classe_medicamento.id,
      deleted_at: row.deleted_at,
      created_at: row.created_at,
      updated_at: row.updated_at,
      id: row.id,
    });
    this.selectedGroup();
    this.selectedSubGroup();
    this.form.disable();
  }

  viewCardMarca(row) {
    this.viewBtn = true;
    this.editBtn = false;
    this.submitBtn = false;
    // for (let index = 0; index < this.medicamentos.length; index++) {
    //   if (this.medicamentos[index].id == row.id) {
    //     this.medicamentoView.id = this.medicamentos[index].id;
    //     this.medicamentoView.nome = this.medicamentos[index].nome;
    //   }
    // }
    this.form_marca.setValue({
      codigo: row.codigo,
      created_at: row.created_at,
      deleted_at: row.deleted_at,
      id: row.id,
      marca: row.marca,
      medicamento_id: row.medicamento_id,
      pais_origem: row.pais_origem,
      updated_at: row.updated_at
    });
    this.form_marca.disable();
  }

  toUpdate() {
    this.editBtn = true;
    this.submitBtn = false;
    this.viewBtn = false;
    this.form.enable();
  }

  toUpdateMarca() {
    this.editBtn = true;
    this.submitBtn = false;
    this.viewBtn = false;
    this.form_marca.enable();
  }

  addMedicamento() {
    this.viewBtn = false;
    this.submitBtn = true;
    this.editBtn = false;
    this.form.enable();
    this.initializeFormMedicamentosSubmit();
  }

  addMarca() {
    this.viewBtn = false;
    this.submitBtn = true;
    this.editBtn = false;
    this.form_marca.enable();
    this.initializeFormMarcasSubmit();
    this.form_marca.get('medicamento_id').setValue(this.medicamentoView.id);
  }

  viewMarcasContainer(item) {
    this.marcasviewContainerBool = true;
    this.medicamentoView.nome = item.nome_generico.nome;
    this.medicamentoView.id = item.id;
    this.medicamentoView.forma = item.forma_medicamento.forma;
    this.medicamentoView.dosagem = item.dosagem;
    this.getMarcasByEmpresaID(item.id);
  }


  getMarcasByEmpresaID(id) {
    this.spinner_2 = true;
    this.marcas = [];
    this._medicamento.getMarcasByIdMedicamento(id).subscribe(data => {
      this.marcas = Object(data)["data"]
      this.marcas.sort(function (a, b) {
        return a.marca.localeCompare(b.marca);
      });
      this.spinner_2 = false;
    }, error => {
      // console.log(error)
      this.spinner_2 = false;
    })
  }

  getFormaMarcaName(marca_id: number): Observable<string> {

    for (let index = 0; index < this.allFormas.length; index++) {
      if (this.allFormas[index].id == marca_id) {
        return this.allFormas[index].forma;
      }
    }
  }

  modalGrupo(op: number) {
    this.group_modal_option = op;
    switch (op) {
      case 1:
        this.spinner_grupo = true;
        this._grupo.getAllGrupoTerapeutico().subscribe(data => {
          this.grupos_terapeuticos = Object(data)["data"]
          this.grupos_terapeuticos.sort(function (a, b) {
            return a.nome.localeCompare(b.nome);
          });
          this.spinner_grupo = false;
        }, error => {
          // console.log(error)
          this.spinner_grupo = false;
        })
        break;

      case 2:
        this.form_grupos.reset();
        break;

      case 3:
        this.submitted = true;
        if (this.form_grupos.invalid) {
          return
        }
        this.spinner_grupo = true;
        this._grupo.registerGrupoTerapeutico(this.form_grupos.value).subscribe(data => {
          this.spinner_grupo = false;
          this.modalGrupo(1);
          this.form_grupos.reset();
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
            this.spinner_grupo = false;
          })
        break;

      case 4:
        this.submitted = true;
        if (this.form_grupos.invalid) {
          return
        }

        this.spinner_grupo = true;
        this._grupo.updateGrupoTerapeutico(this.form_grupos.value).subscribe(data => {
          this.spinner_grupo = false;
          this.modalGrupo(1);
          this.form_grupos.reset();
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
            this.spinner_grupo = false;
          })
        break;

      case 5:
        this.group_modal_option = 1;
        break;

      default:
        break;
    }
  }


  editCardGrupo(item) {
    this.form_grupos.setValue(item);
    this.group_modal_option = 3;
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
        this.spinner_grupo = true;
        this._grupo.deleteGrupoTerapeutico(row.id).subscribe(res => {
          this.spinner_grupo = false;
          this.modalGrupo(1);
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

  //=========== SUB- GRUPO =========================
  modalSubGrupo(op: number) {
    this.sub_group_modal_option = op;
    switch (op) {
      case 1:
        this.spinner_grupo = true;
        this._grupo.getAllSubGrupoTerapeutico().subscribe(data => {
          this.sub_grupos_terapeuticos = Object(data)["data"]
          this.sub_grupos_terapeuticos.sort(function (a, b) {
            return a.nome.localeCompare(b.nome);
          });
          this.spinner_grupo = false;
        }, error => {
          // console.log(error)
          this.spinner_grupo = false;
        })
        break;

      case 2:
        this.form_sub_grupos.reset();
        break;

      case 3:
        this.submitted = true;
        if (this.form_sub_grupos.invalid) {
          return
        }
        this.spinner_grupo = true;
        this._grupo.registerSubGrupoTerapeutico(this.form_sub_grupos.value).subscribe(data => {
          this.spinner_grupo = false;
          this.modalSubGrupo(1);
          this.form_sub_grupos.reset();
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
            this.spinner_grupo = false;
          })
        break;

      case 4:
        this.submitted = true;
        if (this.form_sub_grupos.invalid) {
          return
        }

        this.spinner_grupo = true;
        this._grupo.updateSubGrupoTerapeuticoo(this.form_sub_grupos.value).subscribe(data => {
          this.spinner_grupo = false;
          this.modalSubGrupo(1);
          this.form_sub_grupos.reset();
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
            this.spinner_grupo = false;
          })
        break;

      case 5:
        this.sub_group_modal_option = 1;
        break;

      default:
        break;
    }
  }


  editCardSubGrupo(item) {
    this.form_sub_grupos.setValue({
      id: item.id,
      nome: item.nome,
      grupo_medicamento_id: item.grupo_medicamentos.id
    });
    this.sub_group_modal_option = 3;
  }

  deleteSubGrupo(row) {
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
        this.spinner_grupo = true;
        this._grupo.deleteSubGrupoTerapeutico(row.id).subscribe(res => {
          this.spinner_grupo = false;
          this.modalSubGrupo(1);
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

  //=========== SUB-CLASSE ===========================
  modalSubClasse(op: number) {
    this.sub_class_modal_option = op;
    switch (op) {
      case 1:
        this.spinner_grupo = true;
        this._grupo.getAllSubClasseTerapeutico().subscribe(data => {
          this.sub_classes_terapeuticos = Object(data)["data"]
          this.sub_classes_terapeuticos.sort(function (a, b) {
            return a.nome.localeCompare(b.nome);
          });
          this.spinner_grupo = false;
        }, error => {
          // console.log(error)
          this.spinner_grupo = false;
        })
        break;

      case 2:
        this.form_sub_classes.reset();
        this.modalSubGrupo(1);
        break;

      case 3:
        this.submitted = true;
        if (this.form_sub_classes.invalid) {
          return
        }
        this.spinner_grupo = true;
        this._grupo.registerSubClasseTerapeutico(this.form_sub_classes.value).subscribe(data => {
          this.spinner_grupo = false;
          this.modalSubClasse(1);
          this.form_sub_classes.reset();
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
            this.spinner_grupo = false;
          })
        break;

      case 4:
        this.submitted = true;
        if (this.form_sub_classes.invalid) {
          return
        }

        this.spinner_grupo = true;
        this._grupo.updateSubClasseTerapeuticoo(this.form_sub_classes.value).subscribe(data => {
          this.spinner_grupo = false;
          this.modalSubClasse(1);
          this.form_sub_classes.reset();
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
            this.spinner_grupo = false;
          })
        break;

      case 5:
        this.sub_class_modal_option = 1;
        break;

      default:
        break;
    }
  }


  editCardSubClasse(item) {
    this.form_sub_classes.setValue({
      id: item.id,
      nome: item.nome,
      sub_grupo_medicamento_id: item.sub_grupo_medicamentos.id
    });
    this.sub_class_modal_option = 3;
  }

  deleteSubClasse(row) {
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
        this.spinner_grupo = true;
        this._grupo.deleteSubClasseTerapeutico(row.id).subscribe(res => {
          this.spinner_grupo = false;
          this.modalSubClasse(1);
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

  //=========== FORMA ===========================
  modalFormas(op: number) {
    this.form_modal_option = op;
    switch (op) {
      case 1:
        this.spinner_grupo = true;
        this._grupo.getAllFormas().subscribe(data => {
          this.formas_terapeutica = Object(data)["data"]
          this.formas_terapeutica.sort(function (a, b) {
            return a.forma.localeCompare(b.forma);
          });
          this.spinner_grupo = false;
        }, error => {
          // console.log(error)
          this.spinner_grupo = false;
        })
        break;

      case 2:
        this.form_formas.reset();
        break;

      case 3:
        this.submitted = true;
        if (this.form_formas.invalid) {
          return
        }
        this.spinner_grupo = true;
        this._grupo.registerFormas(this.form_formas.value).subscribe(data => {
          this.spinner_grupo = false;
          this.modalFormas(1);
          this.form_formas.reset();
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
            this.spinner_grupo = false;
          })
        break;

      case 4:
        this.submitted = true;
        if (this.form_formas.invalid) {
          return
        }

        this.spinner_grupo = true;
        this._grupo.updateFormas(this.form_formas.value).subscribe(data => {
          this.spinner_grupo = false;
          this.modalFormas(1);
          this.form_formas.reset();
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
            this.spinner_grupo = false;
          })
        break;

      case 5:
        this.form_modal_option = 1;
        break;

      default:
        break;
    }
  }


  editCardFormas(item) {
    this.form_formas.setValue({
      id: item.id,
      forma: item.forma
    });
    this.form_modal_option = 3;
  }

  deleteFormas(row) {
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
        this.spinner_grupo = true;
        this._grupo.deleteFormas(row.id).subscribe(res => {
          this.spinner_grupo = false;
          this.modalFormas(1);
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


  //=========== NOME GENERICO ===========================
  modalNomeGenerico(op: number) {
    this.nomeGenerico_modal_option = op;
    switch (op) {
      case 1:
        this.spinner_grupo = true;
        this._grupo.getAllNomeGenerico().subscribe(data => {
          this.nomes_Genericos = Object(data)["data"]
          this.nomes_Genericos.sort(function (a, b) {
            return a.nome.localeCompare(b.nome);
          });
          this.spinner_grupo = false;
        }, error => {
          // console.log(error)
          this.spinner_grupo = false;
        })
        break;
      case 2:
        this.form_nome_generico.reset();
        break;
      case 3:
        this.submitted = true;
        if (this.form_nome_generico.invalid) {
          return
        }
        this.spinner_grupo = true;
        this._grupo.registerNomeGenerico(this.form_nome_generico.value).subscribe(data => {
          this.spinner_grupo = false;
          this.modalNomeGenerico(1);
          this.form_nome_generico.reset();
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
            this.spinner_grupo = false;
          })
        break;
      case 4:
        this.submitted = true;
        if (this.form_nome_generico.invalid) {
          return
        }

        this.spinner_grupo = true;
        this._grupo.updateNomeGenerico(this.form_nome_generico.value).subscribe(data => {
          this.spinner_grupo = false;
          this.modalNomeGenerico(1);
          this.form_nome_generico.reset();
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
            this.spinner_grupo = false;
          })
        break;

      case 5:
        this.nomeGenerico_modal_option = 1;
        break;

      default:
        break;
    }
  }


  editCardNomeGenerico(item) {
    this.form_nome_generico.setValue({
      id: item.id,
      nome: item.nome
    });
    this.nomeGenerico_modal_option = 3;
  }

  deleteNomeGenerico(row) {
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
        this.spinner_grupo = true;
        this._grupo.deleteNomeGenerico(row.id).subscribe(res => {
          this.spinner_grupo = false;
          this.modalNomeGenerico(1);
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

  selectedGroup() {
    this.sub_grupos_medicamentos = [];
    // this.sub_classes_medicamentos = [];
    this.create_grupos_all.grupos_medicamentos.forEach(element => {
      if (element.id == this.form.value.grupo_medicamento_id) {
        this.sub_grupos_medicamentos = element.sub_grupos_medicamentos;
        this.selectedSubGroup()
      }

    });
  }

  selectedSubGroup() {
    this.sub_classes_medicamentos = [];
    this.sub_grupos_medicamentos.forEach(element => {
      if (element.id == this.form.value.sub_grupo_medicamento_id) {
        this.sub_classes_medicamentos = element.sub_classes_medicamentos;
      }
    });
  }

  displayFn(medicamento?: Medicamento): string | undefined {

    return medicamento ? medicamento.nome : undefined;
  }

  private _filter(nome: string): Medicamento[] {
    const filterValue = nome.toLowerCase();

    return this.options.filter(option => option.nome.toLowerCase().indexOf(filterValue) === 0);
  }


  dateformat(data: string | number | Date) {
    let date = new Date(data);
    let dia = date.getDate();
    let mes = date.getMonth() + 1;
    let ano = date.getFullYear();
    // console.log("::::::::::::::::::: - " + ano + "-" + mes + "-" + dia)
    return "" + ano + "-" + mes + "-" + dia
  }

}