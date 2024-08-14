import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { URL } from '../API_CONFIG';

@Injectable({
    providedIn: 'root'
})
export class GastosService {

    constructor(private http: HttpClient) { }

    // === BAIXAS ===
    getAllBaixas() {
        return this.http.get<any[]>(`${URL.url}/empresa/baixas`);
    }
    getAllBaixasExcel() {
        return this.http.get<any[]>(`${URL.url}/empresa/baixas_excel`);
    }

    confirmarBaixa(formData: FormData) {
        // console.log(formData);
        return this.http.post(`${URL.url}/empresa/baixas/confirmar`, formData);
    }

    confirmarBaixaBulk(data:any) {
        return this.http.post(`${URL.url}/empresa/baixas/confirmar/bulk`, data);
    }

    confirmarPagamento(data: any) {
        let model = {
            id: data.id,
            proveniencia: data.proveniencia
        }
        return this.http.post(`${URL.url}/empresa/baixas/processar_pagamento`, model);
    }

    processar_pagamento_Baixa_Bulk(data:any) {
        return this.http.post(`${URL.url}/empresa/baixas/processar_pagamento/bulk`, data);
    }


    baixarComprovativo(proveniencia: number, id: number, ficheiro: string) {
        return this.http.get(`${URL.url}/empresa/baixas/${proveniencia}/${id}/comprovativo/download/${ficheiro}`, { responseType: 'blob' });
    }

    devolverBaixa(data: any) {
        return this.http.post(`${URL.url}/empresa/baixas/devolver`, data);
    }


    // ===== PEDIDO DE REEMBOLSO ===
    getAllPedidosReembolso() {
        return this.http.get<any[]>(`${URL.url}/empresa/pedidos_reembolso`);
    }
    getAllPedidosReembolsoExcel() {
        return this.http.get<any[]>(`${URL.url}/empresa/pedidos_reembolso_excel`);
    }
    
    CreateParaPedidosReembolso() {
        return this.http.get<any[]>(`${URL.url}/empresa/pedidos_reembolso/create`);
    }
    
    registarPedidoReembolso(data:FormData) {
        return this.http.post<any[]>(`${URL.url}/empresa/pedidos_reembolso/efectuar_pedido`, data);
    }


    confirmarPedidosReembolso(data: FormData) {
        return this.http.post(`${URL.url}/empresa/pedidos_reembolso/confirmar_pedido`, data);
    }

    confirmarPedidosReembolsoBulk(data:any) {
        return this.http.post(`${URL.url}/empresa/pedidos_reembolso/bulk/confirmar_pedido`, data);
    }

    processarPagamentoPedidosReembolso(data: FormData) {
        return this.http.post(`${URL.url}/empresa/pedidos_reembolso/processar_pagamento`, data);
    }

    processarPagamentoPedidosReembolsoBulk(data:any) {
        return this.http.post(`${URL.url}/empresa/pedidos_reembolso/bulk/processar_pagamento`, data);
    }

    devolverPedidosReembolso(data:any) {
        let model = {
            id: data.id,
            comentario: data.comentario
        }
        return this.http.post(`${URL.url}/empresa/pedidos_reembolso/devolver`, model);
    }

    baixarFilePedidoReembolso(pedido_reembolso_id: number, ficheiro: string) {
        return this.http.get(`${URL.url}/empresa/pedidos_reembolso/${pedido_reembolso_id}/download/${ficheiro}`, { responseType: 'blob' });
    }


     // ===== PEDIDO DE APROVACAO ===
     getAllPedidosAprovacao() {
        return this.http.get<any[]>(`${URL.url}/empresa/baixas/pedido_aprovacao`);
    }
     getAllPedidosAprovacaoExcel() {
        return this.http.get<any[]>(`${URL.url}/empresa/baixas/pedido_aprovacao_excel`);
    }

    aprovarPedidosAprovacao(data: any) {
        return this.http.post(`${URL.url}/empresa/baixas/aprovar/pedido_aprovacao`, data);
    }

    aprovarPedidosAprovacaoBulk(data:any) {
        return this.http.post(`${URL.url}/empresa/baixas/aprovar/bulk/pedido_aprovacao`, data);
    }

    rejeitarPedidoAprovacao(data: any) {
        return this.http.post(`${URL.url}/empresa/baixas/rejeitar/pedido_aprovacao`, data);
    }

         // ===== VENDAS ======
         verificacaoBeneficiarioFarmacia(codigo:string) {
            return this.http.post<any[]>(`${URL.url}/farm/baixas/beneficiario/verificar`, codigo);
        }

         verificacaoBeneficiarioUs(codigo:string) {
            return this.http.post<any[]>(`${URL.url}/uni_sanit/baixas/beneficiario/verificar`, codigo);
        }

        verificacaoBeneficiarioGeral(codigo:string) {
            return this.http.post<any[]>(`${URL.url}/farm/baixas/beneficiario/verificar`, codigo);
        }
    
        submeterVendaFarmacia(data: FormData) {
        // submeterVendaFarmacia(data: any) {
            return this.http.post(`${URL.url}/farm/baixas/efectuar`, data);
        }

        submeterVendaUs(data: FormData) {
            return this.http.post(`${URL.url}/uni_sanit/baixas/efectuar`, data);
        }

        // ===========  PEDIDO DE AUTORIZACAO ===============
        submeterPedidoAprovacaoFarmacia(data: any) {
            return this.http.post(`${URL.url}/farm/pedidos_aprovacao`, data);
        }
        
        submeterPedidoAprovacaoUs(data: any) {
            return this.http.post(`${URL.url}/uni_sanit/pedidos_aprovacao`, data);
        }

        // ===========  TRANSAÇÕES -Farmacia ===============
        getAllTransacoesFarmacia() {
            return this.http.get<any[]>(`${URL.url}/farm/baixas`);
        }
        getAllTransacoesFarmaciaExcel() {
            return this.http.get<any[]>(`${URL.url}/farm/baixas_excel`);
        }
        baixarComprovativoFarmacia(proveniencia: number, id: number, ficheiro: string) {
            return this.http.get(`${URL.url}/farm/baixas/${id}/comprovativo/download/${ficheiro}`, { responseType: 'blob' });
        }
    

        // ===========  TRANSAÇÕES - Unidade Sanitaria ===============
        getAllTransacoesUs() {
            return this.http.get<any[]>(`${URL.url}/uni_sanit/baixas`);
        }
        getAllTransacoesUsExcel() {
            return this.http.get<any[]>(`${URL.url}/uni_sanit/baixas_excel`);
        }

        baixarComprovativoUs(proveniencia: number, id: number, ficheiro: string) {
            return this.http.get(`${URL.url}/uni_sanit/baixas/${id}/comprovativo/download/${ficheiro}`, { responseType: 'blob' });
        }

        // =============== Pedidos de Autorizacao
        getAllPedidosUni(){
            return this.http.get<any[]>(`${URL.url}/uni_sanit/pedidos_aprovacao`);
        }
        getAllPedidosUniExcel(){
            return this.http.get<any[]>(`${URL.url}/uni_sanit/pedidos_aprovacao_excel`);
        }
        // =============== Pedidos de Autorizacao
        getAllPedidosFarm(){
            return this.http.get<any[]>(`${URL.url}/farm/pedidos_aprovacao`);
        }
        getAllPedidosFarmExcel(){
            return this.http.get<any[]>(`${URL.url}/farm/pedidos_aprovacao_excel`);
        }
} 											
		