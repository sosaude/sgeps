import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { URL } from '../API_CONFIG';

@Injectable({
  providedIn: 'root'
})
export class EmpresasService {

  constructor(private http: HttpClient) { }

  // EMPRESA
  getAll() {
    return this.http.get<any[]>(`${URL.url}/admin/empresas`);
  }
  getAllCategorias() {
    return this.http.get<any[]>(`${URL.url}/admin/empresas/create`);
  }

  getById(id: number) {
    return this.http.get(`${URL.url}/admin/empresas/${id}`);
  }

  register(empresa: any) {
    return this.http.post(`${URL.url}/admin/empresas`, empresa);
  }

  update(empresa: any) {
    return this.http.put(`${URL.url}/admin/empresas/${empresa.id}`, empresa);
  }

  delete(id: number) {
    return this.http.delete(`${URL.url}/admin/empresas/${id}`);
  }


  // ================== Utilizador EMPRESA ==============
  utilizadorgetAll() {
    return this.http.get<any[]>(`${URL.url}/admin/utilizador_empresa`);
  }
  utilizadorgetAllCategorias() {
    return this.http.get<any[]>(`${URL.url}/admin/utilizador_empresa/create`);
  }

  utilizadorgetById(id: number) {
    return this.http.get(`${URL.url}/admin/utilizador_empresa/${id}`);
  }

  utilizadorregister(empresa: any) {
    return this.http.post(`${URL.url}/admin/utilizador_empresa`, empresa);
  }

  utilizadorupdate(empresa: any) {
    return this.http.put(`${URL.url}/admin/utilizador_empresa/${empresa.id}`, empresa);
  }

  utilizadordelete(id: number) {
    return this.http.delete(`${URL.url}/admin/utilizador_empresa/${id}`);
  }

  //==========
  getUtilizadoresByEmpresaID(id: number) {
    return this.http.get(`${URL.url}/admin/empresas/${id}/utilizadores`);
  }
}