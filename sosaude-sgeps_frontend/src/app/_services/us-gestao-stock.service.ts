import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { URL } from '../API_CONFIG';

@Injectable({
  providedIn: 'root'
})
export class UsGestaoStockService {

  constructor(private http: HttpClient) { }

  // ============== STOCK ==============

  get_marcas_medicamentos() {
    return this.http.get<any[]>(`${URL.url}/farm/marcas_medicamentos`);
  }

  getStock() {
    return this.http.get<any[]>(`${URL.url}/farm/stock`);
  }

  getStock_iniciar_venda(data:any) {
    return this.http.get<any[]>(`${URL.url}/farm/stock/iniciar_venda/${data}/beneficiario`);
  }

  getStock_pedido_autorizacao(data:any) {
    return this.http.get<any[]>(`${URL.url}/farm/stock/iniciar_pedido_aprovacao/${data}/beneficiario`);
  }

  postStock(data: any) {
    return this.http.post(`${URL.url}/farm/stock/novo`, data);
  }

  editStock(data: any) {
    return this.http.post(`${URL.url}/farm/stock/actualizar`, data);
  }
  deleteStock(id: number){
    return this.http.post(`${URL.url}/farm/stock/remover/${id}`, id);
  }

  


  // ============== SERVIÇOS ==============

  get_servicos_globais() {
    return this.http.get<any[]>(`${URL.url}/uni_sanit/servicos/globais`);
  }

  get_servicos_us() {
    return this.http.get<any[]>(`${URL.url}/uni_sanit/servicos`);
  }

  get_servicos_us_iniciar_servico(data:any) {
    return this.http.get<any[]>(`${URL.url}/uni_sanit/servicos/iniciar_venda/${data}/beneficiario`);
  }

  get_servicos_us_pedido_aprovacao(data:any) {
    return this.http.get<any[]>(`${URL.url}/uni_sanit/servicos/iniciar_pedido_aprovacao/${data}/beneficiario`);
  }
  
  post_servicos_us(data: any) {
    return this.http.post(`${URL.url}/uni_sanit/servicos/novo`, data);
  }

  update_servicos_us(data: any) {
    return this.http.post(`${URL.url}/uni_sanit/servicos/actualizar`, data);
  }
  // delete(id: number) {
  //   return this.http.post(`${URL.url}/uni_sanit/servicos/remover/${id}`);
  // }
  delete(id: number){
    return this.http.post(`${URL.url}/uni_sanit/servicos/remover/${id}`, id);
  }

  // ============== ORDEM DE RESERVA =====================

  getAllOrdemReservaFarmacia() {
    return this.http.get<any[]>(`${URL.url}/farm/baixas/ordens_reserva`);
  }
  getAllOrdemReservaFarmaciaByBeneficiarioID(id:number) {
    return this.http.get<any[]>(`${URL.url}/farm/baixas/ordens_reserva/${id}/beneficiario`);
  }

  // getAllOrdemReservaUs() {
  //   return this.http.get<any[]>(`${URL.url}/uni_sanit/servicos/globais`);
  // }

}
