import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import {URL} from '../API_CONFIG';

@Injectable({
  providedIn: 'root'
})
export class MedicamentosService {

  constructor(private http: HttpClient) { }

  
  // ======= MEDICAMENTOS ============
  getAll() {
      return this.http.get<any[]>(`${URL.url}/admin/medicamentos`);
  }

  getById(id: number) {
      return this.http.get(`${URL.url}/admin/medicamentos/${id}`);
  }

  register(farmacia: any) {
      return this.http.post(`${URL.url}/admin/medicamentos`, farmacia);
  }

  update(farmacia: any) {
      return this.http.put(`${URL.url}/admin/medicamentos/${farmacia.id}`, farmacia);
  }

  delete(id: number) {
      return this.http.delete(`${URL.url}/admin/medicamentos/${id}`);
  } 

  // ======= MARCAS ============
  getAllMarcas() {
      return this.http.get<any[]>(`${URL.url}/admin/marca_medicamentos`);
  }

  getMarcasByIdMedicamento(id: number) {
      return this.http.get(`${URL.url}/admin/medicamentos/marcas/${id}`);
  }

  
  registerMarcas(farmacia: any) {
      return this.http.post(`${URL.url}/admin/marca_medicamentos`, farmacia);
    }
    
    updateMarcas(farmacia: any) {
        return this.http.put(`${URL.url}/admin/marca_medicamentos/${farmacia.id}`, farmacia);
    }
    
    deleteMarcas(id: number) {
        return this.http.delete(`${URL.url}/admin/marca_medicamentos/${id}`);
    } 
    
    getFormas() {
        return this.http.get(`${URL.url}/admin/marca_medicamentos/create`);
    }
}
