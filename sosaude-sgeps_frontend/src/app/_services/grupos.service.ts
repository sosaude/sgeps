import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { URL } from '../API_CONFIG';

@Injectable({
    providedIn: 'root'
})
export class GruposService {

    constructor(private http: HttpClient) { }


    // === ADMIN ===
    getAll() {
        return this.http.get<any[]>(`${URL.url}/empresa/grupo_beneficiarios`);
    }

    getById(id: number) {
        return this.http.get(`${URL.url}/empresa/grupo_beneficiarios/${id}`);
    }

    register(farmaceutico: any) {
        return this.http.post(`${URL.url}/empresa/grupo_beneficiarios`, farmaceutico);
    }

    update(farmaceutico: any) {
        return this.http.put(`${URL.url}/empresa/grupo_beneficiarios/${farmaceutico.id}`, farmaceutico);
    }

    delete(id: number) {
        return this.http.delete(`${URL.url}/empresa/grupo_beneficiarios/${id}`);
    }

    // === ADMIN -GRUPO MEDICAMENTOS ===
    getAllGrupoTerapeutico() {
        return this.http.get<any[]>(`${URL.url}/admin/grupos_medicamentos`);
    }

    getByIdGrupoTerapeutico(id: number) {
        return this.http.get(`${URL.url}/admin/grupos_medicamentos/${id}`);
    }

    registerGrupoTerapeutico(data: any) {
        return this.http.post(`${URL.url}/admin/grupos_medicamentos`, data);
    }

    updateGrupoTerapeutico(data: any) {
        return this.http.put(`${URL.url}/admin/grupos_medicamentos/${data.id}`, data);
    }

    deleteGrupoTerapeutico(id: number) {
        return this.http.delete(`${URL.url}/admin/grupos_medicamentos/${id}`);
    }


    // === ADMIN - SUB-GRUPO MEDICAMENTOS ======
    getAllGruposCreate() {
        return this.http.get<any[]>(`${URL.url}/admin/medicamentos/create`);
    }

    getAllSubGrupoTerapeutico() {
        return this.http.get<any[]>(`${URL.url}/admin/sub_grupos_medicamentos`);
    }

    getByIdSubGrupoTerapeutico(id: number) {
        return this.http.get(`${URL.url}/admin/sub_grupos_medicamentos/${id}`);
    }

    registerSubGrupoTerapeutico(data: any) {
        return this.http.post(`${URL.url}/admin/sub_grupos_medicamentos`, data);
    }

    updateSubGrupoTerapeuticoo(data: any) {
        return this.http.put(`${URL.url}/admin/sub_grupos_medicamentos/${data.id}`, data);
    }

    deleteSubGrupoTerapeutico(id: number) {
        return this.http.delete(`${URL.url}/admin/sub_grupos_medicamentos/${id}`);
    }

    // === ADMIN - SUB-Classe MEDICAMENTOS ======

    getAllSubClasseTerapeutico() {
        return this.http.get<any[]>(`${URL.url}/admin/sub_classes_medicamentos`);
    }

    getByIdSubClasseTerapeutico(id: number) {
        return this.http.get(`${URL.url}/admin/sub_classes_medicamentos/${id}`);
    }

    registerSubClasseTerapeutico(data: any) {
        return this.http.post(`${URL.url}/admin/sub_classes_medicamentos`, data);
    }

    updateSubClasseTerapeuticoo(data: any) {
        return this.http.put(`${URL.url}/admin/sub_classes_medicamentos/${data.id}`, data);
    }

    deleteSubClasseTerapeutico(id: number) {
        return this.http.delete(`${URL.url}/admin/sub_classes_medicamentos/${id}`);
    }

    // === ADMIN - FORMA MEDICAMENTOS ======

    getAllFormas() {
        return this.http.get<any[]>(`${URL.url}/admin/formas_medicamentos`);
    }

    getByFormas(id: number) {
        return this.http.get(`${URL.url}/admin/formas_medicamentos/${id}`);
    }

    registerFormas(data: any) {
        return this.http.post(`${URL.url}/admin/formas_medicamentos`, data);
    }

    updateFormas(data: any) {
        return this.http.put(`${URL.url}/admin/formas_medicamentos/${data.id}`, data);
    }

    deleteFormas(id: number) {
        return this.http.delete(`${URL.url}/admin/formas_medicamentos/${id}`);
    }

    // === ADMIN - NOME GENÉRICO MEDICAMENTOS ======

    getAllNomeGenerico() {
        return this.http.get<any[]>(`${URL.url}/admin/nome_generico_medicamentos`);
    }

    getByNomeGenerico(id: number) {
        return this.http.get(`${URL.url}/admin/nome_generico_medicamentos/${id}`);
    }

    registerNomeGenerico(data: any) {
        return this.http.post(`${URL.url}/admin/nome_generico_medicamentos`, data);
    }

    updateNomeGenerico(data: any) {
        return this.http.put(`${URL.url}/admin/nome_generico_medicamentos/${data.id}`, data);
    }

    deleteNomeGenerico(id: number) {
        return this.http.delete(`${URL.url}/admin/nome_generico_medicamentos/${id}`);
    }

}
