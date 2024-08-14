import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { URL } from '../API_CONFIG';

@Injectable({
  providedIn: 'root'
})
export class OverviewFarmService {

  constructor(private http: HttpClient) { }

  // === OVERVIEW FARMACIAS ===
  getAll() {
    return this.http.get<any[]>(`${URL.url}/farm/overview`);
  }

  getAllFiltered(startDate: any, endDate: any,companyId: any) {
    return this.http.get<any[]>(`${URL.url}/farm/overviewfiltered/${startDate}/${endDate}/${companyId}`);
  }

  getAllTransacoes() {
    return this.http.get<any[]>(`${URL.url}/farm/overview/baixas`);
  }


  
  // === OVERVIEW UNIDADES SANITARIAS ===
  getAllUS() {
    return this.http.get<any[]>(`${URL.url}/uni_sanit/overview`);
  }

  getAllFilteredUSData(startDate: any, endDate: any,companyId: any) {
    return this.http.get<any[]>(`${URL.url}/uni_sanit/overviewfiltered/${startDate}/${endDate}/${companyId}`);
  }

  getAllTransacoesUS() {
    return this.http.get<any[]>(`${URL.url}/uni_sanit/overview/baixas`);
  }
}
