import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { URL } from '../API_CONFIG';

@Injectable({
  providedIn: 'root'
})
export class ConfiguracoesEmpresaService {

  constructor(private http: HttpClient) { }

  // ============ Farmácias ========================
  getAssociarFarmacias() {
    return this.http.get<any[]>(`${URL.url}/empresa/farmacias/todas`);
  }

  associarFarmaciasEmpresa(data: any) {
    let model = { farmacias: data }
    return this.http.post(`${URL.url}/empresa/farmacias`, model);
  }

  getFarmaciasAssociadas() {
    return this.http.get(`${URL.url}/empresa/farmacias`);
  }

  desassociarFarmacias(data) {
    let model = { farmacias: data }
    return this.http.post(`${URL.url}/empresa/farmacias/desassociar`, model);
  }

  // ============ Clínicas ========================

  getAssociarClinicas() {
    return this.http.get<any[]>(`${URL.url}/empresa/unidades_sanitarias/todas`);
  }

  associarClinicasEmpresa(data: any) {
    let model = { unidades_sanitarias: data }
    return this.http.post(`${URL.url}/empresa/unidades_sanitarias`, model);
  }

  getClinicasAssociadas() {
    return this.http.get(`${URL.url}/empresa/unidades_sanitarias`);
  }

  desassociarClinica(data) {
    let model = { unidades_sanitarias: data }
    return this.http.post(`${URL.url}/empresa/unidades_sanitarias/desassociar`, model);
  }

}
