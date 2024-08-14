import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { URL } from '../API_CONFIG';

@Injectable({
    providedIn: 'root'
})
export class SugestoesService {

    constructor(private http: HttpClient) { }


    // === ADMIN ===
    getAll() {
        return this.http.get<any[]>(`${URL.url}/admin/sugestao`);
    }

    getById(id: number) {
        return this.http.get(`${URL.url}/admin/sugestao/${id}`);
    }

    register(data: any) {
        return this.http.post(`${URL.url}/admin/sugestao`, data);
    }

    update(data: any) {
        return this.http.put(`${URL.url}/admin/sugestao/${data.id}`, data);
    }

    delete(id: number) {
        return this.http.delete(`${URL.url}/admin/sugestao/${id}`);
    }

    // ========== FARMÁCIA ==============

    getAllSugestoesFarmacias() {
        return this.http.get<any[]>(`${URL.url}/farm/sugestoes`);
    }

    registerSugestaoFarmacia(data: any) {
 
        return this.http.post(`${URL.url}/farm/sugestoes `, data);
    }

    // ========== Unidade Sanitaria ==============

    getAllSugestoesUs() {
        return this.http.get<any[]>(`${URL.url}/uni_sanit/sugestoes`);
    }

    registerSugestaoUs(data: any) {
        return this.http.post(`${URL.url}/uni_sanit/sugestoes `, data);
    }

}