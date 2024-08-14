import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { URL } from '../API_CONFIG';


@Injectable({
  providedIn: 'root'
})
export class OverviewService {

  constructor(private http: HttpClient) { }

// === OVERVIEW EMPRESAS ===
  getAll() {
    return this.http.get<any[]>(`${URL.url}/empresa/overview`); 
  }
  getAllServicos(startDate: any, endDate: any,usId: any,farmId: any) {
    return this.http.get<any[]>(`${URL.url}/empresa/overview/servicos/${startDate}/${endDate}/${usId}/${farmId}`); 
  }

  getAllServicosFarm(startDate: any, endDate: any, farmId,usId) {
    return this.http.get<any[]>(`${URL.url}/empresa/overview/servicos_farmacia/${startDate}/${endDate}/${farmId}/${usId}`); 
  }

  getAllBeneFaixas(startDate: any, endDate: any) {
    return this.http.get<any[]>(`${URL.url}/empresa/overview/bene_faixas/${startDate}/${endDate}`); 
  }

  getAllBenBaixas(startDate: any, endDate: any, farmId: any, usId: any) {
    return this.http.get<any[]>(`${URL.url}/empresa/overview/index_dashboard/${startDate}/${endDate}/${farmId}/${usId}`); 
  }

// === OVERVIEW ADMINISTRACAO ===

  getAllAdminOverview() {
    return this.http.get<any[]>(`${URL.url}/admin/overview`); 
  }


}
