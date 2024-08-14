<?php

namespace App\Helpers\Farmacia\Baixa;

class RulesManager
{
    public static function validacao($accao_codigo)
    {
        switch ($accao_codigo) {
            case '20':
                return self::validacaoSubmeterBaixaNormal();
                break;

            case '21':
                return self::validacaoSubmeterBaixaApartirDaOrdemeReserva();
                break;

            case '22':
                return self::validacaoSubmeterBaixaApartirDoPedidoDeAutorizacao();
                break;
            
            case '27':
                return self::validacaoReSubmeterBaixaDevolvida();
                break;

            case '40':
                return self::validacaoSubmeterPedidoAutorizacao();
        }
    }


    private static function validacaoSubmeterBaixaNormal()
    {
        return [
            'beneficio_proprio_beneficiario' => 'required|boolean',
            'beneficiario_id' => 'required|integer',
            'dependente_beneficiario_id' => 'nullable|required_if:beneficio_proprio_beneficiario,0|integer',
            'empresa_id' => 'required|integer',
            'farmacia_id' => 'required|integer',
            'valor' => 'required|numeric',
            'nr_comprovativo' => 'required|string|max:100',
            'ficheiros' => 'nullable|array',
            'ficheiros.*' => 'nullable|mimes:jpeg,jpg,png,pdf,doc,docx,excel',
            'itens_baixa' => 'required|array',
            'itens_baixa.*.marca_medicamento_id' => 'required|integer|exists:marca_medicamentos,id',
            'itens_baixa.*.preco' => 'required|numeric',
            'itens_baixa.*.iva' => 'required|numeric',
            'itens_baixa.*.preco_iva' => 'required|numeric',
            'itens_baixa.*:quantidade' => 'required|integer'
        ];
    }

    private static function validacaoSubmeterBaixaApartirDaOrdemeReserva()
    {
        return [
            'id' => 'required|integer',
            'beneficio_proprio_beneficiario' => 'required|boolean',
            'beneficiario_id' => 'required|integer',
            'dependente_beneficiario_id' => 'nullable|required_if:beneficio_proprio_beneficiario,0|integer',
            'empresa_id' => 'required|integer',
            'farmacia_id' => 'required|integer',
            'valor' => 'required|numeric',
            'nr_comprovativo' => 'required|string|max:100',
            'ficheiros' => 'nullable|array',
            'ficheiros.*' => 'nullable|mimes:jpeg,jpg,png,pdf,doc,docx,excel',
            'itens_baixa' => 'required|array',
            'itens_baixa.*.marca_medicamento_id' => 'required|integer|exists:marca_medicamentos,id',
            'itens_baixa.*.preco' => 'required|numeric',
            'itens_baixa.*.iva' => 'required|numeric',
            'itens_baixa.*.preco_iva' => 'required|numeric',
            'itens_baixa.*:quantidade' => 'required|integer'
        ];
    }

    private static function validacaoSubmeterBaixaApartirDoPedidoDeAutorizacao()
    {
        return [
            'id' => 'required|integer',
        ];
    }

    private static function validacaoReSubmeterBaixaDevolvida()
    {
        return [
            'id' => 'required|integer',
            'beneficio_proprio_beneficiario' => 'required|boolean',
            'beneficiario_id' => 'required|integer',
            'dependente_beneficiario_id' => 'nullable|required_if:beneficio_proprio_beneficiario,0|integer',
            'empresa_id' => 'required|integer',
            'farmacia_id' => 'required|integer',
            'valor' => 'required|numeric',
            'nr_comprovativo' => 'required|string|max:100',
            'ficheiros' => 'nullable|array',
            'ficheiros.*' => 'nullable|mimes:jpeg,jpg,png,pdf,doc,docx,excel',
            'itens_baixa' => 'required|array',
            'itens_baixa.*.marca_medicamento_id' => 'required|integer|exists:marca_medicamentos,id',
            'itens_baixa.*.preco' => 'required|numeric',
            'itens_baixa.*.iva' => 'required|numeric',
            'itens_baixa.*.preco_iva' => 'required|numeric',
            'itens_baixa.*:quantidade' => 'required|integer'
        ];
    }

    private static function validacaoSubmeterPedidoAutorizacao()
    {
        return [
            'beneficio_proprio_beneficiario' => 'required|boolean',
            'beneficiario_id' => 'required|integer',
            'dependente_beneficiario_id' => 'nullable|required_if:beneficio_proprio_beneficiario,0|integer',
            'empresa_id' => 'required|integer',
            'valor' => 'required|numeric',
            'comentario' => 'nullable|string',
            'itens_baixa' => 'required|array',
            'itens_baixa.*.marca_medicamento_id' => 'required|integer|exists:marca_medicamentos,id',
            'itens_baixa.*.preco' => 'required|numeric',
            'itens_baixa.*.iva' => 'required|numeric',
            'itens_baixa.*.preco_iva' => 'required|numeric',
            'itens_baixa.*:quantidade' => 'required|integer'
        ];
    }
}
