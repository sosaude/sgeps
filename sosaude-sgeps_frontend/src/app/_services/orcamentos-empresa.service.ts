import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse, HttpRequest } from '@angular/common/http';
import { URL } from '../API_CONFIG';
import { Observable, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class OrcamentosEmpresaService {

  constructor(private http: HttpClient) { }

  getAll() {
    return this.http.get<any[]>(`${URL.url}/empresa/orcamentos`);
  }

  register(data: any) {
    return this.http.post(`${URL.url}/empresa/orcamento`, data);
  }

  getExecutados() {
    return this.http.get<any[]>(`${URL.url}/empresa/orcamentos/executados`);
  }

  updateOrcamentoEmpresa(data: any) {
    return this.http.put(`${URL.url}/empresa/orcamento/${data.id}`, data);
}

  deleteOrcamentoEmpresa(id: number) {
    return this.http.delete(`${URL.url}/empresa/orcamento/${id}`);
} 

  errorMgmt(error: HttpErrorResponse) {
    let errorMessage = '';
    if (error.error instanceof ErrorEvent) {
      // Get client-side error
      errorMessage = error.error.message;
    } else {
      // Get server-side error
      errorMessage = `Error Code: ${error.status}\nMessage: ${error.message}`;
    }
    console.log(errorMessage);
    return throwError(errorMessage);
  }
}
