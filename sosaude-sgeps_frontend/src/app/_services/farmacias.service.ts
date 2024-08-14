import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { URL } from '../API_CONFIG';

@Injectable({
    providedIn: 'root'
})
export class FarmaciasService {

    constructor(private http: HttpClient) { }


    // === ADMIN ===
    getAll() {
        return this.http.get<any[]>(`${URL.url}/admin/farmacias`);
    }

    getById(id: number) {
        return this.http.get(`${URL.url}/admin/farmacias/${id}`);
    }

    getAllFarmaceuticosByFarmaciaId(id: number) {
        return this.http.get(`${URL.url}/admin/farmacias/utilizadores/${id}`);
    }

    register(farmacia: any) {
        return this.http.post(`${URL.url}/admin/farmacias`, farmacia);
    }
 
    update(farmacia: any) {
        return this.http.put(`${URL.url}/admin/farmacias/${farmacia.id}`, farmacia);
    }

    delete(id: number) {
        return this.http.delete(`${URL.url}/admin/farmacias/${id}`);
    }

    // GET ROLES
    getRoles() {
        return this.http.get<any[]>(`${URL.url}/admin/utilizador_farmacia/create`);
    }

    // ======== FARMACIA =============
    getFarmacia() {
        return this.http.get<any[]>(`${URL.url}/farm/farmacias/edit`);
    }

    updateFarmacia(farmacia: any) {
        return this.http.put(`${URL.url}/farm/farmacias/${farmacia.id}`, farmacia);
    }

}