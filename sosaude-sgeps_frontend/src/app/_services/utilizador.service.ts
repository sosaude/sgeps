import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import {URL} from '../API_CONFIG';

@Injectable({
  providedIn: 'root'
})
export class UtilizadorService {

  constructor(private http: HttpClient) { }


  // === ADMIN ===
  getAll() {
      return this.http.get<any[]>(`${URL.url}/admin/utilizador_admin`);
  }
  getRoles() {
      return this.http.get<any[]>(`${URL.url}/admin/utilizador_admin/create`);
  }

  getById(id: number) {
      return this.http.get(`${URL.url}/admin/utilizador_admin/${id}`);
  }

  register(data: any) {
      return this.http.post(`${URL.url}/admin/utilizador_admin`, data);
  }

  update(data: any) {
      return this.http.put(`${URL.url}/admin/utilizador_admin/${data.id}`, data);
  }

  delete(id: number) {
      return this.http.delete(`${URL.url}/admin/utilizador_admin/${id}`);
  } 


  // === EMPRESAS ===
  getAllUtilizadoresEmpresa() {
      return this.http.get<any[]>(`${URL.url}/empresa/utilizador_empresa`);
  }
  getRolesUtilizadoresEmpresa() {
      return this.http.get<any[]>(`${URL.url}/empresa/utilizador_empresa/create`);
  }

  getByIdUtilizadoresEmpresa(id: number) {
      return this.http.get(`${URL.url}/empresa/utilizador_empresa/${id}`);
  }

  registerUtilizadoresEmpresa(data: any) {
      return this.http.post(`${URL.url}/empresa/utilizador_empresa`, data);
  }

  updateUtilizadoresEmpresa(data: any) {
      return this.http.put(`${URL.url}/empresa/utilizador_empresa/${data.id}`, data);
  }

  deleteUtilizadoresEmpresa(id: number) {
      return this.http.delete(`${URL.url}/empresa/utilizador_empresa/${id}`);
  } 


  // === FARMÁCIA ===
  getAllUtilizadoresFarmacia() {
      return this.http.get<any[]>(`${URL.url}/farm/utilizador_farmacia`);
  }
  getRolesUtilizadoresFarmacia() {
      return this.http.get<any[]>(`${URL.url}/farm/utilizador_farmacia/create`);
  }

  getByIdUtilizadoresFarmacia(id: number) {
      return this.http.get(`${URL.url}/farm/utilizador_farmacia//${id}`);
  }

  registerUtilizadoresFarmacia(data: any) {
      return this.http.post(`${URL.url}/farm/utilizador_farmacia`, data);
  }

  updateUtilizadoresFarmacia(data: any) {
      return this.http.put(`${URL.url}/farm/utilizador_farmacia/${data.id}`, data);
  }

  deleteUtilizadoresFarmacia(id: number) {
      return this.http.delete(`${URL.url}/farm/utilizador_farmacia/${id}`);
  } 


  // === UNIDADE SANITÁRIA ===
  getAllUtilizadoresUs() {
      return this.http.get<any[]>(`${URL.url}/uni_sanit/utilizador_unidade_sanitaria`);
  }
  getRolesUtilizadoresUs() {
      return this.http.get<any[]>(`${URL.url}/uni_sanit/utilizador_unidade_sanitaria/create`);
  }

  getByIdUtilizadoresUs(id: number) {
      return this.http.get(`${URL.url}/uni_sanit/utilizador_unidade_sanitaria/${id}`);
  }

  registerUtilizadoresUs(data: any) {
      return this.http.post(`${URL.url}/uni_sanit/utilizador_unidade_sanitaria`, data);
  }

  updateUtilizadoresUs(data: any) {
      return this.http.put(`${URL.url}/uni_sanit/utilizador_unidade_sanitaria/${data.id}`, data);
  }

  deleteUtilizadoresUs(id: number) {
      return this.http.delete(`${URL.url}/uni_sanit/utilizador_unidade_sanitaria/${id}`);
  } 



}
