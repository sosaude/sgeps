import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import {URL} from '../API_CONFIG';

@Injectable({
  providedIn: 'root'
})
export class FarmaceuticosService {

  constructor(private http: HttpClient) { }


  // === ADMIN ===
  getAll() {
      return this.http.get<any[]>(`${URL.url}/admin/utilizador_farmacia`);
  }
  getAllFarmacias() {
      return this.http.get<any[]>(`${URL.url}/admin/utilizador_farmacia/create`);
  }

  getById(id: number) {
      return this.http.get(`${URL.url}/admin/utilizador_farmacia/${id}`);
  }

  register(farmaceutico: any) {
      return this.http.post(`${URL.url}/admin/utilizador_farmacia`, farmaceutico);
  }

  update(farmaceutico: any) {
      return this.http.put(`${URL.url}/admin/utilizador_farmacia/${farmaceutico.id}`, farmaceutico);
  }

  delete(id: number) {
      return this.http.delete(`${URL.url}/admin/utilizador_farmacia/${id}`);
  } 
}