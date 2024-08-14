import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse, HttpRequest } from '@angular/common/http';
import { URL } from '../API_CONFIG';
import { Observable, throwError } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { url } from 'inspector';

@Injectable({
  providedIn: 'root'
})
export class BeneficiariosService {

  constructor(private http: HttpClient) { }


  getAll() {
    return this.http.get<any[]>(`${URL.url}/empresa/beneficiarios`);
  }
  getAllBeneExcel() {
    return this.http.get<any[]>(`${URL.url}/empresa/beneficiarios-excel`);
  }

  getAllCreate() {
    return this.http.get<any[]>(`${URL.url}/empresa/beneficiarios/create`);
  }

  getById(id: number) {
    return this.http.get(`${URL.url}/empresa/beneficiarios/${id}`);
  }

  register(data: any) {
    return this.http.post(`${URL.url}/empresa/beneficiarios`, data);
  }

  update(data: any) {
    return this.http.put(`${URL.url}/empresa/beneficiarios/${data.id}`, data);
  }

  delete(id: number) {
    return this.http.delete(`${URL.url}/empresa/beneficiarios/${id}`);
  }

  deleteDependente(id: number) {
    return this.http.delete(`${URL.url}/empresa/dependente_beneficiarios/${id}`);
  }

  // ====================== IMPORTAR BENEFICIARIO =======================
  upload(file):Observable<any> {
  
    // Create form data
    const formData = new FormData(); 
      
    // Store form name as "file" with file data
    formData.append("ficheiro", file, file.name);
      
    // Make http post request over api
    // with formData as req
    return this.http.post(`${URL.url}/empresa/beneficiarios/import-excel`, formData)
}
  uploadFile(file) {
    const formData = new FormData();

    formData.append("file",file,file.name)
    return this.http.post(`${URL.url}/empresa/beneficiarios/import-excel`,formData)

  }
  // uploadFile(formData: FormData): Observable<any> {
  //   return this.http.post(`${URL.url}/empresa/beneficiarios/import-excel`, formData, {
  //     reportProgress: true,
  //     observe: 'events'
  //   }).pipe(
  //     catchError(this.errorMgmt)
  //   )}

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
