import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { URL } from '../API_CONFIG';
import { Observable } from 'rxjs';


@Injectable({
  providedIn: 'root'
})
export class DependentesService {

  constructor(private http: HttpClient) { }

  getAll() {
    return this.http.get<any[]>(`${URL.url}/empresa/dependente_beneficiarios`); 
  }
  getAllDep() {
    return this.http.get<any[]>(`${URL.url}/empresa/dependente_beneficiarios_excel`); 
  }

  upload(file):Observable<any> {
  
    // Create form data
    const formData = new FormData(); 
      
    // Store form name as "file" with file data
    formData.append("ficheiro", file, file.name);
      
    // Make http post request over api
    // with formData as req
    return this.http.post(`${URL.url}/empresa/dependente_beneficiarios/import-excel`, formData)
}


}
