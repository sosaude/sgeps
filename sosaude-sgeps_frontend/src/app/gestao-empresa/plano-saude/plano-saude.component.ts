import { Component, OnInit, Input, Output, EventEmitter, ElementRef } from '@angular/core';
import { PlanoSaudeService } from 'src/app/_services/plano-saude.service';
import Swal from 'sweetalert2';
import { THIS_EXPR } from '@angular/compiler/src/output/output_ast';
import { HttpErrorResponse } from '@angular/common/http';
declare var jQuery: any;

@Component({
  selector: 'app-plano-saude',
  templateUrl: './plano-saude.component.html',
  styleUrls: ['./plano-saude.component.scss']
})
export class PlanoSaudeComponent implements OnInit {
  @Input() model_grupo_temp: any;
  @Output() closeChildComponent: EventEmitter<any> = new EventEmitter();
  searchText_grupo: any;
  searchText_servico: any;
  spinner_1: boolean = false;
  grupo_selected: any;
  model_cabecalho = {
    beneficio_anual_segurando_limitado: false,
    valor_limite_anual_segurando: null,
    limite_fora_area_cobertura: false,
    valor_limite_fora_area_cobertura: null,
    regiao_cobertura: [],
    grupo_beneficiario_id: null,
    plano_saude_id: null,
    padrao: null,
    grupos_medicamento_plano: [
      // {
      //   comparticipacao_factura: false,
      //   sujeito_limite_global: false,
      //   beneficio_ilimitado: false,
      //   valor_beneficio_limitado: null,
      //   valor_comparticipacao_factura: null,
      //   grupo_medicamento_id: null,
      //   medicamentos: []
      // }
    ],
    categorias_servico_plano: [
      // {
      //   comparticipacao_factura: false,
      //   sujeito_limite_global: false,
      //   beneficio_ilimitado: false,
      //   valor_beneficio_limitado: null,
      //   valor_comparticipacao_factura: null,
      //   categoria_servico_id: null,
      //   servicos: []
      // }
    ]
  };

  insertLimiteBoolean: boolean = false;
  plano_data_create: any;
  continent_ids: any[] = [];
  allPaisesSelected: boolean;
  allMedicamentosSelected: boolean;
  insertLimiteBeneficioBoolean: boolean;
  insertLimiteComparticipacaoBoolean: boolean;
  conjunto_grupo_index: number = 0;
  shownToogleNumber: number = 0;
  allServicosSelected: boolean;
  shownToogleNumber_servicos: number = 0;
  insertLimiteBeneficioBoolean_servico: boolean;
  insertLimiteComparticipacaoBoolean_servico: boolean;
  plano_padrao_notExist: boolean = false;
  plano_padrao_byGrupo_notExist: boolean = false;

  constructor(private _pl: PlanoSaudeService, private el: ElementRef) { }

  ngOnInit() {
    this.getCreatePlano();
    // (function ($) {
    //   // $('#planoSaudeServicos').on('hidden.bs.modal', function (e) {
    //   //   alert('Modal is successfully shown!');
    //   // });

    // })(jQuery);
  }

  backToGrupos() {
    this.closeChildComponent.emit(1);
  }

  getCreatePlano() {
    this._pl.getCreate().subscribe(data => {
      this.plano_data_create = Object(data)["data"];
      // console.log(this.plano_data_create);
      
      this.mergePlanoCreateWithPlanoSaude();
      if (this.model_grupo_temp.id) {
        this.getPlanoByGrupoID(this.model_grupo_temp.id);
      } else {
        this.getPlanoPadrao();
      }
    })
  }

  //=========  MERGE PLANO_CREATE WITH PLANO SAUDE ====================
  mergePlanoCreateWithPlanoSaude() {
    this.model_cabecalho.beneficio_anual_segurando_limitado = false
    this.model_cabecalho.valor_limite_anual_segurando = null
    this.model_cabecalho.limite_fora_area_cobertura = false
    this.model_cabecalho.valor_limite_fora_area_cobertura = null
    this.model_cabecalho.regiao_cobertura = []
    this.model_cabecalho.grupo_beneficiario_id = null
    this.model_cabecalho.plano_saude_id = null
    this.model_cabecalho.grupos_medicamento_plano = []
    this.model_cabecalho.categorias_servico_plano = []

    // ADICIONAR SERVICOS QUE ESTÃO NO OBJECTO CREATE
    this.plano_data_create.categorias_servicos.forEach((element, index) => {
      let element_main = element;
      let element_main_index = index;
      let array_insert_servicos: any[] = [];
      element.servicos.forEach(element => {
        array_insert_servicos.push({
          id: element.id,
          coberto: false,
          pre_autorizacao: false
        });
      });
      this.model_cabecalho.categorias_servico_plano.push({
        comparticipacao_factura: false,
        sujeito_limite_global: false,
        beneficio_ilimitado: false,
        valor_beneficio_limitado: 0,
        valor_comparticipacao_factura: null,
        categoria_servico_id: element_main.id,
        servicos: array_insert_servicos
      })
    });

    // ADICIONAR MEDICAMENTOS QUE ESTÃO NO OBJECTO CREATE~
    this.plano_data_create.grupos_medicamentos.forEach((element, index) => {
      let element_main = element;
      let element_main_index = index;
      let array_insert_medicamentos: any[] = [];
      element_main.sub_grupos_medicamentos.forEach(element => {
        element.medicamentos.forEach(element => {
          array_insert_medicamentos.push({
            id: element.id,
            coberto: false,
            pre_autorizacao: false
          });
        });
      });

      this.model_cabecalho.grupos_medicamento_plano.push({
        comparticipacao_factura: false,
        sujeito_limite_global: false,
        beneficio_ilimitado: false,
        valor_beneficio_limitado: 0,
        valor_comparticipacao_factura: null,
        grupo_medicamento_id: element_main.id,
        id: element.id,
        medicamentos: array_insert_medicamentos
      })
    });

    // console.log(this.model_cabecalho);
  }

  getPlanoPadrao() {

    this._pl.getPlanoSaude().subscribe(data => {
      let res = Object(data)["data"];
      if (res.plano_saude) {
        this.plano_padrao_notExist = false;
        let all_plano = res.plano_saude;
        // console.log("PLANO DE SAUDE PADRAO")
        // console.log(all_plano);

        this.model_cabecalho.beneficio_anual_segurando_limitado = all_plano.beneficio_anual_segurando_limitado;
        this.model_cabecalho.grupo_beneficiario_id = all_plano.grupo_beneficiario_id;
        this.model_cabecalho.plano_saude_id = all_plano.id;
        this.model_cabecalho.limite_fora_area_cobertura = all_plano.limite_fora_area_cobertura;
        all_plano.regiao_cobertura.forEach(element => {
          this.model_cabecalho.regiao_cobertura.push(element.id);
        });
        this.model_cabecalho.valor_limite_anual_segurando = all_plano.valor_limite_anual_segurando;
        this.model_cabecalho.valor_limite_fora_area_cobertura = all_plano.valor_limite_fora_area_cobertura;

        // MEDICAMENTOS => grupos_medicamento_plano
        all_plano.grupos_medicamento_plano.forEach((element, index) => {
          let main_element = element
          this.model_cabecalho.grupos_medicamento_plano.forEach((element, index) => {
            let grupo_especifico_element = element;
            if (main_element.grupo_medicamento_id == element.grupo_medicamento_id) {
              this.model_cabecalho.grupos_medicamento_plano[index].beneficio_ilimitado = main_element.beneficio_ilimitado;
              this.model_cabecalho.grupos_medicamento_plano[index].comparticipacao_factura = main_element.comparticipacao_factura;
              this.model_cabecalho.grupos_medicamento_plano[index].id = main_element.id;
              this.model_cabecalho.grupos_medicamento_plano[index].sujeito_limite_global = main_element.sujeito_limite_global;
              this.model_cabecalho.grupos_medicamento_plano[index].valor_beneficio_limitado = main_element.valor_beneficio_limitado;
              this.model_cabecalho.grupos_medicamento_plano[index].valor_comparticipacao_factura = main_element.valor_comparticipacao_factura;

              main_element.medicamentos.forEach(element => {
                let med_element = element;
                grupo_especifico_element.medicamentos.forEach((element, index) => {
                  if (med_element.id == element.id) {
                    grupo_especifico_element.medicamentos[index].id = med_element.id;
                    grupo_especifico_element.medicamentos[index].coberto = med_element.coberto;
                    grupo_especifico_element.medicamentos[index].pre_autorizacao = med_element.pre_autorizacao;
                  }
                });
              });
            }
          })
        });


        // SERVICOS => categorias_servico_plano
        all_plano.categorias_servico_plano.forEach((element, index) => {
          let main_element = element
          this.model_cabecalho.categorias_servico_plano.forEach((element, index) => {
            let grupo_especifico_element = element;
            if (main_element.categoria_servico_id == element.categoria_servico_id) {
              this.model_cabecalho.categorias_servico_plano[index].beneficio_ilimitado = main_element.beneficio_ilimitado;
              this.model_cabecalho.categorias_servico_plano[index].comparticipacao_factura = main_element.comparticipacao_factura;
              this.model_cabecalho.categorias_servico_plano[index].id = main_element.id;
              this.model_cabecalho.categorias_servico_plano[index].sujeito_limite_global = main_element.sujeito_limite_global;
              this.model_cabecalho.categorias_servico_plano[index].valor_beneficio_limitado = main_element.valor_beneficio_limitado;
              this.model_cabecalho.categorias_servico_plano[index].valor_comparticipacao_factura = main_element.valor_comparticipacao_factura;

              main_element.servicos.forEach(element => {
                let ser_element = element;
                grupo_especifico_element.servicos.forEach((element, index) => {
                  if (ser_element.id == element.id) {
                    grupo_especifico_element.servicos[index].id = ser_element.id;
                    grupo_especifico_element.servicos[index].coberto = ser_element.coberto;
                    grupo_especifico_element.servicos[index].pre_autorizacao = ser_element.pre_autorizacao;
                  }
                });
              });
            }
          })
        });
        // console.log(this.model_cabecalho);
      }
      else {
        this.plano_padrao_notExist = true;
      }
    })
  }

  getPlanoByGrupoID(grupo_id) {

    this._pl.getByGrupoId(grupo_id).subscribe(data => {
      let res = Object(data)["data"]
      this.plano_padrao_byGrupo_notExist = false;
      if (res.plano_saude) {
        this.plano_padrao_notExist = false;
        let all_plano = res.plano_saude;
        this.model_cabecalho.padrao = res.padrao;
        this.model_cabecalho.grupo_beneficiario_id = all_plano.grupo_beneficiario_id;
        this.model_cabecalho.beneficio_anual_segurando_limitado = all_plano.beneficio_anual_segurando_limitado;
        if (res.padrao) {
          this.model_cabecalho.plano_saude_id = null;
        } else {
          this.model_cabecalho.plano_saude_id = all_plano.id;
        }
        this.model_cabecalho.limite_fora_area_cobertura = all_plano.limite_fora_area_cobertura;
        all_plano.regiao_cobertura.forEach(element => {
          this.model_cabecalho.regiao_cobertura.push(element.id);
        });
        this.model_cabecalho.valor_limite_anual_segurando = all_plano.valor_limite_anual_segurando;
        this.model_cabecalho.valor_limite_fora_area_cobertura = all_plano.valor_limite_fora_area_cobertura;

        // MEDICAMENTOS => grupos_medicamento_plano
        all_plano.grupos_medicamento_plano.forEach((element, index) => {
          let main_element = element
          this.model_cabecalho.grupos_medicamento_plano.forEach((element, index) => {
            let grupo_especifico_element = element;
            if (main_element.grupo_medicamento_id == element.grupo_medicamento_id) {
              this.model_cabecalho.grupos_medicamento_plano[index].beneficio_ilimitado = main_element.beneficio_ilimitado;
              this.model_cabecalho.grupos_medicamento_plano[index].comparticipacao_factura = main_element.comparticipacao_factura;
              this.model_cabecalho.grupos_medicamento_plano[index].id = main_element.id;
              this.model_cabecalho.grupos_medicamento_plano[index].sujeito_limite_global = main_element.sujeito_limite_global;
              this.model_cabecalho.grupos_medicamento_plano[index].valor_beneficio_limitado = main_element.valor_beneficio_limitado;
              this.model_cabecalho.grupos_medicamento_plano[index].valor_comparticipacao_factura = main_element.valor_comparticipacao_factura;

              main_element.medicamentos.forEach(element => {
                let med_element = element;
                grupo_especifico_element.medicamentos.forEach((element, index) => {
                  if (med_element.id == element.id) {
                    grupo_especifico_element.medicamentos[index].id = med_element.id;
                    grupo_especifico_element.medicamentos[index].coberto = med_element.coberto;
                    grupo_especifico_element.medicamentos[index].pre_autorizacao = med_element.pre_autorizacao;
                  }
                });
              });
            }
          })
        });

        // SERVICOS => categorias_servico_plano
        all_plano.categorias_servico_plano.forEach((element, index) => {
          let main_element = element
          this.model_cabecalho.categorias_servico_plano.forEach((element, index) => {
            let grupo_especifico_element = element;
            if (main_element.categoria_servico_id == element.categoria_servico_id) {
              this.model_cabecalho.categorias_servico_plano[index].beneficio_ilimitado = main_element.beneficio_ilimitado;
              this.model_cabecalho.categorias_servico_plano[index].comparticipacao_factura = main_element.comparticipacao_factura;
              this.model_cabecalho.categorias_servico_plano[index].id = main_element.id;
              this.model_cabecalho.categorias_servico_plano[index].sujeito_limite_global = main_element.sujeito_limite_global;
              this.model_cabecalho.categorias_servico_plano[index].valor_beneficio_limitado = main_element.valor_beneficio_limitado;
              this.model_cabecalho.categorias_servico_plano[index].valor_comparticipacao_factura = main_element.valor_comparticipacao_factura;

              main_element.servicos.forEach(element => {
                let ser_element = element;
                grupo_especifico_element.servicos.forEach((element, index) => {
                  if (ser_element.id == element.id) {
                    grupo_especifico_element.servicos[index].id = ser_element.id;
                    grupo_especifico_element.servicos[index].coberto = ser_element.coberto;
                    grupo_especifico_element.servicos[index].pre_autorizacao = ser_element.pre_autorizacao;
                  }
                });
              });
            }
          })
        });

      }
      else {
        this.plano_padrao_byGrupo_notExist = true;
        Swal.fire({
          title: 'Plano de Saúde Principal inexistente! Configure primeiro o Plano de Saúde Principal!',
          text: "",
          type: 'warning',
          confirmButtonColor: "#f15726",
          confirmButtonText: 'Sim',
          reverseButtons: true
        }).then((result) => {
          if (result.value) {
            this.backToGrupos();
          }
        })
      }
    }, (error: any) => {
      // console.log(error);
      this.backToGrupos();

    })
  }

  // =========== LIMITE =========================
  guardarLimite(option) {
    switch (option) {
      case 1: // limite Global
        if (this.model_cabecalho.beneficio_anual_segurando_limitado) {
          if (!this.model_cabecalho.valor_limite_anual_segurando) {
            this.insertLimiteBoolean = true;
            return
          } else {
            document.getElementById('guardarLimiteGlobalID').click();
          }
        } else {
          document.getElementById('guardarLimiteGlobalID').click();
        }
        break;
      case 2: //limite fora de area
        if (this.model_cabecalho.limite_fora_area_cobertura) {
          if (!this.model_cabecalho.valor_limite_fora_area_cobertura) {
            this.insertLimiteBoolean = true;
            return
          } else {
            document.getElementById('guardarLimiteGlobalID_1').click();
          }
        } else {
          document.getElementById('guardarLimiteGlobalID_1').click();
        }
        break;
    }
  }

  changeStateLimit(event, option) {
    switch (option) {
      case 1:
        if (event.checked) {
          if (!this.model_cabecalho.valor_limite_anual_segurando) {
            this.insertLimiteBoolean = true;
          } else {
            this.insertLimiteBoolean = false;
          }
        } else {
          this.insertLimiteBoolean = false;
        }
        break;
      case 2:
        if (event.checked) {
          if (!this.model_cabecalho.valor_limite_fora_area_cobertura) {
            this.insertLimiteBoolean = true;
          } else {
            this.insertLimiteBoolean = false;
          }
        } else {
          this.insertLimiteBoolean = false;
        }
        break;
    }
  }

  inputChange_limite(event, option) {
    switch (option) {
      case 1:
        if (this.model_cabecalho.beneficio_anual_segurando_limitado) {
          if (!this.model_cabecalho.valor_limite_anual_segurando) {
            this.insertLimiteBoolean = true;
          } else {
            this.insertLimiteBoolean = false;
          }
        } else {
          this.insertLimiteBoolean = false;
        }
        break;

      case 2:
        if (this.model_cabecalho.limite_fora_area_cobertura) {
          if (!this.model_cabecalho.valor_limite_fora_area_cobertura) {
            this.insertLimiteBoolean = true;
          } else {
            this.insertLimiteBoolean = false;
          }
        } else {
          this.insertLimiteBoolean = false;
        }
        break;
    }
  }

  // ========= PAISES =============
  changeStatePaises(event, label, option_id) {

    switch (label) {
      case 'todos':
        if (event.checked) {
          this.allPaisesSelected = true;
          this.model_cabecalho.regiao_cobertura = [];
          this.plano_data_create.continentes.forEach(element => {
            this.continent_ids.push(element.id);
            element.paises.forEach(element => {
              this.model_cabecalho.regiao_cobertura.push(element.id)
            });
          });
        } else if (!event.checked) {
          this.allPaisesSelected = false;
          this.model_cabecalho.regiao_cobertura = [];
          this.continent_ids = [];
        }
        break;

      case 'alguns':
        if (event.checked) {
          this.plano_data_create.continentes.forEach((element, index) => {
            if (element.id == option_id) {
              this.continent_ids.push(element.id);
              element.paises.forEach(element => {
                this.model_cabecalho.regiao_cobertura.push(element.id)
              });
            }
          });
        } else if (!event.checked) {
          this.allPaisesSelected = false;
          this.continent_ids.forEach((element, index) => {
            if (element == option_id) {
              this.continent_ids.splice(index, 1)
            }
          });
          this.plano_data_create.continentes.forEach(element => {
            if (element.id == option_id) {
              element.paises.forEach(element => {
                let value = element.id;
                this.model_cabecalho.regiao_cobertura.forEach((element, index) => {
                  if (element == value) {
                    this.model_cabecalho.regiao_cobertura.splice(index, 1)
                  }
                });
              });
            }
          });
        }
        break;

      case 'especifico':
        if (event.checked) {
          this.model_cabecalho.regiao_cobertura.push(option_id)
        } else if (!event.checked) {
          this.allPaisesSelected = false;
          this.model_cabecalho.regiao_cobertura.forEach((element, index) => {
            if (element == option_id) {
              this.model_cabecalho.regiao_cobertura.splice(index, 1)
            }
          });
        }
        // console.log(this.model_cabecalho.regiao_cobertura);

        break;

    }
  }

  statePais(pais_id) {
    return this.model_cabecalho.regiao_cobertura.includes(pais_id)
  }
  stateContinent(continent_id) {
    return this.continent_ids.includes(continent_id)
  }


  // ============ MEDICAMENTOS =========================== 
  selectedGrupo(item) {
    this.grupo_selected = item;
    // console.log('Grupo selecionado',this.grupo_selected);

    this.model_cabecalho.grupos_medicamento_plano.forEach((element, index) => {
      if (element.grupo_medicamento_id == this.grupo_selected.id) {
        this.conjunto_grupo_index = index;
        this.shownToogleNumber = index + 1;
      }
    })
    // console.log(this.model_cabecalho.grupos_medicamento_plano);

  }

  changeStateMedicamento(event, label, option_id) {
    switch (label) {
      case 'todos':
        if (event.checked) {
          this.allMedicamentosSelected = true;
          this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].medicamentos.forEach((element, index) => {
            this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].medicamentos[index].coberto = true;
          })

        } else if (!event.checked) {
          this.allMedicamentosSelected = false;
          this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].medicamentos.forEach((element, index) => {
            this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].medicamentos[index].coberto = false;
          })
        }
        break;

      case 'alguns':
        if (event.checked) {
          if (option_id == 1) {
            this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].sujeito_limite_global = false;
            this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].valor_beneficio_limitado = null;
            this.insertLimiteBeneficioBoolean = false;

          } else if (option_id == 2) {
            this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].beneficio_ilimitado = false;
            // this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].comparticipacao_factura = false;
            this.insertLimiteBeneficioBoolean = false;
            this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].valor_beneficio_limitado = this.model_cabecalho.valor_limite_anual_segurando;
            // this.insertLimiteComparticipacaoBoolean = false;
          }
          else if (option_id == 3) {
            // this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].beneficio_ilimitado = false;
            // this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].sujeito_limite_global = false;
            if (!this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].valor_comparticipacao_factura) {
              this.insertLimiteComparticipacaoBoolean = true;
              // this.insertLimiteBeneficioBoolean = false;
            } else {
              // this.insertLimiteBeneficioBoolean = false;
              this.insertLimiteComparticipacaoBoolean = false;
            }
          }
        } else if (!event.checked) {
          if (option_id == 1) {
            // this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].valor_beneficio_limitado = null;
            if (this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].valor_beneficio_limitado) {
              this.insertLimiteBeneficioBoolean = false;
            } else {
              this.insertLimiteBeneficioBoolean = true;
            }
          } else if (option_id == 2) {
            this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].valor_beneficio_limitado = null;
            this.insertLimiteBeneficioBoolean = true;
          }
          else if (option_id == 3) {
            this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].valor_comparticipacao_factura = null;
            this.insertLimiteComparticipacaoBoolean = false;
          }
        }
        break;

      case 'especifico':
        if (event.checked) {
          this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].medicamentos.forEach((element, index) => {
            if (element.id == option_id) {
              this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].medicamentos[index].coberto = true;
            }
          })

        } else if (!event.checked) {
          this.allMedicamentosSelected = false;
          this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].medicamentos.forEach((element, index) => {
            if (element.id == option_id) {
              this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].medicamentos[index].coberto = false;
            }
          })
        }
        break;

      case 'pre-autorizacao':
        if (event.checked) {
          this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].medicamentos.forEach((element, index) => {
            if (element.id == option_id) {
              this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].medicamentos[index].pre_autorizacao = true;
            }
          })
        } else if (!event.checked) {
          this.allMedicamentosSelected = false;
          this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].medicamentos.forEach((element, index) => {
            if (element.id == option_id) {
              this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].medicamentos[index].pre_autorizacao = false;
            }
          });
        }
        break;
    }
  }

  stateMedicamento(medicamento_id) {
    return this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].medicamentos.some(element => element['id'] === medicamento_id && element['coberto'] === true)
  }

  stateMedicamentoPreAutorizacao(medicamento_id) {
    return this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].medicamentos.some(element => element['id'] === medicamento_id && element['pre_autorizacao'] === true)
  }

  inputChange_valorLimite(event, option) {
    switch (option) {
      case 1:
        if (this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].beneficio_ilimitado) {
          if (!this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].valor_beneficio_limitado) {
            this.insertLimiteBeneficioBoolean = true;
          } else {
            this.insertLimiteBeneficioBoolean = false;
          }
        } else {
          this.insertLimiteBeneficioBoolean = false;
        }
        break;

      case 2:
        if (this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].comparticipacao_factura) {
          if (!this.model_cabecalho.grupos_medicamento_plano[this.conjunto_grupo_index].valor_comparticipacao_factura) {
            this.insertLimiteComparticipacaoBoolean = true;
          } else {
            this.insertLimiteComparticipacaoBoolean = false;
          }
        } else {
          this.insertLimiteComparticipacaoBoolean = false;
        }
        break;
    }
  }

  verifyMedicamentoArrayOfObject(grupo_medicamento_id) {
    let status: boolean = false;
    this.model_cabecalho.grupos_medicamento_plano.forEach((element, index) => {
      if (grupo_medicamento_id == element.grupo_medicamento_id) {
        status = element.medicamentos.some(element => element['coberto'] === true || element['pre_autorizacao'] === true)
      }
    });
    return status;
  }



  // ============ SERVIÇOS =========================== 

  selectedGrupoServicoes(item) {
    this.grupo_selected = item;

    this.model_cabecalho.categorias_servico_plano.forEach((element, index) => {
      if (element.categoria_servico_id == this.grupo_selected.id) {
        this.conjunto_grupo_index = index;
        this.shownToogleNumber_servicos = index + 1;
      }
    })
  }

  changeStateServico(event, label, option_id) {
    switch (label) {
      case 'todos':
        if (event.checked) {
          this.allServicosSelected = true;
          this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos.forEach((element, index) => {
            this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos[index].coberto = true;
          })

        } else if (!event.checked) {
          this.allServicosSelected = false;
          this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos.forEach((element, index) => {
            this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos[index].coberto = false;
          })
        }
        break;

      case 'alguns':
        if (event.checked) {
          if (option_id == 1) {
            this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].sujeito_limite_global = false;
            this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].valor_beneficio_limitado = null;
            this.insertLimiteBeneficioBoolean_servico = false;

          } else if (option_id == 2) {
            this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].beneficio_ilimitado = false;
            this.insertLimiteBeneficioBoolean_servico = false;
            this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].valor_beneficio_limitado = this.model_cabecalho.valor_limite_anual_segurando;
          }
          else if (option_id == 3) {
            if (!this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].valor_comparticipacao_factura) {
              this.insertLimiteComparticipacaoBoolean_servico = true;
            } else {
              this.insertLimiteComparticipacaoBoolean_servico = false;
            }
          }
        } else if (!event.checked) {
          if (option_id == 1) {
            if (this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].valor_beneficio_limitado) {
              this.insertLimiteBeneficioBoolean_servico = false;
            } else {
              this.insertLimiteBeneficioBoolean_servico = true;
            }
          } else if (option_id == 2) {
            this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].valor_beneficio_limitado = null;
            this.insertLimiteBeneficioBoolean_servico = true;
          }
          else if (option_id == 3) {
            this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].valor_comparticipacao_factura = null;
            this.insertLimiteComparticipacaoBoolean_servico = false;
          }
        }
        break;

      case 'especifico':
        if (event.checked) {
          this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos.forEach((element, index) => {
            if (element.id == option_id) {
              this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos[index].coberto = true;
            }
          })

        } else if (!event.checked) {
          this.allServicosSelected = false;
          this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos.forEach((element, index) => {
            if (element.id == option_id) {
              this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos[index].coberto = false;
            }
          })
        }
        break;

      case 'pre-autorizacao':
        if (event.checked) {
          this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos.forEach((element, index) => {
            if (element.id == option_id) {
              this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos[index].pre_autorizacao = true;
            }
          })
        } else if (!event.checked) {
          this.allServicosSelected = false;
          this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos.forEach((element, index) => {
            if (element.id == option_id) {
              this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos[index].pre_autorizacao = false;
            }
          });
        }
        // console.log(this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos);
        break;
    }
  }

  stateServico(servico_id) {

    return this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos.some(element => element['id'] === servico_id && element['coberto'] === true)
  }

  stateServicoPreAutorizacao(servico_id) {
    return this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].servicos.some(element => element['id'] === servico_id && element['pre_autorizacao'] === true)
  }

  inputChange_valorLimiteServico(event, option) {
    switch (option) {
      case 1:
        if (this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].beneficio_ilimitado) {
          if (!this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].valor_beneficio_limitado) {
            this.insertLimiteBeneficioBoolean_servico = true;
          } else {
            this.insertLimiteBeneficioBoolean_servico = false;
          }
        } else {
          this.insertLimiteBeneficioBoolean_servico = false;
        }
        break;

      case 2:
        if (this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].comparticipacao_factura) {
          if (!this.model_cabecalho.categorias_servico_plano[this.conjunto_grupo_index].valor_comparticipacao_factura) {
            this.insertLimiteComparticipacaoBoolean_servico = true;
          } else {
            this.insertLimiteComparticipacaoBoolean_servico = false;
          }
        } else {
          this.insertLimiteComparticipacaoBoolean_servico = false;
        }
        break;
    }
  }


  verifyServicoArrayOfObject(categoria_servico_id) {
    let status: boolean = false;
    this.model_cabecalho.categorias_servico_plano.forEach((element, index) => {
      if (categoria_servico_id == element.categoria_servico_id) {

        status = element.servicos.some(element => element['coberto'] === true || element['pre_autorizacao'] === true)
      }
    });
    return status;
  }


  // =========== SUBMISSÃO ==================
  submeterPlano() {

    // console.log(this.model_cabecalho);

    Swal.fire({
      title: 'Tem certeza que deseja submeter?',
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
        if (this.model_grupo_temp.id) {
          this.model_cabecalho.grupo_beneficiario_id = this.model_grupo_temp.id;

          this._pl.storePlanoSaudeByGrupo(this.model_cabecalho).subscribe(res => {
            // console.log(res);
            this.spinner_1 = false;
            Swal.fire({
              title: 'Submetido.',
              text: "Plano submetido com sucesso.",
              type: 'success',
              showCancelButton: false,
              confirmButtonColor: "#f15726",
              confirmButtonText: 'Ok'
            })
            this.mergePlanoCreateWithPlanoSaude();
            this.getPlanoByGrupoID(this.model_grupo_temp.id)
            this.backToGrupos();
          },
            error => {
              this.spinner_1 = false;

            });
        }
        else {
          this.model_cabecalho.grupo_beneficiario_id = null;
          this.model_cabecalho.padrao = true;
          this._pl.storePlanoSaude(this.model_cabecalho).subscribe(res => {
            // console.log(res);
            this.spinner_1 = false;
            Swal.fire({
              title: 'Submetido.',
              text: "Plano submetido com sucesso.",
              type: 'success',
              showCancelButton: false,
              confirmButtonColor: "#f15726",
              confirmButtonText: 'Ok'
            })
            this.mergePlanoCreateWithPlanoSaude();
            this.getPlanoPadrao()
            this.backToGrupos();
          },
            error => {
              this.spinner_1 = false;
            });
        }
      }
    })
  }

  // =========== REDEFINIR PLANO ==================
  redefinirPlano() {
    Swal.fire({
      title: 'Tem certeza que deseja submeter?',
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
        let model = {
          grupo_beneficiario_id: this.model_grupo_temp.id,
          plano_saude_id: this.model_cabecalho.plano_saude_id
        }

        // console.log(model);
        
        this._pl.redefinirPlanoSaudeByGrupo(model).subscribe(res => {
          // console.log(res);
          this.spinner_1 = false;
          Swal.fire({
            title: 'Submetido.',
            text: "Plano submetido com sucesso.",
            type: 'success',
            showCancelButton: false,
            confirmButtonColor: "#f15726",
            confirmButtonText: 'Ok'
          })
          this.mergePlanoCreateWithPlanoSaude();
          this.getPlanoByGrupoID(this.model_grupo_temp.id)
        },
          error => {
            this.spinner_1 = false;
          });
      }
    })
  }
}
