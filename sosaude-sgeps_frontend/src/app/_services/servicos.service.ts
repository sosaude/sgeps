import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import {URL} from '../API_CONFIG';

@Injectable({
  providedIn: 'root'
})
export class ServicosService {

  constructor(private http: HttpClient) { }


  // === ADMIN ===
  getAll() {
      return this.http.get<any[]>(`${URL.url}/admin/servicos`);
  }
  getCreate() {
      return this.http.get<any[]>(`${URL.url}/admin/servicos/create`);
  }


  getById(id: number) {
      return this.http.get(`${URL.url}/admin/servicos/${id}`);
  }

  register(data: any) {
      return this.http.post(`${URL.url}/admin/servicos`, data);
  }

  update(data: any) {
      return this.http.put(`${URL.url}/admin/servicos/${data.id}`, data);
  }

  delete(id: number) {
      return this.http.delete(`${URL.url}/admin/servicos/${id}`);
  } 

  // ====== CATEGORIAS =======
  getAllCategorias() {
      return this.http.get<any[]>(`${URL.url}/admin/categorias_servicos`);
  }


  getCategoriaById(id: number) {
      return this.http.get(`${URL.url}/admin/categorias_servicos/${id}`);
  }

  registerCategoria(data: any) {
      return this.http.post(`${URL.url}/admin/categorias_servicos`, data);
  }

  updateCategoria(data: any) {
      return this.http.put(`${URL.url}/admin/categorias_servicos/${data.id}`, data);
  }

  deleteCategoria(id: number) {
      return this.http.delete(`${URL.url}/admin/categorias_servicos/${id}`);
  } 

}