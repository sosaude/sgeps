import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { URL } from '../API_CONFIG';

@Injectable({
  providedIn: 'root'
})
export class ClinicasService {


  constructor(private http: HttpClient) { }


  // === CLINICAS ===
  getAll() {
    return this.http.get<any[]>(`${URL.url}/admin/unidades_sanitarias`);
  }

  getRoles() {
    return this.http.get<any[]>(`${URL.url}/admin/unidades_sanitarias/create`);
  }

  getById(id: number) {
    return this.http.get(`${URL.url}/admin/unidades_sanitarias/${id}`);
  }

  register(data: any) {
    return this.http.post(`${URL.url}/admin/unidades_sanitarias`, data);
  }

  update(data: any) {
    return this.http.put(`${URL.url}/admin/unidades_sanitarias/${data.id}`, data);
  }

  delete(id: number) {
    return this.http.delete(`${URL.url}/admin/unidades_sanitarias/${id}`);
  }

  // ================== Utilizador CLINICA ============== 
  utilizadorgetAll() {
    return this.http.get<any[]>(`${URL.url}/admin/utilizador_unidade_sanitaria`);
  }
  utilizadorgetAllCategorias() {
    return this.http.get<any[]>(`${URL.url}/admin/utilizador_unidade_sanitaria/create`);
  }

  utilizadorgetById(id: number) {
    return this.http.get(`${URL.url}/admin/unidades_sanitarias/${id}/utilizadores`);
  }

  utilizadorregister(empresa: any) {
    return this.http.post(`${URL.url}/admin/utilizador_unidade_sanitaria`, empresa);
  }

  utilizadorupdate(empresa: any) {
    return this.http.put(`${URL.url}/admin/utilizador_unidade_sanitaria/${empresa.id}`, empresa);
  }

  utilizadordelete(id: number) {
    return this.http.delete(`${URL.url}/admin/utilizador_unidade_sanitaria/${id}`);
  }
  //==============================
  getUtilizadoresByClinicaID(id: number) {
    return this.http.get(`${URL.url}/admin/unidades_sanitarias/${id}/utilizadores`);
  }


  // ======== UNIS. SANITÁRIA =============
  getUnidadeSanitaria() {
    return this.http.get<any[]>(`${URL.url}/uni_sanit/unidades_sanitarias/edit`);
  }

  updateUnidadeSanitaria(farmacia: any) {
    return this.http.put(`${URL.url}/uni_sanit/unidades_sanitarias/${farmacia.id}`, farmacia);
  }

  getRolesUnidadeSanitaria() {
    return this.http.get<any[]>(`${URL.url}/uni_sanit/utilizador_unidade_sanitaria/create`);
  }

}
