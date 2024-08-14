import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { URL } from '../API_CONFIG';

@Injectable({
  providedIn: 'root'
})
export class PlanoSaudeService {

  constructor(private http: HttpClient) { }

    // === PLANO DE SAÚDE ===
    getCreate() {
      return this.http.get<any[]>(`${URL.url}/empresa/plano_saude/create`);
  }
  
  storePlanoSaude(data: any) {
      return this.http.post(`${URL.url}/empresa/plano_saude/padrao`, data);
  }

  getPlanoSaude() {
      return this.http.get(`${URL.url}/empresa/plano_saude/padrao`);
  }

  // CONFIGUAR O PLANO DE SAÚDE PARA UM GRUPO ESPECÍFICO (SALVAR OU ACTUALIZAR O PLANO)
  storePlanoSaudeByGrupo(data: any) {
      return this.http.post(`${URL.url}/empresa/plano_saude/configurar`, data);
  }

  // REDEFINIR PLANO DE SAÚDE DE UM GRUPO ESPECÍFICO
  redefinirPlanoSaudeByGrupo(data: any) {
      return this.http.post(`${URL.url}/empresa/plano_saude/redefinir`, data);
  }

  // getByPlanoId(id: number) {
  //     return this.http.get(`${URL.url}/empresa/plano_saude/${id}/edit`);
  // }
    
  // RECURSOS PARA CONFIGURAR O PLANO DE SAÚDE PARA UM GRUPO ESPECÍFICO (DEVOLVE O 
  // PLANO DE SAÚDE DO GRUPO INFORMADO, CASO EXISTA, OU DEVOLVE O PLANO PADRÃO, CASO NÃO EXISTA O PLANO PARA O GRUPO INFORMADO)
  getByGrupoId(grupo_beneficiario_id: number) {
      return this.http.get(`${URL.url}/empresa/plano_saude/configurar/${grupo_beneficiario_id}`);
  }

}
